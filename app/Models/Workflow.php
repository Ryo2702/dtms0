<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = [
        'transaction_type_id',
        'name',
        'description',
        'difficulty',
        'workflow_config',
        'is_default',
        'status'
    ];

    protected $casts = [
        'workflow_config' => 'array',
        'is_default' => 'boolean',
        'status' => 'boolean'
    ];

    public function transactionType() {
        return $this->belongsTo(TransactionType::class);
    }

    public function getWorkflowSteps() {
        return $this->workflow_config['steps'] ?? [];
    }

    public function getTransition()  {
       return $this->workflow_config['transitions'] ?? []; 
    }

    public function hasWorkConfigured() {
        return !empty($this->workflow_config['steps']);
    }

    public function getInitialState() {
        $steps = $this->getWorkflowSteps();

        if (empty($steps)) {
            return null;
        }

        $firstStep = reset($steps);

        return 'pending_' . strtolower(str_replace(' ', '_', $firstStep['department_name'])) . '_review';
    }

    public function getDifficultBadgeClass()  {
        return match($this->difficulty){
            'moderate' => 'badge-warning',
            'complex' => 'badge-error',
            default => 'badge-success'
         };
    }
}
