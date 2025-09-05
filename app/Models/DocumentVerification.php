<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class DocumentVerification extends Model
{

    use HasFactory;

    protected $fillable = [
        'verification_code',
        'document_id',
        'document_type',
        'client_name',
        'issued_by',
        'issued_by_id',
        'issued_at',
        'document_data',
        'official_receipt_number',
        'verification_count',
        'last_verified_at',
        'is_active'
    ];
    protected $casts = [
        'document_data' => 'array',
        'issued_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Generate unique verification code
    public static function generateVerificationCode()
    {
        do {
            $code = 'VER-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }

    // Get verification URL
    public function getVerificationUrlAttribute()
    {
        return route('documents.verify', $this->verification_code);
    }

    // Get QR code URL
    public function getQrCodeUrlAttribute()
    {
        return route('documents.qrcode', $this->verification_code);
    }

    // Increment verification count
    public function recordVerification()
    {
        $this->increment('verification_count');
        $this->update(['last_verified_at' => now()]);
    }

    // Check if document is valid
    public function isValid()
    {
        return $this->is_active && $this->issued_at->lte(now());
    }
}
