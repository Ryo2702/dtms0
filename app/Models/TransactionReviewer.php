<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TransactionReviewer extends Model
{
    //start na me

    protected $fillable = [
        'transaction_id',
        'reviewer_id',
        'department_id',
        'status',
        'reviewer_notes',
        'process_time_value',
        'process_time_unit',
        'due_date',
        'is_overdue',
        'reviewed_at',
        'iteration_number',
        'rejection_reason',
        'resubmission_deadline',
        'previous_reviewer_id'
    ];

    protected function casts()
    {
        return [
            'due_date' => 'datetime',
            'reviewed_at' => 'datetime',
            'is_overdue' => 'boolean'
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }


    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    //scope
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    public function scopeReSubmit($query)
    {
        return $query->where('status', 're_submit');
    }
    public function scopeReturningToOrignating($query)
    {
        return $query->where('status', 'returning_to_originating');
    }
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', 1);
    }

    public function previousReviewer() {
        return $this->belongsTo(User::class, 'previous_reviewer_id');
    }
    public function incrementIteration() {
        $this->increment('iteration_number');
    }

    public function reject($reason) {
        $this->update([
            'status' => 're_submit',
            'rejection_reason' => $reason,
            'resubmission_deadline' => Carbon::now()->addDays(3),
        ]);
    }
    //status
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    public function isReSubmit(): bool
    {
        return $this->status === 're_submit';
    }
    public function isApproved(): bool
    {
        return $this->status === 'aprroved';
    }
        public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    public function isReturnToOriginating(): bool
    {
        return $this->status === 'return_to_originating';
    }
}
