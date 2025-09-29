<?php

namespace App\Services\Document;

use App\Models\DocumentReview;
use App\Models\DocumentVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class DocumentDownloadService
{
    public function generateDocument(DocumentReview $review)
    {
        $user = Auth::user();

        // Add download step to chain
        $review->addToForwardingChain(
            'downloaded',
            $user,
            null,
            'Document downloaded by original creator for client signature',
            null
        );

        $verification = $this->createVerification($review, $user);
        $review->update(['downloaded_at' => now()]);

        $templatePath = $this->getTemplatePath($review->document_type);
        $templateProcessor = new TemplateProcessor($templatePath);

        $this->populateTemplate($templateProcessor, $review, $verification, $user);

        $fileName = $review->document_id . '_' . str_replace(' ', '_', $review->document_type) . '.docx';
        $outputPath = storage_path("app/temp/{$fileName}");

        $this->ensureDirectoryExists(dirname($outputPath));
        $templateProcessor->saveAs($outputPath);

        return response()->download($outputPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    private function createVerification(DocumentReview $review, $user): DocumentVerification
    {
        return DocumentVerification::create([
            'verification_code' => DocumentVerification::generateVerificationCode(),
            'document_id' => $review->document_id,
            'document_type' => $review->document_type,
            'client_name' => $review->client_name,
            'issued_by' => $user->name,
            'issued_by_id' => $user->employee_id ?? $user->id,
            'issued_at' => now(),
            'document_data' => $review->document_data,
            'official_receipt_number' => $review->official_receipt_number
        ]);
    }

    private function getTemplatePath(string $documentType): string
    {
        $file = match ($documentType) {
            "Mayor's Clearance" => 'Mayors_Clearance.docx',
            'Municipality Peace Order Council' => 'MPOC_Sample.docx',
            default => throw new \InvalidArgumentException("Document template {$documentType} not found.")
        };

        $templatePath = storage_path("app/public/templates/{$file}");

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Document template file '{$file}' not found.");
        }

        return $templatePath;
    }

    private function populateTemplate(TemplateProcessor $processor, DocumentReview $review, DocumentVerification $verification, $user): void
    {
        // Common fields
        $processor->setValue('employee_id', $user->employee_id ?? 'N/A');
        $processor->setValue('document_id', $review->document_id ?? 'N/A');
        $processor->setValue('issued_at', now()->format('M d, Y'));
        $processor->setValue('verification_code', $verification->verification_code);

        // QR Code
        $qrCodePath = $this->generateQrCodeFile($verification);

        if ($qrCodePath && file_exists($qrCodePath)) {
            $processor->setImageValue('qr_verification_url', [
                'path' => $qrCodePath,
                'width' => 55,
                'height' => 55,
                'ratio' => false
            ]);
        } else {
            $processor->setValue('qr_verification_url', 'none');
        }

        // Document-specific fields
        $data = $review->document_data;

        if ($review->document_type === "Mayor's Clearance") {
            $this->populateMayorsClearance($processor, $data, $review);
        }
    }

    private function populateMayorsClearance(TemplateProcessor $processor, array $data, DocumentReview $review): void
    {
        $processor->setValue('name', $data['name'] ?? '');
        $processor->setValue('address', $data['address'] ?? '');
        $processor->setValue('purpose', $data['purpose'] ?? '');
        $processor->setValue('fee', $data['fee'] ?? '');
        $processor->setValue('or_number', $review->official_receipt_number ?? 'N/A');
        $processor->setValue('date', $data['date'] ?? now()->format('Y-m-d'));
    }


    private function generateQrCodeFile(DocumentVerification $verification): ?string
    {
        try {
            $qrData = route('documents.verify', $verification->verification_code);
            
            $tempDir = storage_path('app/public/temp');
            $this->ensureDirectoryExists($tempDir);
            
            $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'qr_' . $verification->verification_code . '.png';
            
            $qrCode = new QrCode($qrData);
            $qrCode->setSize(200);
            $qrCode->setMargin(10);
            
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            
            file_put_contents($tempPath, $result->getString());
            
            return file_exists($tempPath) && filesize($tempPath) > 0 ? $tempPath : null;
            
        } catch (\Exception $e) {
            Log::error('QR generation failed: ' . $e->getMessage());
            return null;
        }
    }

    // private function convertSvgToPng(string $svgContent, string $outputPath): void
    // {
    //     // Create a simple black and white QR code image using GD
    //     $size = 200;
    //     $image = imagecreatetruecolor($size, $size);
    //     $white = imagecolorallocate($image, 255, 255, 255);
    //     $black = imagecolorallocate($image, 0, 0, 0);
        
    //     imagefill($image, 0, 0, $white);
        
    //     $font = 5;
    //     $text = "QR Code";
    //     imagestring($image, $font, 80, 95, $text, $black);
        
    //     imagepng($image, $outputPath);
    //     imagedestroy($image);
    // }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
