<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentReview;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentAdminController extends Controller
{
    public function track(Request $request)
    {
        $user = Auth::user();

        if ($user->type !== 'Admin') {
            abort(403, 'Only administrators can access document tracking.');
        }

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        $departments = Department::withCount([
            'documentReviews as total_created',
            'documentReviews as pending_count' => function ($query) {
                $query->where('status', 'pending');
            },
            'documentReviews as completed_count' => function ($query) {
                $query->where('status', 'approved')->whereNotNull('downloaded_at');
            },
            'documentReviews as rejected_count' => function ($query) {
                $query->where('status', 'rejected');
            },
            'documentReviews as canceled_count' => function ($query) {
                $query->where('status', 'canceled');
            },
            'documentReviews as approved_count' => function ($query) {
                $query->where('status', 'approved')->whereNull('downloaded_at');
            }
        ]);

        switch ($sortField) {
            case 'department':
                $departments->orderBy('name', $sortDirection);
                break;
            case 'total_created':
            case 'pending_count':
            case 'approved_count':
            case 'completed_count':
            case 'rejected_count':
            case 'canceled_count':
                $departments->orderBy($sortField, $sortDirection);
                break;
            default:
                $departments->orderBy('name', 'asc');
        }

        $departments = $departments->paginate(15)->appends([
            'sort' => $sortField,
            'direction' => $sortDirection
        ]);

        $stats = $this->getStatistics();

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
