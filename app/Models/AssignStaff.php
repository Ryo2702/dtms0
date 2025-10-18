<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignStaff extends Model
{
    protected $fillable = [
        'full_name',
        'position',
        'role',
        'department_id',
        'is_active'
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }
}
