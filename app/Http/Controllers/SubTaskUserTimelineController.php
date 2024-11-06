<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubTask;
use Yajra\DataTables\Facades\DataTables;

class SubTaskUserTimelineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
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
                            ->orWhere('name', 'like', "%{$searchTerm}%"); // Search in the subtask name
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
            // Log the error message
            \Log::error('Error retrieving closed tasks: ' . $e->getMessage());
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
        }
    }
}
