<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'name',
        'description',
        'report_type',
        'template_id',
        'filters',
        'columns',
        'sort_by',
        'date_range_start',
        'date_range_end',
        'created_by',
        'is_public',
        'schedule_frequency',
        'last_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'columns' => 'array',
            'date_range_start' => 'datetime',
            'date_range_end' => 'datetime',
            'last_generated_at' => 'datetime',
        ];
    }

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function results()
    {
        return $this->hasMany(ReportResult::class);
    }

    public function latestResult()
    {
        return $this->hasOne(ReportResult::class)->latest();
    }
}
