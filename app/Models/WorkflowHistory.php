<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowHistory extends Model
{
    public function transaction() {
        return $this->belongsTo(Transaction::class);
    }

    public function reviewer() {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
