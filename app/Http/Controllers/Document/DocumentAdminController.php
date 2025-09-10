<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentAdminController extends Controller
{
    public function track(Request $request)
    {
        $user = Auth::user();

        if ($user->type !== 'Admin') {
            abort(403, 'Only administrators can access document tracking.');
        }

        $query = DocumentReview::with(['creator', 'reviewer', 'currentDepartment', 'originalDepartment']);

        $this->applyFilters($query, $request);

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);

        $documents->getCollection()->transform(function ($review) {
            $review->journey_steps = count($review->forwarding_chain ?? []);
            $review->processing_time = $review->submitted_at && $review->downloaded_at
                ? $review->submitted_at->diffInMinutes($review->downloaded_at)
                : null;
            $review->is_overdue = $review->due_at && now()->greaterThan($review->due_at) && !$review->downloaded_at;
            return $review;
        });

        $filterOptions = $this->getFilterOptions();
        $stats = $this->getStatistics();

        return view('documents.admin.track', compact(
            'documents',
            'user',
            'filterOptions',
            'stats'
        ) + $filterOptions);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('document_id', 'LIKE', "%{$search}%")
                    ->orWhere('client_name', 'LIKE', "%{$search}%")
                    ->orWhere('document_type', 'LIKE', "%{$search}%")
                    ->orWhereHas('creator', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('employee_id', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('reviewer', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('employee_id', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('department')) {
            $departmentId = $request->get('department');
            $query->where(function ($q) use ($departmentId) {
                $q->where('original_department_id', $departmentId)
                    ->orWhere('current_department_id', $departmentId);
            });
        }

        if ($request->filled('user_type')) {
            $userType = $request->get('user_type');
            $query->whereHas('creator', function ($userQuery) use ($userType) {
                $userQuery->where('type', $userType);
            });
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->get('document_type'));
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'completed') {
                $query->where('status', 'approved')->whereNotNull('downloaded_at');
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
    }

    private function getFilterOptions(): array
    {
        return [
            'departments' => \App\Models\Department::all(),
            'documentTypes' => DocumentReview::select('document_type')->distinct()->pluck('document_type'),
            'userTypes' => ['Head', 'Staff']
        ];
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
