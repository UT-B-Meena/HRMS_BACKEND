<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Product;
use App\Models\Project;
use App\Models\SubTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class TaskController extends Controller
{

    public function index()
    {
        $products = Product::select('id', 'name')->get();

        return view('tasks.task', compact('products'));
    }

    public function getTasksData()
    {

        $tasks = Task::with(['product:id,name', 'project:id,name', 'createdBy:id,name', 'updatedBy:id,name'])->get();

        return DataTables::of($tasks)
            ->addIndexColumn()
            ->addColumn('product', fn($task) => $task->product->name ?? '')
            ->addColumn('project', fn($task) => $task->project->name ?? '')
            ->addColumn('action', function ($task) {
                return '<button onclick="showTask(' . $task->id . ')" class="btn btn-primary">Edit</button>
                    <button class="btn btn-danger" onclick="deleteTask(' . $task->id . ')">Delete</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function create(Request $request)
    {
        $productId = $request->input('product_id');
        $projects = Project::select('id', 'name')->where('product_id', $productId)->get();
        return $projects;
    }


    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required',
            'project_id' => 'required',
        ]);

        $validatedData['created_by'] = Auth::id();
        $validatedData['updated_by'] = Auth::id();
        $validatedData['status'] = 0;


        $task = Task::create($validatedData);

        if ($task) {
            Session::flash('success', 'Task created successfully!');
            return redirect()->back();
        } else {
            Session::flash('error', 'Failed to create task. Please try again.');
            return redirect()->back();
        }
    }


    public function show(string $id)
    {

        $task = Task::findOrFail($id);
        $projects = Project::select('id', 'name')->where('product_id', $task->product_id)->get();
        if ($task) {

            return response()->json([
                'task' => $task,
                'projects' => $projects,
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

        $task = Task::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'product' => 'required',
            'project' => 'required',
        ]);

        $task->name = $validatedData['name'];
        $task->product_id = $validatedData['product'];
        $task->project_id = $validatedData['project'];
        $task->updated_by = Auth::id();


        if ($task->save()) {

            Session::flash('success', 'Task updated successfully!');
            return redirect()->back();
        } else {

            Session::flash('error', 'Failed to update task. Please try again.');
            return redirect()->back();
        }
    }



    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);

        $exists = SubTask::where('task_id', $id)->exists();
        if($exists){
            if ($task->delete()) {
                return response()->json(['status' => 200, 'message' => 'Task deleted successfully.'], 200);
            } else {
                return response()->json(['status' => 500, 'message' => 'Failed to delete task.'], 500);
            }
        }else{
            return response()->json(['status' => 302, 'message' => 'Task cannot be deleted because it is associated.'],  302);
        }

    }
}
