<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $teamleader=Auth::id();
            $attendanceQuery = EmployeeAttendance::where('date', $request->date); 
            $currentTime = now()->format('H:i'); 
            $currentTime = "16:30";
            $cutoffTime = '13:30';
            if ($request->status === "Absent") {
                $absentUserIds = $attendanceQuery->where(function ($query) use ($currentTime, $cutoffTime) {
                    // Full Day Leave
                    $query->where('day_type', 1)
                          // First Half Leave taken and it's before the cutoff time
                          ->orWhere(function ($query) use ($currentTime, $cutoffTime) {
                              $query->where('day_type', 2)
                                    ->where('half_type', 1)
                                    ->whereRaw('? < ?', [$currentTime, $cutoffTime]);
                          })
                          ->orWhere(function ($query) use ($currentTime, $cutoffTime) {
                            $query->where('day_type', 2)
                                  ->where('half_type', 2)
                                  ->whereRaw('? >= ?', [$currentTime, $cutoffTime]);
                        });
                          
                })->pluck('user_id')->toArray();
            } else {
                // Present: Check for users who took a half-day first half leave after the cutoff time
                $absentUserIds = $attendanceQuery->where(function ($query) use ($currentTime, $cutoffTime) {
                    // Full Day Leave
                    $query->where('day_type', 1)
                          // First Half Leave taken and it's after the cutoff time
                          ->orWhere(function ($query) use ($currentTime, $cutoffTime) {
                              $query->where('day_type', 2)
                                    ->where('half_type', 1)
                                    ->whereRaw('? < ?', [$currentTime, $cutoffTime]);
                          })
                          ->orWhere(function ($query) use ($currentTime, $cutoffTime) {
                            $query->where('day_type', 2)
                                  ->where('half_type', 2)
                                  ->whereRaw('? >= ?', [$currentTime, $cutoffTime]);
                        });
                })->pluck('user_id')->toArray();
            }
            $usersQuery = User::with(['team'])
            ->whereHas('team', function ($query) use ($teamleader) {
                $query->where('reporting_user_id', $teamleader);
            });
            if($request->status === "Absent"){
                $usersQuery ->whereIn('id',$absentUserIds);
            }else{
                $usersQuery ->whereNotIn('id',$absentUserIds);
            }
            $usersQuery ->get();
            return DataTables::of($usersQuery)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($user) {
                    return '<input type="checkbox" name="user_ids[]" value="' . $user->id . '">';
                })
                ->addColumn('status', function ($user) use ($request) {
                    $badgeClass = $request->status === 'Present' ? 'badge-success' : 'badge-danger';
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($request->status) . '</span>';
                })
                ->rawColumns(['checkbox', 'status'])
                ->make(true);
        }
        return view('employeeAttendance.index');

    }

    public function updateStatus(Request $request){

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
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id', // Ensure each ID exists in the users table
            'date' => 'required|date',
            'attendanceType' => 'required|string',
        ]);
        $ids = $request->ids;
        $date = $request->date;
        $attendanceType= $request->attendanceType;
        $status= $request->statusFilter;
        $halfDay = $request->halfDay;
            foreach ($ids as $id) {
                $attendance = EmployeeAttendance::firstOrNew([
                    'user_id' => $id,
                    'date' => $date,
                ]);
                $attendance->day_type = $attendanceType;
                $attendance->half_type = $halfDay; 
                $attendance->updated_by = Auth::id();
                if($status=="Present" &&  $attendanceType ==2){
                    $attendance->half_type = $halfDay==1?2:1; 
                } 
                $attendance->save(); 
            }
            if($status=="Present" &&  $attendanceType ==1 ){
                foreach ($ids as $id) {
                    $employee = EmployeeAttendance::where('user_id',$id)->where('date',$date);
                    $employee->delete();
                }
            }
    
        return response()->json(['success' => true]);
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
