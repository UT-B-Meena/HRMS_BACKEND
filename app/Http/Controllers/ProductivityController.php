<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\SubTask;
use Illuminate\Http\Request;
use Carbon\CarbonInterval;
use Yajra\DataTables\Facades\DataTables;

class ProductivityController extends Controller
{



    public function index(Request $request)
    {
        $teams = Team::all();

        if ($request->ajax()) {
            $query = SubTask::select('user_id')
                ->selectRaw("
                    CONCAT(
                        LPAD(FLOOR(SUM(estimated_hours) / 10000), 2, '0'), ':',
                        LPAD(FLOOR((SUM(estimated_hours) % 10000) / 100), 2, '0'), ':',
                        LPAD(SUM(estimated_hours) % 100, 2, '0')
                    ) AS total_estimated_hours
                ")
                ->selectRaw("
                    CONCAT(
                        LPAD(FLOOR(SUM(total_hours_worked) / 10000), 2, '0'), ':',
                        LPAD(FLOOR((SUM(total_hours_worked) % 10000) / 100), 2, '0'), ':',
                        LPAD(SUM(total_hours_worked) % 100, 2, '0')
                    ) AS total_hours_worked
                ")
                ->selectRaw("
                    CONCAT(
                        LPAD(FLOOR(SUM(extended_hours) / 10000), 2, '0'), ':',
                        LPAD(FLOOR((SUM(extended_hours) % 10000) / 100), 2, '0'), ':',
                        LPAD(SUM(extended_hours) % 100, 2, '0')
                    ) AS total_extended_hours
                ")
                ->with(['user:id,employee_id,name'])
                ->groupBy('user_id');

            if ($request->has('team_id') && $request->team_id != '') {
                $query->where('team_id', $request->team_id);
            }

            if ($request->has('month') && $request->month != '') {
                $query->whereMonth('created_at', $request->month);
            }
            if ($request->has('year') && $request->year != '') {
                $query->whereYear('created_at', $request->year);
            }

            $productivity = $query->get();
            return DataTables::of($productivity)
                ->addIndexColumn()
                ->make(true);
        }

        return view('productivity.index', compact('teams'));
    }






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
}
