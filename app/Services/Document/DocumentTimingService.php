<?php

namespace App\Services\Document;

use App\Models\DocumentReview;
use Carbon\Carbon;

class DocumentTimingService
{
    public function calculateDocumentTiming(DocumentReview $review): array
    {
        if (!$review->forwarding_chain) {
            return $this->calculateSimpleTiming($review);
        }

        return $this->calculateStepBasedTiming($review);
    }

    public function calculateMultipleDocumentsTiming($reviews): void
    {
        foreach ($reviews as $review) {
            $timing = $this->calculateDocumentTiming($review);
            
            // Set calculated properties without triggering accessors
            $review->setAttribute('calculated_remaining_minutes', $timing['remaining_minutes']);
            $review->setAttribute('calculated_is_overdue', $timing['is_overdue']);
            $review->setAttribute('calculated_step_data', $timing['step_data'] ?? null);
        }
    }

    private function calculateSimpleTiming(DocumentReview $review): array
    {
        if ($review->due_at) {
            $remainingMinutes = now()->diffInMinutes($review->due_at, false);
            return [
                'remaining_minutes' => round($remainingMinutes),
                'is_overdue' => $remainingMinutes < 0 && !$review->downloaded_at,
                'type' => 'simple'
            ];
        }
        
        return ['remaining_minutes' => 0, 'is_overdue' => false, 'type' => 'simple'];
    }

    private function calculateStepBasedTiming(DocumentReview $review): array
    {
        $currentStep = null;
        $currentStepIndex = null;
        $updatedChain = [];
        
        foreach ($review->forwarding_chain as $index => $step) {
            $stepData = $this->calculateIndividualStepTiming($step, $index, $review);
            $updatedChain[] = array_merge($step, $stepData['step_updates']);
            
            if (($step['status'] ?? 'completed') === 'pending') {
                $currentStep = $stepData;
                $currentStepIndex = $index;
            }
        }

        if (!$currentStep) {
            return [
                'remaining_minutes' => 0,
                'is_overdue' => false,
                'type' => 'step_based',
                'updated_chain' => $updatedChain
            ];
        }

        return [
            'remaining_minutes' => $currentStep['remaining_minutes'],
            'is_overdue' => $currentStep['is_overdue'],
            'step_index' => $currentStepIndex,
            'type' => 'step_based',
            'current_step' => $currentStep,
            'updated_chain' => $updatedChain,
            'step_data' => $currentStep
        ];
    }

    private function calculateIndividualStepTiming(array $step, int $stepIndex, DocumentReview $review): array
    {
        $stepStatus = $step['status'] ?? 'completed';
        $allocatedMinutes = $step['allocated_minutes'] ?? 0;
        $stepStart = Carbon::parse($step['timestamp'] ?? $step['forwarded_at'] ?? $review->submitted_at);
        
        if ($stepStatus === 'pending') {
            $stepDueTime = $stepStart->copy()->addMinutes($allocatedMinutes);
            $remainingMinutes = now()->diffInMinutes($stepDueTime, false);
            
            return [
                'remaining_minutes' => round($remainingMinutes),
                'is_overdue' => $remainingMinutes < 0,
                'step_due_at' => $stepDueTime,
                'step_updates' => [
                    'remaining_time_minutes' => round($remainingMinutes),
                    'is_overdue' => $remainingMinutes < 0,
                    'step_due_at' => $stepDueTime->toISOString()
                ]
            ];
        } else {
            // For completed steps
            $completedAt = Carbon::parse($step['completed_at'] ?? now());
            $accomplishedMinutes = $stepStart->diffInMinutes($completedAt);
            $wasOverdue = $accomplishedMinutes > $allocatedMinutes;
            
            return [
                'accomplished_minutes' => $accomplishedMinutes,
                'was_overdue' => $wasOverdue,
                'step_updates' => [
                    'accomplished_minutes' => $accomplishedMinutes,
                    'accomplished_time' => $this->formatTime($accomplishedMinutes, 'minutes'),
                    'was_overdue' => $wasOverdue,
                    'overdue_by_minutes' => $wasOverdue ? $accomplishedMinutes - $allocatedMinutes : 0
                ]
            ];
        }
    }

    private function formatTime($value, $unit): string
    {
        if ($unit === 'minutes') {
            if ($value >= 1440) {
                $days = floor($value / 1440);
                $remainingHours = floor(($value % 1440) / 60);
                return $days . 'd' . ($remainingHours > 0 ? ' ' . $remainingHours . 'h' : '');
            } elseif ($value >= 60) {
                $hours = floor($value / 60);
                $remainingMinutes = $value % 60;
                return $hours . 'h' . ($remainingMinutes > 0 ? ' ' . $remainingMinutes . 'm' : '');
            } else {
                return $value . 'm';
            }
        }
        
        return $value . ' ' . $unit;
    }
}