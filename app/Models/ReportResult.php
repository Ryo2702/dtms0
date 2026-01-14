<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportResult extends Model
{
    protected $fillable = [
        'report_id',
        'data',
        'summary',
        'total_records',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'summary' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
