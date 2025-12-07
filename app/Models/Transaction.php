<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'document_name', 
        'description',
        'transaction_type_id',
        'assign_staff_id',
        'transaction_status',
        'current_workflow_step',
        'submitted_at',
        'completed_at',
        'workflow_history'
    ];

    protected function casts()  {
        return [
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime'
        ];
    }

    public function transactionType()  {
        return $this->belongsTo(TransactionType::class);
    }

    public function assignStaff() 
    {
        return $this->belongsTo(AssignStaff::class);
    }

    public function department() 
    {
        return $this->belongsTo(Department::class);
    }

    public function notes() {
        return $this->hasMany(TransactionNote::class);
    }

    public function reviews() 
    {
        return $this->hasMany(TransactionReviewer::class);    
    }

    public function scopeInProgress($query) {
        return $query->where('transaction_status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('transaction_status', 'completed');
    }

    public function scopeOverdue($query) {
        return $query->where('transaction_status', 'overdue');
    }

    public function scopeDepartment($query, $departmentId) {
        return $query->where('department_id', $departmentId);
    }

    public function scopeTransanctionType($query, $transactionTypeId)
    {
        return $query->where('transaction_type_id', $transactionTypeId);
    }

    public function isCompleted() 
    {
        return $this->transaction_status === 'completed';
    }

    public function isOverdue() {
        return $this->transaction_status === 'overdue';
    }

    public function currentWorkflow() {
        return $this->belongsTo(TransactionWorkflow::class, 'current_workflow_step');
    }

    public function workflowHistory() {
        return $this->hasMany(WorkflowHistory::class);
    }

    public function canCycle() {
        $currentStep = $this->currentWorkflow;
        return $currentStep && $currentStep->allow_cycles;
    }

    public function moveToNextStep($status){
        $workflow = $this->currentWorkflow;

        if ($status === 'aproved' && $workflow->next_step_on_aprroval_id) {
            $this->update(['current_workflow_step' => $workflow->next_step_on_approval_id]);
        }elseif($status === 're_submit' && $workflow->next_step_on_rejection_id){
            $this->update(['current_workflow_step' => $workflow->next_step_on_rejection_id]);
        }
    }
}
