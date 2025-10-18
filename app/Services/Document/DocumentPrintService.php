<?php

namespace App\Services\Document;

use App\Models\DocumentReview;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

class DocumentPrintService
{
    private DocumentIdGenerator $documentIdGenerator;

    public function __construct(DocumentIdGenerator $documentIdGenerator)
    {
        $this->documentIdGenerator = $documentIdGenerator;
    }

    public function printReceipt(DocumentReview $review)
    {
        $user = Auth::user();

        // Generate document ID if not exists and save to review
        if (! $review->document_id) {
            $documentId = $this->documentIdGenerator->generate();
            $review->update(['document_id' => $documentId]);
        } else {
            $documentId = $review->document_id;
        }

        // Generate QR code with document ID
        $qrPath = $this->generateQrCodeFile($documentId);

        // === PRINTER CONFIG Linux===
        $printerPath = null;
        exec('ls /dev/usb/lp* 2>/dev/null', $devices);

        if (! empty($devices)) {
            $printerPath = $devices[0]; // pick first one
        } else {
            throw new \Exception('No thermal printer found under /dev/usb/');
        }

        $connector = new FilePrintConnector($printerPath);
        $printer = new Printer($connector);

        $printer->initialize();
        $printer->getPrintConnector()->write("\x1B\x21\x30");

        try {
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);

            // Add department name
            $departmentName = $review->originalDepartment->name ?? 'N/A';
            $printer->text(strtoupper($departmentName)."\n"."\n");

            $printer->text(now()->toDateTimeString()."\n");
            $printer->text("Document Receipt\n");
            $printer->setEmphasis(true);
            $printer->feed();

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Document ID: '.$documentId."\n");

            $printer->feed();

            // Print QR Code centered
            if ($qrPath && file_exists($qrPath)) {
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $img = EscposImage::load($qrPath, false);
                $printer->bitImage($img);
                $printer->feed();
                $printer->text("Scan to Verify\n");
            } else {
                $printer->text("[QR Code missing]\n");
            }

            $printer->feed(2);
            $printer->cut();

            // Mark as downloaded
            $review->update(['downloaded_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Printing failed: '.$e->getMessage());
        } finally {
            $printer->close();
        }

        return redirect()->back()->with('success', 'Printed Successfully');
    }

    private function generateQrCodeFile(string $documentId): ?string
    {
        try {
            // Generate URL that directly links to the document review
            $qrData = route('documents.show-by-id', $documentId);

            $tempDir = storage_path('app/public/temp');
            $this->ensureDirectoryExists($tempDir);

            $tempPath = $tempDir.DIRECTORY_SEPARATOR.'qr_'.$documentId.'.png';

            $qrCode = new QrCode($qrData);
            $qrCode->setSize(250);
            $qrCode->setMargin(10);

            $writer = new PngWriter;
            $result = $writer->write($qrCode);

            file_put_contents($tempPath, $result->getString());

            return (file_exists($tempPath) && filesize($tempPath) > 0) ? $tempPath : null;
        } catch (\Exception $e) {
            Log::error('QR generation failed: '.$e->getMessage());

            return null;
        }
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (! file_exists($directory)) {
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
