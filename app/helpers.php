<?php

if (!function_exists('formatTime')) {
    function formatTime($value, $unit)
    {
        return $value . ' ' . ucfirst($unit);
    }
}

if (!function_exists('formatRemainingTime')) {
    function formatRemainingTime($minutes)
    {
        $absMinutes = abs($minutes);
        if ($absMinutes < 60) {
            return $absMinutes . ' minute' . ($absMinutes !== 1 ? 's' : '');
        } elseif ($absMinutes < 1440) {
            $hours = floor($absMinutes / 60);
            return $hours . ' hour' . ($hours !== 1 ? 's' : '');
        } elseif ($absMinutes < 10080) {
            $days = floor($absMinutes / 1440);
            return $days . ' day' . ($days !== 1 ? 's' : '');
        } else {
            $weeks = floor($absMinutes / 10080);
            $remainingDays = floor(($absMinutes % 10080) / 1440);

            if ($remainingDays > 0) {
                return $weeks . ' week' . ($weeks !== 1 ? 's' : '') . ' ' . $remainingDays . ' day' . ($remainingDays !== 1 ? 's' : '');
            } else {
                return $weeks . ' week' . ($weeks !== 1 ? 's' : '');
            }
        }
    }
}

if (!function_exists('formatAccomplishedTime')) {
    function formatAccomplishedTime($startTime, $endTime)
    {
        $diff = $endTime->diff($startTime);

        if ($diff->d >= 7) {
            $weeks = floor($diff->d / 7);
            $remainingDays = $diff->d % 7;

            if ($remainingDays > 0) {
                return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ' .
                    $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
            } else {
                return $weeks . ' week' . ($weeks > 1 ? 's' : '');
            }
        } elseif ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ' . $diff->i . ' min';
        } else {
            return $diff->i . ' minute' . ($diff->i !== 1 ? 's' : '');
        }
    }
}