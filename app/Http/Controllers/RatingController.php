<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\SubTask;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class RatingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $teamId = $request->teamId;
    
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $query = SubTask::with(['user:id,employee_id,name', 'team:id,name'])
                ->selectRaw('user_id, team_id, 
                             AVG(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN rating END) AS avg_monthly_rating, 
                             AVG(rating) AS avg_total_rating', [$currentMonth, $currentYear])
                ->groupBy('user_id', 'team_id');
    
    
            if ($teamId !== "all") {
                $query->where('team_id', $teamId);
            }
    
            $subTasks = $query->get();

            $data = $subTasks->map(function ($subTask) use ($currentMonth, $currentYear) {
                // Check if there is a rating for the current month in the rating table for this user
                $userRating = Rating::where('user_id', $subTask->user_id)
                                    ->whereMonth('created_at', $currentMonth)
                                    ->whereYear('created_at', $currentYear)
                                    ->first();
                $monthlyRate= round($userRating ? $userRating->rating : $subTask->avg_monthly_rating,0);
                $editButton = '<button class="btn btn-sm btn-primary edit-btn"  data-emp-name="' .$subTask->user->name . '" data-month-rate="' . $monthlyRate . '" data-user-id="' . $subTask->user_id . '">Edit</button>';

                return [
                    'name' => $subTask->user->name,
                    'employee_id' => $subTask->user->employee_id,
                    'user_id' => $subTask->user->id,
                    'team' => $subTask->team->name,
                    'avg_monthly_rating' => $monthlyRate,
                    'avg_total_rating' => round($subTask->avg_total_rating,0),
                    'action' => $editButton,
                ];
            });
    
            return DataTables::of($data)
            ->addIndexColumn() 
            ->rawColumns(['action']) 
            ->make(true);
        }
        $teams=Team::select('id','name')->get();
        return view('rating.index',compact('teams'));
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
        $authuser=Auth::id();
        $request->validate([
            'user_id' => 'required',
            'user_id' => 'exists:users,id', // Ensure each ID exists in the users table
            'ratingValue' => 'required|integer|max:10',
        ]);
        $currentmonth = now()->format('yy-m'); 

        $rating = Rating::firstOrNew([
            'user_id' => $request->user_id,
            'month'=>$currentmonth
        ]);
        $rating->month=$currentmonth ;
        $rating->rating=$request->ratingValue;
        $rating->updated_by=$authuser;
        $rating->save();
        return response()->json(['success' => "rating updtaed successfully"]);


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
