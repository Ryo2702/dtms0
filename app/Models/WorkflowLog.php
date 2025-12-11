<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowLog extends Model
{
    protected $fillable = [
        'transaction_id',
        'from_state',
        'to_state',
        'action',
        'action_by',
        'remarks',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    /**
     * Get human-readable action description
     */
    public function getActionDescription(): string
    {
        $actor = $this->actor->name ?? 'Unknown';
        
        return match($this->action) {
            'approve' => "{$actor} approved and forwarded",
            'reject' => "{$actor} returned for revision",
            'resubmit' => "{$actor} resubmitted",
            'cancel' => "{$actor} cancelled",
            default => "{$actor} performed {$this->action}",
        };
    }
}
