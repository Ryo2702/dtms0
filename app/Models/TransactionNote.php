<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionNote extends Model
{
    protected $fillable = [
        'transaction_id',
        'notes_per_department'
    ];

    public function transaction()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function scopeByTransaction($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    public function scopeLatest($query) 
    {
        return $query->orderBy('created_at', 'desc');
    }
}
