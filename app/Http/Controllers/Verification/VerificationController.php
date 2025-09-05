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

        $verification->recordVerfication();

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

    public function lookup(Request $request)
    {
        if ($request->has('code')) {
            return redirect()->route('documents.verify', $request->code);
        }

        return view('verification.lookup');
    }
}
