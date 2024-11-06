<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Product;
use App\Models\SubTask;
use App\Models\SubTaskUserTimeline;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $projects = Project::with(['product:id,name', 'createdBy:id,name', 'updatedBy:id,name'])->get();
            return DataTables::of($projects)
                ->addColumn('action', function ($project) {
                    return '<button data-id="' . $project->id . '" class="btn btn-primary edit-btn">Edit</button>
                        <button data-id="' . $project->id . '" class="btn btn-danger delete-btn">Delete</button>';
                })
                ->editColumn('status', function ($project) {
                    return Project::STATUS[$project->status]; // Display the readable status
                })
                ->make(true);
        }
        return view('projects.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'product_id' => 'required|exists:products,id']);

        $project = Project::create([
            'name' => $request->name,
            'product_id' => $request->product_id,
            'status' => 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => 'Project created successfully', 'project' => $project]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $project = Project::findOrFail($id);
        return response()->json($project);
    }

    /**
     * Update the specified resource in a storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate(['name' => 'required', 'product_id' => 'required|exists:products,id']);
        $project = Project::findOrFail($id);

        $project->update([
            'name' => $request->name,
            'product_id' => $request->product_id,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => 'Project updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Project::findOrFail($id);
        $product->delete();

        return response()->json(['success' => 'Project deleted successfully.']);
    }

    public function projectRequest(Request $request)
    {

        $projects = Project::pluck('name', 'id');
        $users = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'employee_id' => $user->employee_id,
                'display' => "{$user->name} - {$user->employee_id}", // Create display value
            ];
        });
        if ($request->ajax()) {

            $data = SubTask::with([
                'project:id,name as project_name',
                'user:id,name,team_id as assignee',
                'user.team:id,name as team_name',
                'assigned_user:id,name as assigned_by'
            ])->where('status', 2);

            if ($request->has('project_id') && $request->project_id != '') {
                $data->where('project_id', $request->project_id);
            }

            if ($request->has('user_id') && $request->user_id != '') {
                $data->where('user_id', $request->user_id);
            }

            if ($request->date) {
                $data->whereDate('updated_at', $request->date);
            }

            if ($request->has('search') && $request->search != '') {
                $searchTerm = $request->search;
                $data->where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', "%$searchTerm%")
                          ->orWhereHas('user', function($q) use ($searchTerm) {
                              $q->where('name', 'like', "%$searchTerm%");
                          })
                          ->orWhereHas('user.team', function($q) use ($searchTerm) {
                              $q->where('name', 'like', "%$searchTerm%");
                          })
                          ->orWhereHas('project', function($q) use ($searchTerm) {
                              $q->where('name', 'like', "%$searchTerm%");
                          })
                          ->orWhereHas('assigned_user', function($q) use ($searchTerm) {
                              $q->where('name', 'like', "%$searchTerm%");
                          });
                });
            }


            return DataTables::of($data->get())
                ->addIndexColumn()
                ->editColumn('date', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('Y-m-d') : 'N/A';
                })
                ->editColumn('subtask_name', function ($row) {
                    return $row->name;
                })
                ->editColumn('assignee', function ($row) {
                    return $row->user->name ?? 'N/A';
                })
                ->editColumn('team_name', function ($row) {
                    return $row->user->team->team_name ?? 'N/A';
                })
                ->editColumn('assigned_by', function ($row) {
                    return $row->assignedUser->name ?? 'N/A';
                })
                ->addColumn('project_name', function ($row) {
                    return $row->project->project_name ?? 'N/A';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('project_request.update', $row->id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i> Update</a>';
                })
                ->make(true);
        }

        return view('projects.project_request', compact('projects', 'users'));

    }

    public function getProjectRequestData($id)
    {
        $subtask = SubTask::with([
            'product:id,name as product_name',
            'project:id,name as project_name',
            'task:id,name as task_name',
            'user:id,name as assignee',
            'assigned_user:id,name as assigned_by'
        ])->find($id);

        if ($subtask) {
            return view('projects.edit_project_request', compact('subtask'));
        }

        return response()->json(['message' => 'Subtask not found'], 404);
    }

    public function updateProjectRequest(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'remark' => 'nullable|string|max:255',
        ]);

        $subtask = SubTask::find($id);

        if (!$subtask) {
            return redirect()->back()->with('error', 'Subtask not found.');
        }

        $subtask->rating = $request->input('rating');
        $subtask->remark = $request->input('remark');
        if ($request->input('action') === 'reopen') {
            $subtask->status = 0;
            $subtask->reopen_status = 1;
        } elseif ($request->input('action') === 'close') {
            $subtask->status = 3;
        }

        $subtask->save();

        return redirect()->route('project_request', $id)->with('success', 'Subtask updated successfully.');
    }

    public function getProjectStatusData(Request $request)
    {

        $projects = Project::pluck('name', 'id');
        $products = Product::pluck('name', 'id');
        $users = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'employee_id' => $user->employee_id,
                'display' => "{$user->name} - {$user->employee_id}", // Create display value
            ];
        });
        if ($request->ajax()) {
            $status = $request->input('status', 0); 

            $query = SubTaskUserTimeline::with(['product:id,name', 'project:id,name', 'task:id,name', 'subtask:id,name', 'user:id,name'])
                ->whereHas('subtask', function($q) use ($status) {
                    $q->where('status', $status);
                });

                if ($request->has('product_id') && $request->product_id != '') {
                    $query->where('product_id', $request->product_id);
                }

                if ($request->has('project_id') && $request->project_id != '') {
                    $query->where('project_id', $request->project_id);
                }
    
                if ($request->has('user_id') && $request->user_id != '') {
                    $query->where('user_id', $request->user_id);
                }
    
                if ($request->date) {
                    $query->whereDate('created_at', $request->date);
                }

                if ($request->has('search') && $request->search != '') {
                    $searchTerm = $request->search;
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('created_at', 'like', "%$searchTerm%") // Search by created_at if necessary
                          ->orWhereHas('product', function($q) use ($searchTerm) {
                              $q->where('name', 'like', "%$searchTerm%");
                          })
                          ->orWhereHas('project', function($q) use ($searchTerm) {
                              $q->where('name', 'like', "%$searchTerm%");
                          })
                          ->orWhereHas('subtask', function($q) use ($searchTerm) {
                              $q->where('name', 'like', "%$searchTerm%");
                          })
                          ->orWhereHas('user', function($q) use ($searchTerm) {
                              $q->where('name', 'like', "%$searchTerm%");
                          });
                    });
                }

                return DataTables::of($query)
                ->addIndexColumn() 
                ->editColumn('date', function ($timeline) {
                    return $timeline->created_at->format('Y-m-d'); 
                })
                ->editColumn('product', function ($timeline) {
                    return $timeline->product->name ?? '-';
                })
                ->editColumn('project', function ($timeline) {
                    return $timeline->project->name ?? '-';
                })
                ->editColumn('subtask', function ($timeline) {
                    return $timeline->subtask->name ?? '-';
                })
                ->editColumn('user', function ($timeline) {
                    return $timeline->user->name ?? '-';
                })
                ->editColumn('estimated_hours', function ($timeline) {
                    $estimated_hours = $timeline->subtask->estimated_hours;
                    return $estimated_hours ? $estimated_hours->format('H:i:s'): '-';
                })
                ->editColumn('start_time', function ($timeline) {
                    $startTime = is_string($timeline->start_time) ? Carbon::parse($timeline->start_time) : $timeline->start_time;
                    return $startTime ? $startTime->format('Y-m-d H:i:s') : '-';
                })
                ->editColumn('end_time', function ($timeline) {
                    $endTime = is_string($timeline->end_time) ? Carbon::parse($timeline->end_time) : $timeline->end_time;
                    return $endTime ? $endTime->format('Y-m-d H:i:s') : '-';
                })
                ->editColumn('task_duration', function ($timeline) { 
                    return $timeline->subtask->total_hours_worked ?? '-';
                })
                ->make(true);
            
        }

        return view('projects.project_status', compact('products','projects', 'users'));
    }


}