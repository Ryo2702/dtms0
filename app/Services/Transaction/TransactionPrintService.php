<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class TransactionPrintService
{
    public function printReceipt(Transaction $transaction)
    {
        try {
            // Generate QR code with transaction code
            $qrPath = $this->generateQrCodeFile($transaction->transaction_code);

            // Try to find and connect to printer
            $connector = $this->getPrinterConnector();
            
            if (!$connector) {
                Log::warning('No printer found - skipping print for transaction: ' . $transaction->transaction_code);
                return;
            }

            $printer = new Printer($connector);

            try {
                $printer->initialize();
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setEmphasis(true);

                // Header
                $printer->text("TRANSACTION RECEIPT\n");
                $printer->text("====================\n");
                $printer->feed();

                // Transaction details
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Transaction Code: " . $transaction->transaction_code . "\n");
                $printer->text("Workflow: " . $transaction->workflow->transaction_name . "\n");
                $printer->text("Date: " . $transaction->created_at->format('Y-m-d H:i:s') . "\n");
                
                if ($transaction->originDepartment) {
                    $printer->text("Origin: " . $transaction->originDepartment->name . "\n");
                }
                
                if ($transaction->assignStaff) {
                    $printer->text("Staff: " . $transaction->assignStaff->name . "\n");
                }
                
                $printer->feed();

                // QR Code
                if ($qrPath && file_exists($qrPath)) {
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $img = EscposImage::load($qrPath, false);
                    $printer->bitImage($img);
                    $printer->feed();
                    $printer->text("Scan to Track\n");
                }

                $printer->feed(2);
                $printer->cut();

            } finally {
                $printer->close();
            }
        } catch (\Exception $e) {
            Log::warning('Transaction printing failed: ' . $e->getMessage());
            // Don't throw - printing is optional
        }
    }

    private function getPrinterConnector()
    {
        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'WIN') {
            // Windows
            $printerName = $this->getWindowsPrinter();
            if ($printerName) {
                try {
                    return new WindowsPrintConnector($printerName);
                } catch (\Exception $e) {
                    Log::warning("Failed to connect to Windows printer {$printerName}: " . $e->getMessage());
                    return null;
                }
            }
        } else {
            // Linux
            $printerPath = $this->getLinuxPrinter();
            if ($printerPath) {
                try {
                    return new FilePrintConnector($printerPath);
                } catch (\Exception $e) {
                    Log::warning("Failed to connect to Linux printer {$printerPath}: " . $e->getMessage());
                    return null;
                }
            }
        }

        return null;
    }

    private function getWindowsPrinter(): ?string
    {
        // Try common thermal printer names
        $possiblePrinters = ['POS-80', 'TM-T20', 'TSP100', 'ZDesigner', 'Thermal', 'Receipt'];
        
        exec('wmic printer get name 2>&1', $output);
        
        foreach ($output as $line) {
            $line = trim($line);
            if (empty($line) || $line === 'Name') {
                continue;
            }
            
            // Check if line contains common printer names
            foreach ($possiblePrinters as $printer) {
                if (stripos($line, $printer) !== false) {
                    Log::info("Found Windows printer: {$line}");
                    return $line;
                }
            }
        }

        // If no thermal printer found, try first available printer
        if (isset($output[1]) && trim($output[1]) !== 'Name') {
            $printerName = trim($output[1]);
            if (!empty($printerName)) {
                Log::info("Using first available Windows printer: {$printerName}");
                return $printerName;
            }
        }

        Log::warning('No Windows printer found');
        return null;
    }

    private function getLinuxPrinter(): ?string
    {
        // Scan for USB printers
        $possiblePaths = [
            '/dev/usb/lp0',
            '/dev/usb/lp1',
            '/dev/usb/lp2',
            '/dev/lp0',
            '/dev/lp1',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_writable($path)) {
                Log::info("Found accessible Linux printer: {$path}");
                return $path;
            }
        }

        // Try to find via ls command
        exec('ls /dev/usb/lp* 2>/dev/null', $devices);
        if (!empty($devices)) {
            foreach ($devices as $device) {
                if (is_writable($device)) {
                    Log::info("Found writable Linux printer: {$device}");
                    return $device;
                }
            }
        }

        // Try standard /dev/lp*
        exec('ls /dev/lp* 2>/dev/null', $devices);
        if (!empty($devices)) {
            foreach ($devices as $device) {
                if (is_writable($device)) {
                    Log::info("Found writable Linux printer: {$device}");
                    return $device;
                }
            }
        }

        Log::warning('No accessible Linux printer found. Check permissions: sudo chmod 666 /dev/usb/lp0');
        return null;
    }

    private function generateQrCodeFile(string $transactionCode): ?string
    {
        try {
            $qrData = route('transactions.show', ['transaction' => $transactionCode]);

            $tempDir = storage_path('app/public/temp');
            $this->ensureDirectoryExists($tempDir);

            $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'qr_transaction_' . $transactionCode . '.png';

            $qrCode = new QrCode($qrData);
            $qrCode->setSize(250);
            $qrCode->setMargin(10);

            $writer = new PngWriter;
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
}
