<?php

namespace App\Services\Document;

use App\Models\DocumentReview;
use App\Models\DocumentVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;

class DocumentPrintService
{
    public function printReceipt(DocumentReview $review)
    {
        $user = Auth::user();

        // Create a verification record
        $verification = $this->createVerification($review, $user);

        // Generate QR code
        $qrPath = $this->generateQrCodeFile($verification);

        // === PRINTER CONFIG Linux===
       $printerPath = null;
        exec('ls /dev/usb/lp* 2>/dev/null', $devices);

        if (!empty($devices)) {
        $printerPath = $devices[0]; // pick first one
        } else {
        throw new \Exception('No thermal printer found under /dev/usb/');
        }

$connector = new FilePrintConnector($printerPath);
        $printer = new Printer($connector);


        $printer->initialize();
        $printer->getPrintConnector()->write("\x1B\x21\x30");


        // === WINDOWS PRINTER NAME ===
        // $printerName = "XPrinter 58mm"; 
        // $connector = new WindowsPrintConnector($printerName);
        // $printer = new Printer($connector);

        try {
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text(now()->toDateTimeString() . "\n");
            $printer->text("Document Receipt\n");
            $printer->setEmphasis(true);
            $printer->feed();

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Verification Code: " . ($verification->verification_code ?? 'N/A') . "\n");
            $printer->text("Document ID: " . ($review->document_id ?? 'N/A') . "\n");
            $printer->text("Employee ID: " . ($user->employee_id ?? 'N/A') . "\n");

            $printer->feed();

            // Print QR Code centered
            if ($qrPath && file_exists($qrPath)) {
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $img = EscposImage::load($qrPath, false);
                $printer->bitImage($img);
                 $printer->setEmphasis(true);
                $printer->feed();
                $printer->text("Scan to Verify\n");
            } else {
                $printer->text("[QR Code missing]\n");
            }

            $printer->feed(2);
            $printer->cut();
        } catch (\Exception $e) {
            Log::error('Printing failed: ' . $e->getMessage());
        } finally {
            $printer->close();
        }

        return redirect()->back()->with('success', 'Printed Successfully');
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
        ]);
    }

    private function generateQrCodeFile(DocumentVerification $verification): ?string
    {
        try {
            $qrData = route('documents.verify', $verification->verification_code);
            
            $tempDir = storage_path('app/public/temp');
            $this->ensureDirectoryExists($tempDir);
            
            $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'qr_' . $verification->verification_code . '.png';
            
            $qrCode = new QrCode($qrData);
            $qrCode->setSize(250);
            $qrCode->setMargin(10);
            
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            
            file_put_contents($tempPath, $result->getString());
            
            return (file_exists($tempPath) && filesize($tempPath) > 0) ? $tempPath : null;
        } catch (\Exception $e) {
            Log::error('QR generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    // Added to satisfy controller call
    public function generateDocument(DocumentReview $review)
    {
        // Delegate to the existing print logic. Adjust return type if you want a different behavior.
        return $this->printReceipt($review);
    }
}
