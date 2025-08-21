<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserArchive extends Model
{
    protected $fillable = [
        'user_id',
        'municipal_id',
        'name',
        'email',
        'department',
        'type',
        'reason',
        'deactivated_at',
        'deactivated_by'
    ];

    protected $casts = [
        'deactivated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }
}
