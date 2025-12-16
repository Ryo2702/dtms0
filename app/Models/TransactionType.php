<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'transaction_name',
        'description',
        'status',
        'workflow_config',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'workflow_config' => 'array',
        ];
    }

    protected $attributes = [
        'status' => 1,
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function workflows() {
        return $this->hasMany(Workflow::class);
    }

    public function defaultWorkflow(){
        return $this->hasOne(Workflow::class)->where('is_default', true);
    }

    public function activeWorkflows(){
        return $this->hasMany(Workflow::class)->where('status', true);
    }

    /**
     * Get workflow steps from config
     */
    public function getWorkflowSteps(): array
    {
        return $this->workflow_config['steps'] ?? [];
    }

    /**
     * Get transitions map from config
     */
    public function getTransitions(): array
    {
        return $this->workflow_config['transitions'] ?? [];
    }

    /**
     * Get the first department in workflow
     */
    public function getOriginatingDepartment(): ?array
    {
        $steps = $this->getWorkflowSteps();
        return $steps[0] ?? null;
    }

    /**
     * Get the initial state for new transactions
     */
    public function getInitialState(): string
    {
        $firstStep = $this->getOriginatingDepartment();
        if ($firstStep) {
            return 'pending_' . $this->sanitizeDepartmentName($firstStep['department_name']) . '_review';
        }
        return 'pending';
    }

    /**
     * Sanitize department name for state string
     */
    public function sanitizeDepartmentName(string $name): string
    {
        return str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
    }

    /**
     * Check if workflow is configured
     */
    public function hasWorkflowConfigured(): bool
    {
        return !empty($this->workflow_config) && !empty($this->workflow_config['steps']);
    }
}
