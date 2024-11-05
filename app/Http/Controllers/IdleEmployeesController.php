<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class IdleEmployeesController extends Controller
{



    public function index(Request $request)
    {
        $teams = Team::all();
        $userId = Auth::id();
        if ($request->ajax()) {
            $today = Carbon::today();
            $authUser = Auth::user();

            $query = User::select('id', 'employee_id', 'name', 'team_id', 'role_id')
                ->with(['team:id,name'])
                ->whereDoesntHave('subTasktimeline', function ($subTasktimelineQuery) use ($today) {
                    $subTasktimelineQuery->whereColumn('user_id', 'users.id')
                        ->whereDate('created_at', $today);
                })
                ->whereDoesntHave('attendances', function ($attendanceQuery) use ($today) {
                    $attendanceQuery->whereColumn('user_id', 'users.id')
                        ->whereDate('date', $today);
                });

            if ($authUser->role_id == 3) {
                $query->where('team_id', $authUser->team_id);
            }

            if ($request->has('team_id') && $request->team_id != '') {
                $query->where('team_id', $request->team_id);
            }

            $idle_employees = $query->get();

            return DataTables::of($idle_employees)
                ->addIndexColumn()
                ->make(true);
        }

        return view('idle_employees.index', compact('teams','userId'));
    }



    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
