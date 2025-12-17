<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = [
        'transaction_name',
        'description',
        'difficulty',
        'workflow_config',
        'is_default',
        'status'
    ];

    protected $casts = [
        'workflow_config' => 'array',
        'is_default' => 'boolean',
        'status' => 'boolean'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id)) {
                $prefix = 'TS';
                $last = self::orderBy('created_at', 'desc')->first();
                $next = 1;
                if ($last && preg_match('/^TS(\d{4,})$/', $last->id, $m)) {
                    $next = ((int) $m[1]) + 1;
                }
                $model->id = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            }
        });
    }
    public function documentTags()
    {
        return $this->belongsToMany(DocumentTag::class, 'document_tag_workflow')
            ->withPivot('is_required')
            ->withTimestamps();
    }

    public function getWorkflowSteps()
    {
        return $this->workflow_config['steps'] ?? [];
    }

    public function getTransition()
    {
        return $this->workflow_config['transitions'] ?? [];
    }

    public function hasWorkConfigured()
    {
        return !empty($this->workflow_config['steps']);
    }

    public function getInitialState()
    {
        $steps = $this->getWorkflowSteps();

        if (empty($steps)) {
            return null;
        }

        $firstStep = reset($steps);

        return 'pending_' . strtolower(str_replace(' ', '_', $firstStep['department_name'])) . '_review';
    }

    public function getDifficultBadgeClass()
    {
        return match ($this->difficulty) {
            'complex' => 'badge-warning',
            'highly_technical' => 'badge-error',
            default => 'badge-success'
        };
    }

    public function getTotalEstimatedDays(): float
    {
        $totalDays = 0;
        foreach ($this->getWorkflowSteps() as $step) {
            $value = $step['process_time_value'] ?? 0;
            $unit = $step['process_time_unit'] ?? 'days';

            if ($unit === 'hours') {
                $totalDays += $value / 24;
            } elseif ($unit === 'weeks') {
                $totalDays += $value * 7;
            } else {
                $totalDays += $value;
            }
        }
        return round($totalDays, 1);
    }

    public function getTimeDifficulty(): string
    {
        $totalDays = $this->getTotalEstimatedDays();

        if ($totalDays >= 35) return 'highly_technical';
        if ($totalDays >= 14) return 'complex';
        return 'simple';
    }

    public function getTimeDifficultyBadgeClass(): string
    {
        return match ($this->getTimeDifficulty()) {
            'complex' => 'badge-warning',
            'highly_technical' => 'badge-error',
            default => 'badge-success'
        };
    }

    public function document_type()
    {
        return $this->belongsToMany(DocumentTag::class, 'document_tag_workflow')->withPivot('is_required')->withTimestamps();
    }

    public function requiredDocumentTags()
    {
        return $this->documentTags()->wherePivot('is_required', true);
    }

    public function getTagDepartments()
    {
        return $this->documentTags()
            ->with('department')
            ->get()
            ->pluck('department')
            ->unique();
    }

    public function hasDocumentTag($tagId)
    {
        return $this->documentTags()->where('document_tags.id', $tagId)->exists();
    }

    public function syncDocumentTags(array $tags): void
    {
        $syncData = [];
        foreach ($tags as $tag) {
            $tagId = is_array($tag) ? $tag['id'] : $tag;
            $isRequired = is_array($tag) ? ($tag['is_required'] ?? false) : false;
            $syncData[$tagId] = ['is_required' => $isRequired];
        }
        $this->documentTags()->sync($syncData);
    }
}
