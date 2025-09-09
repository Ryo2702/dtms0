<?php

namespace App\Services;

use App\Models\DocumentReview;
use App\Models\DocumentVerification;
use App\Jobs\GenerateDocumentJob;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentDownloadService
{
    public function generateDocument(DocumentReview $review, array $data): BinaryFileResponse
    {
        // Dispatch job for heavy document generation
        GenerateDocumentJob::dispatch($review, $data);

        $templatePath = $this->getTemplatePath($review->document_type);

        if (!file_exists($templatePath)) {
            abort(404, 'Template not found');
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Replace placeholders with data
        foreach ($data as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        // Handle QR code generation
        $this->handleQrCode($templateProcessor, $review);

        // Generate filename and save
        $filename = $this->generateFilename($review, $data);
        $outputPath = storage_path("app/temp/{$filename}");

        $templateProcessor->saveAs($outputPath);

        // Mark as downloaded
        $review->update(['downloaded_at' => now()]);

        return $this->downloadFile($outputPath, $filename);
    }

    private function getTemplatePath(string $documentType): string
    {
        $templates = [
            'mayor_clearance' => storage_path('app/templates/Mayor_Clearance.docx'),
            'mpoc' => storage_path('app/templates/MPOC.docx'),
        ];

        return $templates[$documentType] ?? storage_path("app/templates/{$documentType}.docx");
    }

    private function handleQrCode(TemplateProcessor $templateProcessor, DocumentReview $review): void
    {
        $verification = DocumentVerification::where('document_id', $review->document_id)->first();

        if ($verification) {
            $qrCodePath = $this->generateQrCodeFile($verification);

            if ($qrCodePath && file_exists($qrCodePath)) {
                $templateProcessor->setImageValue('qr_verification_url', [
                    'path' => $qrCodePath,
                    'width' => 100,
                    'height' => 100,
                    'ratio' => false
                ]);
            }
        }
    }

    private function generateQrCodeFile(DocumentVerification $verification): ?string
    {
        try {
            $qrData = "Document ID: {$verification->document_id}\n";
            $qrData .= "Title: {$verification->document_type}\n";
            $qrData .= "Name: {$verification->client_name}\n";
            $qrData .= "Employee ID: {$verification->employee_id}\n";
            $qrData .= "Department: {$verification->department}\n";
            $qrData .= "Verification URL: " . route('documents.verify', $verification->verification_code);

            $tempPath = storage_path('app/temp/qr_' . $verification->verification_code . '.png');

            $tempDir = dirname($tempPath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(200)
                ->margin(1)
                ->generate($qrData, $tempPath);

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Failed to generate QR code file: ' . $e->getMessage());
            return null;
        }
    }

    private function generateFilename(DocumentReview $review, array $data): string
    {
        $name = $data['full_name'] ?? $data['employee_name'] ?? 'Document';
        $sanitizedName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $name);
        $timestamp = now()->format('Ymd_His');

        return "{$review->document_type}_{$sanitizedName}_{$timestamp}.docx";
    }

    private function downloadFile(string $path, string $filename): BinaryFileResponse
    {
        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])->deleteFileAfterSend(true);
    }
}
