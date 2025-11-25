<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
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

    public function workflows() 
    {
        return $this->hasMany(TransactionWorkflow::class);    
    }
}
