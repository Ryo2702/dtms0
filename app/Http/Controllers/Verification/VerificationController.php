<?php

namespace App\Http\Controllers\Verification;

use App\Http\Controllers\Controller;
use App\Models\DocumentVerification;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VerificationController extends Controller
{
    public function verify($verificationCode)
    {
        $verification = DocumentVerification::where('verification_code', $verificationCode)
            ->where('is_active', true)
            ->first();

        if (!$verification) {
            return view('verification.not-found', compact('verificationCode'));
        }
        $verification->recordVerification();

        return view('verification.show', compact('verification'));
    }

    public function qrcode($verificationCode)
    {
        $verification = DocumentVerification::where('verification_code', $verificationCode)
            ->where('is_active', true)
            ->first();

        if (!$verification) {
            return view('verification.not-found', compact('verificationCode'));
        }

        $url = route('documents.verify', $verificationCode);

        return QrCode::format('svg')
            ->size(72)
            ->margin(0)
            ->generate($url);
    }

    // Add this new method to generate QR code as base64 image for documents
    public function generateQrCodeImage($verificationCode)
    {
        $verification = DocumentVerification::where('verification_code', $verificationCode)
            ->where('is_active', true)
            ->first();

        if (!$verification) {
            return null;
        }

        // Create QR code data with document information
        $qrData = "Document ID: {$verification->document_id}\n";
        $qrData .= "Title: {$verification->document_type}\n";
        $qrData .= "Name: {$verification->client_name}\n";
        $qrData .= "Employee ID: {$verification->employee_id}\n";
        $qrData .= "Department: {$verification->department}\n";
        $qrData .= "Verification URL: " . route('documents.verify', $verificationCode);

        // Generate QR code as PNG and convert to base64
        $qrCodePng = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($qrData);

        return 'data:image/png;base64,' . base64_encode($qrCodePng);
    }

    public function lookup(Request $request)
    {
        if ($request->has('code')) {
            return redirect()->route('documents.verify', $request->code);
        }

        return view('verification.lookup');
    }
}
