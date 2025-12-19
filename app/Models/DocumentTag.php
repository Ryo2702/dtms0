<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentTag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status'
    ];

    protected function casts()
    {
        return [
            'status' => 'boolean'
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && !$tag->isDirty('slug')) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }


    public function departments(){
        return $this->belongsToMany(Department::class, 'department_document_tag')->withTimestamps();
    }

    public function workflows()  {
        return $this->belongsToMany(Workflow::class, 'document_tag_workflow')
            ->withPivot('is_required')
            ->withTimestamps();
    
    }


    public function scopeActive($query) {
        return $query->where('status', true);
    }

    public function scopeByDepartment($query, $departmentId) {
        return $query->whereHas('departments', function ($q) use ($departmentId){
            $q->where('departments.id', $departmentId);
        });
    }

    public function isUsedDepartment($departmentId) {
        return $this->departments()->where('departments.id', $departmentId)->exists();
    }
}
