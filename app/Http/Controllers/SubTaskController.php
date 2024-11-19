<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\SubTask;
use App\Models\SubTaskUserTimeline;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class SubTaskController extends Controller
{
    protected $commonController;

    public function __construct(CommonController $commonController)
    {
        $this->commonController = $commonController;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $products = Product::select('id', 'name')->get();
        $teams = Team::select('id', 'name')->get();
        $owners = User::select('id', 'name')->whereIn('role_id', [2, 3])->get();

        $subtasks = SubTask::with([
            'product:id,name',
            'project:id,name',
            'task:id,name',
            'team:id,name',
            'assigned_user:id,name',
            'user:id,name',
            'createdBy:id,name',
            'updatedBy:id,name'
        ])

            ->when($user->role_id == 3, function ($query) use ($user) {
                $query->where('team_id', $user->team_id);
            })
            ->when($user->role_id == 4, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });



        if ($user->role_id == '4') {
            $data = $this->commonController->getSubtasksData($subtasks);
            return view('subtasks.employee_subtask', $data);
        } else {
            $subtasks = $subtasks->get();
            $groupedSubtasks = [
                'To-Do' => $subtasks->filter(fn($subtask) => $subtask->status == 0 && $subtask->reopen_status == 0),
                'On-Going Task' => $subtasks->filter(fn($subtask) => $subtask->status == 1 && $subtask->reopen_status == 0),
                'Closed' => $subtasks->filter(fn($subtask) => $subtask->status == 3 && $subtask->reopen_status == 0),
                'Reopen' => $subtasks->filter(fn($subtask) => $subtask->reopen_status == 1 && $subtask->reopen_status != 0)
            ];
            return view('subtasks.subtask', compact('products', 'teams', 'owners', 'groupedSubtasks'));
        }
    }

    function getSubtaskFilter(Request $request)
    {
        $user = Auth::user();
        $team_id = $request->input('team_id');
        $priority = $request->input('priority');
        $search_value = $request->input('search_value');

        $subtasks = SubTask::with([
            'product:id,name',
            'project:id,name',
            'task:id,name',
            'team:id,name',
            'assigned_user:id,name',
            'user:id,name',
            'createdBy:id,name',
            'updatedBy:id,name'
        ])

            ->when(Auth::user()->role_id == 3, function ($query) {
                $query->where('team_id', Auth::user()->team_id);
            })
            ->when($team_id, function ($query) use ($team_id) {
                $query->where('team_id', $team_id);
            })
            ->when($priority, function ($query) use ($priority) {
                $query->where('priority', $priority);
            })
            ->when($search_value, function ($query) use ($search_value) {
                $query->where(function ($q) use ($search_value) {
                    $q->whereHas('product', fn($q) => $q->where('name', 'like', "%$search_value%"))
                        ->orWhereHas('project', fn($q) => $q->where('name', 'like', "%$search_value%"))
                        ->orWhereHas('task', fn($q) => $q->where('name', 'like', "%$search_value%"))
                        ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%$search_value%"))
                        ->orWhereHas('assigned_user', fn($q) => $q->where('name', 'like', "%$search_value%"))
                        ->orWhere('name', 'like', "%$search_value%");
                });
            })
            ->get();

        $groupedSubtasks = [
            'To-Do' => $subtasks->filter(fn($subtask) => $subtask->status == 0 && $subtask->reopen_status == 0),
            'On-Going Task' => $subtasks->filter(fn($subtask) => $subtask->status == 1 && $subtask->reopen_status == 0),
            'Closed' => $subtasks->filter(fn($subtask) => $subtask->status == 3 && $subtask->reopen_status == 0),
            'Reopen' => $subtasks->filter(fn($subtask) => $subtask->reopen_status == 1 && $subtask->reopen_status != 0)
        ];

        $html = view('subtasks.subtask_section', compact('groupedSubtasks'))->render();
        return response()->json($html);
    }
    public function create(Request $request)
    {
        $projectId = $request->input('project_id');
        $tasks = Task::select('id', 'name')->where('project_id', $projectId)->get();
        return $tasks;
    }

    public function team_emp(Request $request)
    {
        $teamId = $request->input('team_id');
        $users = User::select('id', 'name')->where(['team_id' => $teamId, 'role_id' => 4])->get();
        return $users;
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required',
            'project_id' => 'required',
            'task_id' => 'required',
            'description' => 'required',
            'team_id' => 'required',
            'assigned_user_id' => 'required',
            'user_id' => 'required',
            'dead_line' => 'required',
            'hours' => 'required|integer|min:0',
            'minutes' => 'required|integer|min:0|max:59',
            'priority' => 'required'
        ]);

        $validatedData['created_by'] = Auth::id();
        $validatedData['updated_by'] = Auth::id();
        $validatedData['status'] = 0;

        $hours = str_pad((int) $validatedData['hours'], 2, '0', STR_PAD_LEFT);   // Pad with zero if length is 1
        $minutes = str_pad((int) $validatedData['minutes'], 2, '0', STR_PAD_LEFT);

        $time = "{$hours}:{$minutes}:00";

        $dateTime = \DateTime::createFromFormat('H:i:s', $time);

        if ($dateTime) {
            $validatedData['estimated_hours'] = $dateTime->format('H:i:s');
        } else {
            $validatedData['estimated_hours'] = '00:00:00';
        }

        $exists = SubTask::where(['task_id' => $validatedData['task_id'], 'name' => $validatedData['name']])->exists();
        if (!$exists) {
            $task = SubTask::create($validatedData);

            if ($task) {
                return response()->json(['status' => 200, 'message' => 'Sub Task Added successfully.'], 200);
            } else {
                return response()->json(['status' => 500, 'message' => 'Failed to add sub task.'], 500);
            }
        } else {
            return response()->json(['status' => 422, 'message' => 'Sub Task name already exits.'], 422);
        }
    }

    public function show(string $id)
    {

        $subtask = SubTask::findOrFail($id);
        $projects = Project::select('id', 'name')->where('product_id', $subtask->product_id)->get();
        $tasks = Task::select('id', 'name')->where('project_id', $subtask->project_id)->get();
        $users = User::select('id', 'name')->where('team_id', $subtask->team_id)->get();
        if ($subtask) {

            return response()->json([
                'subtask' => $subtask,
                'projects' => $projects,
                'tasks' => $tasks,
                'users' => $users,
                'status' => 200
            ], 200);
        } else {
            return response()->json([
                'error' => 'Task not found',
                'status' => 200
            ], 404);
        }
    }


    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required',
            'project_id' => 'required',
            'task_id' => 'required',
            'description' => 'required',
            'team_id' => 'required',
            'assigned_user_id' => 'required',
            'user_id' => 'required',
            'dead_line' => 'required',
            'hours' => 'required|integer|min:0',
            'minutes' => 'required|integer|min:0|max:59',
            'priority' => 'required'
        ]);


        $task = SubTask::find($id);

        if (!$task) {
            return response()->json(['status' => 404, 'message' => 'Sub Task not found.'], 404);
        }

        $validatedData['updated_by'] = Auth::id();

        $hours = str_pad((int) $validatedData['hours'], 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((int) $validatedData['minutes'], 2, '0', STR_PAD_LEFT);
        $time = "{$hours}:{$minutes}:00";
        $dateTime = \DateTime::createFromFormat('H:i:s', $time);

        $validatedData['estimated_hours'] = $dateTime ? $dateTime->format('H:i:s') : '00:00:00';


        $exists = SubTask::where('name', $validatedData['name'])
            ->where('id', '!=', $id)
            ->exists();

        if (!$exists) {

            $task->update($validatedData);

            return response()->json(['status' => 200, 'message' => 'Sub Task updated successfully.'], 200);
        } else {
            return response()->json(['status' => 422, 'message' => 'Sub Task name already exists.'], 422);
        }
    }

    public function destroy(string $id)
    {
        $subtask = SubTask::findOrFail($id);
        if ($subtask->delete()) {
            return response()->json(['status' => 200, 'message' => 'Sub task deleted successfully.'], 200);
        } else {
            return response()->json(['status' => 500, 'message' => 'Failed to delete sub task.'], 500);
        }
    }

    
}
