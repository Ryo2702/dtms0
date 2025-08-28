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
        'department_id',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }

    /**
     * Get department name from archived data
     */
    public function getDepartmentNameAttribute(): ?string
    {
        return $this->department?->name;
    }

    /**
     * Scope to get archives by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to get archives by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
