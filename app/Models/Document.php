<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'document_id',
        'client_name',
        'title',
        'reviewer_id',
        'process_time',
        'time_unit',
        'time_value',
        'priority',
        'assigned_staff',
        'attachment_path',
        'created_via',
        'department_id',
        'status'
    ];

    protected $casts = [
        'process_time' => 'integer',
        'time_value' => 'integer',
        'priority' => 'string',
        'time_unit' => 'string',
        'created_via' => 'string',
        'status' => 'string'
    ];

    public function reviewer(){
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function department()  {
        return $this->belongsTo(Department::class);
    }

    public function scopeDepartment($query, $departmentId) {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByStatus($query, $status)  {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)  {
        return $query->where('priority', $priority);
    }

    public function getFormattedProcessTimeAttribute() {
        return $this->time_value . ' ' . $this->time_unit;
    }

    public function getPriorityBadgeClassAttribute() {
        return match ($this->attributes['priority'] ?? 'low') {
            'low' => 'secondary',
            'normal' => 'success',
            'medium' => 'info',
            'high' => 'danger',
            'urgent' => 'danger',
            default => 'secondary'
        };
    }

    public function hasAttachment()  {
        return !is_null($this->attachment_path);
    }

    public function getAttachmentUrlAttribute()  {
        return $this->attachment_path ? asset('storage/'. $this->attachment_path) : null;
    }

}
