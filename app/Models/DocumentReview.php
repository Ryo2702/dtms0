<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DocumentReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'document_type',
        'client_name',
        'document_data',
        'official_receipt_number',
        'created_by',
        'assigned_to',
        'current_department_id',
        'original_department_id',
        'status',
        'review_notes',
        'process_time_minutes',
        'submitted_at',
        'reviewed_at',
        'downloaded_at',
        'expires_at',
        'forwarding_chain',
        'is_final_review'
    ];

    protected $casts = [
        'document_data' => 'array',
        'forwarding_chain' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'downloaded_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_final_review' => 'boolean',
        'process_time_minutes' => 'integer'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function currentDepartment()
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    public function originalDepartment()
    {
        return $this->belongsTo(Department::class, 'original_department_id');
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    public function getRemainingTimeAttribute()
    {
        if (!$this->expires_at || $this->isExpired()) {
            return 0;
        }

        return now()->diffInMinutes($this->expires_at, false);
    }

    public function addToForwardingChain($action, $fromUser, $toUser = null, $notes = null, $processTime = null)
    {
        $chain = $this->forwarding_chain ?? [];
        $currentStep = count($chain) + 1;

        // Fix: Ensure processTime is an integer if provided
        if ($processTime !== null) {
            $processTime = (int) $processTime;
        }

        $chainEntry = [
            'step' => $currentStep,
            'action' => $action, // created, submitted_for_review, forwarded, completed, rejected, downloaded
            'from_user_id' => $fromUser->id,
            'from_user_name' => $fromUser->name,
            'from_user_type' => $fromUser->type,
            'from_department' => $fromUser->department?->name,
            'to_user_id' => $toUser?->id,
            'to_user_name' => $toUser?->name,
            'to_user_type' => $toUser?->type,
            'to_department' => $toUser?->department?->name,
            'notes' => $notes,
            'process_time' => $processTime,
            'timestamp' => now()->toISOString(),
            'status' => $this->getStepStatus($action)
        ];

        $chain[] = $chainEntry;

        // Update previous step status to completed if this is a new pending step
        if (count($chain) > 1 && $chainEntry['status'] === 'pending') {
            $chain[count($chain) - 2]['status'] = 'completed';
        }

        $this->update(['forwarding_chain' => $chain]);
    }

    private function getStepStatus($action)
    {
        switch ($action) {
            case 'created':
            case 'downloaded':
            case 'completed':
            case 'rejected':
                return 'completed';
            case 'submitted_for_review':
            case 'forwarded':
                return 'pending';
            default:
                return 'pending';
        }
    }

    // Get current step in the process
    public function getCurrentStepAttribute()
    {
        if (!$this->forwarding_chain) return null;

        $pendingStep = collect($this->forwarding_chain)->firstWhere('status', 'pending');
        return $pendingStep ?? collect($this->forwarding_chain)->last();
    }

    // Get progress percentage
    public function getProgressPercentageAttribute()
    {
        if (!$this->forwarding_chain) return 0;

        $totalSteps = count($this->forwarding_chain);
        $completedSteps = collect($this->forwarding_chain)->where('status', 'completed')->count();

        if ($this->status === 'downloaded') return 100;
        if ($this->status === 'approved') return 90;
        if ($this->status === 'rejected') return 100;

        return $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
    }

    // Scope for filtering
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeDownloaded($query)
    {
        return $query->where('status', 'downloaded');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
