<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'report_type',
        'default_columns',
        'available_filters',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_columns' => 'array',
            'available_filters' => 'array',
        ];
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'template_id');
    }
}
