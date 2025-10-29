<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class HistoryController extends Controller
{
    /**
     * Display document history for head account only
     */
    public function index()
    {
        $user = auth()->user();
        
        $ownerColumn = $this->detectOwnerColumn();
        $sentByColumn = $this->detectSentByColumn();

           if ($user->type !== 'Head') {
            abort(403, 'Anauthorized.');
        }

        // If no owner column detected, return safe empty results and pass columns for debugging
        if (! $ownerColumn) {
            $ownDocuments = Document::whereRaw('0 = 1')->paginate(15);
            $sentToDepartments = Document::whereRaw('0 = 1')->paginate(15);

            $stats = [
                'totalDocuments' => 0,
                'documentsSent' => 0,
                'pendingDocuments' => 0,
                'approvedDocuments' => 0,
            ];

            $ownerColumns = \Schema::getColumnListing('documents');

            return view('head.document-history.index', compact('ownDocuments', 'sentToDepartments', 'stats'))
                ->with('ownerColumnMissing', true)
                ->with('ownerColumns', $ownerColumns);
        }

        $ownDocuments = Document::where($ownerColumn, $user->id)
            ->with(['creator', 'recipient_department'])
            ->latest()
            ->paginate(15);

        // Get documents sent to other departments by the head (only if sent-by column exists)
        $sentQuery = Document::query();
        if ($sentByColumn) {
            $sentQuery->where($sentByColumn, $user->id);
        } else {
            // fallback to ownerColumn if no sent column exists (will return empty)
            $sentQuery->whereRaw('0 = 1');
        }
        $sentToDepartments = $sentQuery->with(['creator', 'recipient_department'])->latest()->paginate(15);

        // Get statistics for the head
        $stats = [
            'totalDocuments' => Document::where($ownerColumn, $user->id)->count(),
            'documentsSent' => $sentByColumn ? Document::where($sentByColumn, $user->id)->count() : 0,
            'pendingDocuments' => Document::where($ownerColumn, $user->id)->where('status', 'pending')->count(),
            'approvedDocuments' => Document::where($ownerColumn, $user->id)->where('status', 'approved')->count(),
        ];

        return view('history.history.index', compact('ownDocuments', 'sentToDepartments', 'stats'));
    }

    /**
     * Show document details - head only
     */
    public function show(Document $document)
    {
        $user = auth()->user();

        $ownerColumn = $this->detectOwnerColumn();
        $sentByColumn = $this->detectSentByColumn();

        // If owner column is missing we can't authorize safely
        if (! $ownerColumn) {
            abort(404, 'Document owner column not found on documents table. Inspect your schema.');
        }

        $isOwner = $document->{$ownerColumn} ?? null;
        $isSentBy = $sentByColumn ? ($document->{$sentByColumn} ?? null) : null;


        $document->load(['creator', 'recipient_department']);

        return view('head.document-history.show', compact('document'));
    }

    /**
     * Detect which column to use as the document owner column.
     * Returns the column name string or aborts with clear message.
     */
    private function detectOwnerColumn(): ?string
    {
        $candidates = ['user_id', 'created_by', 'creator_id', 'owner_id', 'head_id'];

        foreach ($candidates as $col) {
            if (Schema::hasColumn('documents', $col)) {
                return $col;
            }
        }

        // Return null instead of aborting — caller will handle gracefully
        return null;
    }

    /**
     * Detect the sent-by column if present.
     * Returns column name or null.
     */
    private function detectSentByColumn(): ?string
    {
        $candidates = ['sent_by', 'forwarded_by', 'sent_by_id'];

        foreach ($candidates as $col) {
            if (Schema::hasColumn('documents', $col)) {
                return $col;
            }
        }

        return null;
    }
}