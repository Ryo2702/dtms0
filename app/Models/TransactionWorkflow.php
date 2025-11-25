<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionWorkflow extends Model
{
    protected $fillable = [
        'transaction_type_id',
        'department_id',
        'sequence_order',
        'is_originating'
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
}
