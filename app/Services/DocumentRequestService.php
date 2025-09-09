<?php

namespace App\Services;

use App\Models\DocumentReview;
use App\Repositories\DocumentReviewRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DocumentRequestService
{
    public function __construct(
        private DocumentReviewRepository $reviewRepository
    ) {}

    public function createReview(array $data, string $file, array $docInfo): DocumentReview
    {
        $documentId = $this->generateDocumentId();

        return DocumentReview::create([
            'document_id' => $documentId,
            'document_type' => $docInfo['title'] ?? $file,
            'document_data' => json_encode($data),
            'status' => 'pending',
            'created_by' => Auth::id(),
            'assigned_to' => $this->getInitialReviewer(),
            'current_department_id' => $this->getInitialDepartment(),
            'original_department_id' => Auth::user()->department_id,
            'due_at' => now()->addBusinessDays(3), // Default 3 business days
        ]);
    }

    private function generateDocumentId(): string
    {
        return "DT-" . now()->format('Ymd') . "-" . strtoupper(Str::random(6));
    }

    private function getInitialReviewer(): ?int
    {
        // Logic to determine initial reviewer
        // Could be based on document type, department, etc.
        return Auth::user()->department->head_user_id ?? null;
    }

    private function getInitialDepartment(): ?int
    {
        // Logic to determine initial department for review
        return Auth::user()->department_id;
    }
}
