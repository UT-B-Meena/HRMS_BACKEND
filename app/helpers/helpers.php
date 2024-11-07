<?php

use App\Models\SubTask;

if (!function_exists('getErrorMessage')) {
    function getErrorMessage($errors, $field)
    {
        return $errors->has($field) ? $errors->first($field) : '';
    }
}

function status($subtask_Id, $status, $activeStatus) {
    $subtask = SubTask::find($subtask_Id);
    if ($subtask) {
        $subtask->status = $status;
        $subtask->active_status = $activeStatus;
        $subtask->save();
        return response()->json(['success' => true, 'message' => 'Task status updated successfully']);
    }
    return response()->json(['success' => false, 'message' => 'Task not found']);
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