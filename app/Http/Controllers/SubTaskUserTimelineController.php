<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubTask;
use App\Models\SubTaskUserTimeline;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SubTaskUserTimelineController extends Controller
{

    protected $commonController;

    public function __construct(CommonController $commonController)
    {
        $this->commonController = $commonController;
    }

    public function getClosedTasks(Request $request)
    {
        $userId = auth()->id();
        try {
            if ($request->ajax()) {
                $closedTasks = SubTask::where('status', 3)
                    ->where('user_id', $userId)
                    ->with(['product', 'project', 'task']);
                if ($request->has('search') && $request->search != '') {
                    $searchTerm = $request->search;

                    $closedTasks->where(function ($query) use ($searchTerm) {
                        $query->whereHas('product', function ($q) use ($searchTerm) {
                            $q->where('name', 'like', "%{$searchTerm}%");
                        })
                            ->orWhereHas('project', function ($q) use ($searchTerm) {
                                $q->where('name', 'like', "%{$searchTerm}%");
                            })
                            ->orWhereHas('task', function ($q) use ($searchTerm) {
                                $q->where('name', 'like', "%{$searchTerm}%");
                            })
                            ->orWhere('name', 'like', "%{$searchTerm}%");
                    });
                }

                return DataTables::of($closedTasks->get())
                    ->addIndexColumn()
                    ->addColumn('product', function ($row) {
                        return $row->product ? $row->product->name : 'N/A';
                    })
                    ->addColumn('project', function ($row) {
                        return $row->project ? $row->project->name : 'N/A';
                    })
                    ->addColumn('task', function ($row) {
                        return $row->task ? $row->task->name : 'N/A';
                    })
                    ->addColumn('subtask', function ($row) {
                        return $row->name;
                    })
                    ->addColumn('estimated_time', function ($row) {
                        return $row->estimated_hours;
                    })
                    ->addColumn('time_taken', function ($row) {
                        return $row->total_hours_worked;
                    })
                    ->addColumn('rating', function ($row) {
                        return $row->rating;
                    })
                    ->make(true);

            }
        } catch (\Exception $e) {
            \Log::error('Error retrieving closed tasks: ' . $e->getMessage());
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateSubtasks(Request $request)
    {
        $user = Auth::user();
        $subtask_id = $request->subtask_id;
        $status = $request->status;
        $active_status = $request->active_status;
        $last_start_time_uf = $request->last_start_time;
        $last_start_time = Carbon::parse($last_start_time_uf);
        $timeline_id = $request->timeline_id;
        $subtask = SubTask::find($subtask_id);
        if ($status == 1 && $active_status == 1) {
            $subtask->status = $status;
            $subtask->active_status = $active_status;
            $subtask->save();
            $Timeline = SubTaskUserTimeline::create([
                'user_id' => $subtask->user_id,
                'product_id' => $subtask->product_id,
                'project_id' => $subtask->project_id,
                'task_id' => $subtask->task_id,
                'subtask_id' => $subtask->id,
                'start_time' => Carbon::now(),
                'end_time' => null
            ]);


        } else if ($status == 1 && $active_status == 0) {
            $current_time = Carbon::now();
            $time_difference = $last_start_time->diffInSeconds($current_time);
            $new_total_hours_worked = $this->commonController->calculateNewWorkedTime($subtask->total_hours_worked, $time_difference);
            $subtask->total_hours_worked = $new_total_hours_worked;
            $subtask->status = $status;
            $subtask->active_status = $active_status;
            $subtask->save();

            $subtask_timeline = SubTaskUserTimeline::find($timeline_id);
            $subtask_timeline->end_time = Carbon::now()->format('Y-m-d H:i:s');
            $subtask_timeline->save();


        } else {
            $current_time = Carbon::now();
            $time_difference = $last_start_time->diffInSeconds($current_time);
            $new_total_hours_worked = $this->commonController->calculateNewWorkedTime($subtask->total_hours_worked, $time_difference);
            $estimated_hours = $subtask->estimated_hours;
            $new_total_hours_worked_seconds = $this->commonController->convertToSeconds($new_total_hours_worked);
            $estimated_hours_seconds = $this->commonController->convertToSeconds($estimated_hours);
            $subtask->command = $request->comment;
            $remaining_seconds = $estimated_hours_seconds - $new_total_hours_worked_seconds;

            if ($remaining_seconds < 0) {
                $extended_seconds = abs($remaining_seconds);
                $extended_status = 1;
                $subtask->extended_status = $extended_status;

                $extended_hours = gmdate("H:i:s", $extended_seconds);
            } else {
                $extended_hours = "00:00:00";
            }

            $subtask->total_hours_worked = $new_total_hours_worked;
            $subtask->extended_hours = $extended_hours;
            $subtask->status = $status;
            $subtask->active_status = $active_status;
            $subtask->reopen_status = 0;
            $subtask->save();

            $subtask_timeline = SubTaskUserTimeline::find($timeline_id);
            $subtask_timeline->end_time = Carbon::now()->format('Y-m-d H:i:s');
            $subtask_timeline->save();

        }
        

        $subtasks = SubTask::with([
            'product:id,name',
            'project:id,name',
            'task:id,name',
            'team:id,name',
            'assigned_user:id,name',
            'user:id,name',
            'createdBy:id,name',
            'updatedBy:id,name'
        ])->where('user_id', $user->id)->orderBy('updated_at', 'desc');

        $data = $this->commonController->getSubtasksData($subtasks);
        return response()->json([
            'html' => view('subtasks.partials.ongoing_section', $data)->render(),
            'status' => $status,
            'active_status' => $active_status,
            'timeline' => $Timeline ?? null,
        ]);

    }



}
