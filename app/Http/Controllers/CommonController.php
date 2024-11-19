<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubTask;
use App\Models\SubTaskUserTimeline;
use Carbon\Carbon;
class CommonController extends Controller
{
    public function calculateTimeLeft($estimated, $worked, $timeDifference)
    {
        $estimatedInSeconds = $this->convertToSeconds($estimated);
        $workedInSeconds = $this->convertToSeconds($worked);

        $remainingSeconds = max($estimatedInSeconds - $workedInSeconds - $timeDifference, 0);

        return gmdate('H:i:s', $remainingSeconds);
    }

    public function calculateNewWorkedTime($worked, $timeDifference)
    {
        $workedInSeconds = $this->convertToSeconds($worked);
        $newTotalWorkedInSeconds = $workedInSeconds + $timeDifference;
        return gmdate('H:i:s', $newTotalWorkedInSeconds);
    }

    public function update_status($id, $status = null, $active_status = null, $reopen_status = null, $extended_status = null)
    {
        $subtask = SubTask::find($id);
        if (!$subtask) {
            return response()->json(['message' => 'Subtask not found'], 404);
        }
        if ($status !== null) {
            $subtask->status = $status;
        }

        if ($active_status !== null) {
            $subtask->active_status = $active_status;
        }

        if ($reopen_status !== null) {
            $subtask->reopen_status = $reopen_status;
        }

        if ($extended_status !== null) {
            $subtask->extended_status = $extended_status;
        }
        $subtask->save();
        return response()->json(['message' => 'Subtask updated successfully']);
    }

    public function getSubtasksData($subtasks)
    {
        $subtasks = $subtasks->get();
        $activeTask = $subtasks->where('status', 1)->where('active_status', 1)->first();
        $lastStartTime = null;
        $timeline_id = null;
        $timeLeft = 0;

        if ($activeTask) {
            $lastStartTimeRecord = SubTaskUserTimeline::where('subtask_id', $activeTask->id)
                ->orderBy('start_time', 'desc')
                ->first();
            $lastStartTime = $lastStartTimeRecord ? Carbon::parse($lastStartTimeRecord->start_time) : null;
            $timeline_id = $lastStartTimeRecord->id ?? null;

            if ($lastStartTime) {
                $timezone = config('app.timezone');
                $lastStartTime = $lastStartTime->setTimezone($timezone);
                $now = Carbon::now()->setTimezone($timezone);
                $timeDifference = $lastStartTime->diffInSeconds($now);

                $timeLeft = $this->calculateTimeLeft($activeTask->estimated_hours, $activeTask->total_hours_worked, $timeDifference);
            }
        }

        $subtasksWithTimeLeft = $subtasks->map(function ($subtask) use ($timeLeft) {
            $estimatedInSeconds = $this->convertToSeconds($subtask->estimated_hours ?? '00:00:00');
            $workedInSeconds = $this->convertToSeconds($subtask->total_hours_worked ?? '00:00:00');
            $remainingSeconds = $estimatedInSeconds - $workedInSeconds;

            $timeLeftFormatted = $remainingSeconds > 0 ? gmdate("H:i:s", $remainingSeconds) : '00:00:00';

            return [
                'subtask' => $subtask,
                'time_left' => $timeLeftFormatted
            ];
        });

        $groupedSubtasks = [
            'To-Do' => $subtasksWithTimeLeft->filter(fn($subtaskData) => $subtaskData['subtask']->status == 0 || ($subtaskData['subtask']->status == 1 && $subtaskData['subtask']->reopen_status == 0 && $subtaskData['subtask']->active_status == 0)),
            'Pending-Approval' => $subtasksWithTimeLeft->filter(fn($subtaskData) => $subtaskData['subtask']->status == 2 && $subtaskData['subtask']->reopen_status == 0),
            'Reopen' => $subtasksWithTimeLeft->filter(fn($subtaskData) => $subtaskData['subtask']->reopen_status == 1 && $subtaskData['subtask']->active_status == 0)
        ];

        return compact('groupedSubtasks', 'activeTask', 'timeLeft', 'lastStartTime', 'timeline_id');
    }




    public function convertToSeconds($time)
    {
        $parts = explode(':', $time);
        if (count($parts) == 3) {
            list($hours, $minutes, $seconds) = $parts;
            return ($hours * 3600) + ($minutes * 60) + $seconds;
        } else {
            list($hours, $minutes) = $parts;
            return ($hours * 3600) + ($minutes * 60);
        }
    }
}
