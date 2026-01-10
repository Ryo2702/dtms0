<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionReviewer extends Model
{
    protected $fillable = [
        'transaction_id',
        'reviewer_id',
        'department_id',
        'status',
        'due_date',
        'is_overdue',
        'reviewed_at',
        'iteration_number',
        'rejection_reason',
        'resubmission_deadline',
        'previous_reviewer_id',
        'received_status',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reviewed_at' => 'datetime',
        'resubmission_deadline' => 'datetime',
        'received_at' => 'datetime',
        'is_overdue' => 'boolean',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function previousReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_reviewer_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true);
    }

    public function scopeForReviewer($query, int $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return $this->is_overdue || ($this->due_date && $this->due_date->isPast());
    }

    public function markAsOverdue(): void
    {
        $this->update(['is_overdue' => true]);
    }

    public function isReceived(): bool
    {
        return $this->received_status === 'received';
    }

    public function isNotReceived(): bool
    {
        return $this->received_status === 'not_received';
    }

    public function isPendingReceive(): bool
    {
        return $this->received_status === 'pending';
    }
}
