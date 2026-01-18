<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'document_id',
        'transaction_status',
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
        'data'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
        'created_at' => 'datetime'
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function document() {
        return $this->belongsTo(DocumentReview::class, 'document_id');
    }
}
