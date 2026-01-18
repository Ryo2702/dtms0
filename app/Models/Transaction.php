<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use function Symfony\Component\Clock\now;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'level_of_urgency',
        'workflow_id',
        'workflow_snapshot',
        'total_workflow_steps',
        'assign_staff_id',
        'department_id',
        'origin_department_id',
        'current_workflow_step', 
        'transaction_status',
        'receiving_status',
        'current_state',
        'revision_number',
        'created_by',
        'submitted_at',
        'completed_at',
        'received_at',
        'workflow_history',
        'custom_document_tags',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'received_at' => 'datetime',
            'workflow_snapshot' => 'array',
            'workflow_history' => 'array',
            'custom_document_tags' => 'array'
        ];
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function assignStaff()
    {
        return $this->belongsTo(AssignStaff::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documentTags() {
        return $this->hasMany(DocumentTag::class, 'document_tags_id');
    }

    public function reviewers()  {
        return $this->hasMany(TransactionReviewer::class);
    }

    public function currentReviewer()  {
        return $this->hasOne(TransactionReviewer::class)
            ->where('status', 'pending')
            ->latest();
    }

    public function transactionLogs()
    {
        return $this->hasMany(TransactionLog::class)->orderBy('created_at', 'desc');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function originDepartment() {
        return $this->belongsTo(Department::class, 'origin_department_id');
    }

    // Receiving status helpers
    public function isPendingReceiving(): bool
    {
        return $this->receiving_status === 'pending';
    }

    public function isReceived(): bool
    {
        return $this->receiving_status === 'received';
    }

    public function isNotReceived(): bool
    {
        return $this->receiving_status === 'not_received';
    }

    // Scopes
    public function scopeInProgress($query)
    {
        return $query->where('transaction_status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('transaction_status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('transaction_status', 'overdue');
    }

    public function scopeCancelled($query)
    {
        return $query->where('current_state', 'cancelled');
    }

    public function scopeHighlyUrgent($query)  {
        return $query->where('level_of_urgency', 'highly_urgent');
    }

    public function scopeUrgent($query){
        return $query->where('level_of_urgency', 'urgent');
    }

    public function scopeNormal($query) {
        return $query->where('level_of_urgency', 'normal');
    }

    // State helpers
    public function isCompleted(): bool
    {
        return $this->current_state === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->current_state === 'cancelled';
    }

    public function isPendingReview(): bool
    {
        return str_starts_with($this->current_state, 'pending_');
    }

    public function isReturnedState(): bool
    {
        return str_starts_with($this->current_state, 'returned_to_');
    }

    public function isUrgent() {
        return in_array($this->level_of_urgency, ['urgent', 'highly_urgent']);
    }

    public function isHighlyUrgent() {
        return $this->level_of_urgency === 'highly_urgent';
    }

    /**
     * Get due date from current reviewer
     */
    public function getDueDateAttribute()
    {
        $currentReviewer = $this->reviewers()
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
        
        return $currentReviewer?->due_date;
    }

    /**
     * Get current department from state
     */
    public function getCurrentDepartmentFromState(): ?string
    {
        if (preg_match('/pending_(.+)_review/', $this->current_state, $matches)) {
            return str_replace('_', ' ', $matches[1]);
        }
        if (preg_match('/returned_to_(.+)/', $this->current_state, $matches)) {
            return str_replace('_', ' ', $matches[1]);
        }
        return null;
    }

    public function getWorkflowSteps()  {
        return $this->workflow_snapshot['steps'] ?? $this->workflow?->getWorkflowSteps() ?? [];
    }

    public function getTransitions() {
        return $this->workflow_snapshot['transitions'] ?? $this->workflow?->getTransition();
    }

    /**
     * Get available actions for current state
     */
    public function getAvailableActions(): array
    {
        $transitions = $this->workflow->getTransition();
        return $transitions[$this->current_state] ?? [];
    }

    /**
     * Increment revision number
     */
    public function incrementRevision(): void
    {
        $this->increment('revision_number');
    }

    //transactionn tracking number
    public static function generateTransactionCode(string $prefix = 'TTN'){
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        $count = static::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%04d-%s', $prefix, $date, $count, $random);
    }
}
