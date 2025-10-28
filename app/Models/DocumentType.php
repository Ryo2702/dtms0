<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = [
        'title',
        'description',
        'department_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function documents()  {
        return $this->hasMany(Document::class, 'document_type', 'title');
    }

    public function scopeActive($query){
        return $query->where('is_active', true);
    }
}
