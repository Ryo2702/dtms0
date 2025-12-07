<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;
    protected $fillable = [
        'document_name',
        'description',
        'status',
    ];

    protected function casts()
    {
        return [
            'status' => 'boolean'
        ];
    }

    protected $attributes = [
        'status' => 1
    ];

    public function scopeActive()
    {
        return $this->where('status', 1);
    }
    public function scopeInactive()
    {
        return $this->where('status', 0);
    }

    public function primaryWorkflow() 
    {
        return $this->hasOne(TransactionWorkflow::class)->where('is_originating', true);    
    }

    public function allWorkflow(){
        return $this->hasMany(TransactionWorkflow::class)->orderBy('sequence_order');
    }
}
