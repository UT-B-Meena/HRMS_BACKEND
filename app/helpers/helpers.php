<?php

use App\Models\SubTask;

if (!function_exists('getErrorMessage')) {
    function getErrorMessage($errors, $field)
    {
        return $errors->has($field) ? $errors->first($field) : '';
    }
}


function timeDifference($estimated, $worked) {
    [$estHours, $estMinutes] = explode(':', $estimated);
    $estimatedSeconds = $estHours * 3600 + $estMinutes * 60;

    [$workHours, $workMinutes, $workSeconds] = explode(':', $worked);
    $workedSeconds = $workHours * 3600 + $workMinutes * 60 + $workSeconds;

    $diffSeconds = $estimatedSeconds - $workedSeconds;

    $hours = floor($diffSeconds / 3600);
    $minutes = floor(($diffSeconds % 3600) / 60);
    $seconds = $diffSeconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}