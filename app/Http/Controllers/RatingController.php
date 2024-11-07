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
    
            $currentmonth = now()->format('Y-m'); 
            $rating= Rating::with(['user.team'])->where('month',$currentmonth);
            if ($teamId !== "all") {
                $rating->whereHas('user', function($query) use ($teamId) {
                    $query->where('team_id', $teamId);
                });
            }
            $ratingData = $rating->get();
            return DataTables::of($ratingData)
            ->addIndexColumn() 
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-primary edit-btn"  data-emp-name="' .$row->user->name . '" data-month-rate="' . $row->rating . '" data-user-id="' . $row->user_id . '">Edit</button>';
            })
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
        $currentmonth = now()->format('Y-m'); 
        $averageRating = Rating::where('user_id', $request->user_id)->whereNot('month',$currentmonth)->sum('rating');
        $avgcount = Rating::where('user_id', $request->user_id)->count();
        $avg=$averageRating + $request->ratingValue;
        $averages= round($avg/ $avgcount,0);
        $rating = Rating::firstOrNew([
            'user_id' => $request->user_id,
            'month'=>$currentmonth
        ]);
        $rating->month=$currentmonth;
        $rating->rating=$request->ratingValue;
        $rating->average=$averages;
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
