<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionWorkflow extends Model
{
    protected $fillable = [
        'transaction_type_id',
        'department_id',
        'sequence_order',
        'is_originating',
        'proccess_time_value',
        'process_time_unit',
        'next_step_on_approval',
        'next_step_on_rejection',
        'allow_cycles',
        'max_cycle_count'
    ];

    protected function casts()
    {
        return [
            'is_originating' => 'boolean'
        ];
    }


    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);    
    }

    public function department() 
    {
        return $this->belongsTo(Department::class);    
    }

    public function scopeByTransactionType($query, $transactionTypeId) 
    {
        return $query->where('transaction_type_id', $transactionTypeId);
    }


    public function scopeOrderedBySequence($query) 
    {
        return $this->orderBy('sequence_order', 'asc');
    }

    public function scopeOriginating($query)  {
        return $query->where('is_originating', 1);
    }

    public function nextStepOnApproval() {
        return $this->belongsTo(TransactionWorkflow::class, 'next_step_on_approval_id');
    }

    public function nextStepOnRejection() {
        return $this->belongsTo(TransactionWorkflow::class, 'next_step_on_rejection_id');
    }

    public function previousStage(){
        return $this->hasMany(TransactionWorkflow::class, 'next_step_on_approval_id');
    }
}
