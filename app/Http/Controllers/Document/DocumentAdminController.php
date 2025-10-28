<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentReview;
use App\Services\Track\TrackServices;
use Illuminate\Http\Request;


class DocumentAdminController extends Controller
{
    public function track(Request $request)
    {
        $user = auth()->user();

        if ($user->type !== 'Admin') {
            abort(403, 'Only administrators can access document tracking.');
        }

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';


        $departments = TrackServices::getDepartmentWithDocumentCounts([
            'exclude_admin_heads' => true,
            'sort_by' => $sortField,
            'sort_direction' => $sortDirection,
        ])->paginate(10)->appends([
            'sort' => $sortField,
            'direction' => $sortDirection 
        ]);

        $stats = TrackServices::getDocumentStatistics(['exclude_admin_heads' => true]);

        return view('documents.admin.track', compact(
            'departments',
            'user',
            'stats'
        ));
    }

    private function getStatistics(): array
    {
        return [
            'total_documents' => DocumentReview::count(),
            'pending_documents' => DocumentReview::where('status', 'pending')->count(),
            'completed_documents' => DocumentReview::where('status', 'approved')->whereNotNull('downloaded_at')->count(),
            'overdue_documents' => DocumentReview::where('status', 'pending')
                ->where('due_at', '<', now())->count(),
            'avg_processing_time' => DocumentReview::where('status', 'approved')
                ->whereNotNull('downloaded_at')
                ->whereNotNull('submitted_at')
                ->get()
                ->avg(function ($doc) {
                    return $doc->submitted_at->diffInMinutes($doc->downloaded_at);
                }),
        ];
    }
}
