<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\SubTask;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class SubTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
        ])->get();

        $groupedSubtasks = [
            'To-Do' => $subtasks->filter(fn($subtask) => $subtask->status == 0 && $subtask->reopen == 0),
            'On-Going Task' => $subtasks->filter(fn($subtask) => $subtask->status == 1 && $subtask->reopen == 0),
            'Closed' => $subtasks->filter(fn($subtask) => $subtask->status == 3 && $subtask->reopen == 0),
            'Reopen' => $subtasks->filter(fn($subtask) => $subtask->reopen == 1)
        ];

        return view('subtasks.subtask', compact('products', 'teams', 'owners', 'groupedSubtasks'));
    }

    public function getsubTasksData()
    {


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

        $time = "{$validatedData['hours']}:{$validatedData['minutes']}:00";
        $dateTime = \DateTime::createFromFormat('H:i:s', $time);

        $validatedData['estimated_hours'] = $dateTime->format('H:i:s');

        $task = SubTask::create($validatedData);

        if ($task) {
            Session::flash('success', 'Sub task created successfully!');
            return redirect()->back();
        } else {
            Session::flash('error', 'Failed to create sub task. Please try again.');
            return redirect()->back();
        }
    }

    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
