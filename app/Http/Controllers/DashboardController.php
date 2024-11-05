<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Product;
use App\Models\subtask;
use App\Models\Team;
use App\Models\Project;
use App\Models\User;
use Yajra\DataTables\DataTables;
use DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = Auth::user()->role_id;

        $products = Product::get();
        if($role == '1'){
            return view('dashboard.index_PM',compact('products')); //Mangers
        }elseif($role == '2'){
            return view('dashboard.index_TL'); //Team Lead
            
        }else{
            return view('dashboard.index_EM'); // Employees

        }
    }
    public function viewProducts(Request $request, $id)
{
    // Fetch products and teams
    $projects = Project::all(); // Use all() to get all projects
    $teams = Team::all();
    $rating =  round(SubTask::where('product_id',$id)->avg('rating'));      // Use all() to get all teams

    return view('dashboard.product_PM', compact('projects', 'teams', 'id','rating'));
}
public function viewProduct(Request $request, $id)
{
    $project_id = $request->input('project_id');
    $team_id = $request->input('team_id');
    $date = $request->input('date');
    $searchValue = $request->input('search.value', '');

    // Eloquent query with relationships
    $query = SubTask::with(['user', 'product', 'project'])
        ->where('product_id', $id);

    // Apply filters
    if ($project_id) {
        $query->where('project_id', $project_id);
    }
    if ($team_id) {
        $query->where('team_id', $team_id);
    }
    if ($date) {
        $query->whereDate('created_at', $date);
    }

    // Apply search filter
    if ($searchValue) {
        $query->where(function ($query) use ($searchValue) {
            $query->where('name', 'like', "%$searchValue%")
                ->orWhereHas('employee', function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%$searchValue%");
                })
                ->orWhereHas('product', function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%$searchValue%");
                })
                ->orWhereHas('project', function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%$searchValue%");
                })
                ->orWhere(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y')"), 'like', "%$searchValue%");
               
        });
    }

    // Get the results
    $subTasks = $query->get()->map(function ($subTask) {
        return [
            'id' => $subTask->id,
            'date' => optional($subTask->created_at)->format('d-m-Y'), // Date formatted
            'employee_name' => optional($subTask->user)->name,
            'team_name' => optional($subTask->team)->name, // Assuming team relation is defined
            'project_name' => optional($subTask->project)->name,
            'name' => $subTask->name,
            'status' => ($subTask->status == 0)? 'In Progress': 'Completed',
        ];
    });

    return DataTables::of($subTasks)->make(true);
}

public function fetchTeamData(Request $request)
{
    $productId = $request->input('product_id');

    $teamData = SubTask::where('product_id', $productId)->with(['user', 'team'])->get();

    // Group the data by team_id and get user details along with the count
    $groupedTeams = $teamData->groupBy('team_id')->map(function ($subTasks, $teamId) use ($productId) {
        // Retrieve the team name from the first subTask (assuming all have the same team)
        $teamName = $subTasks->first()->team->name;
    
        // Fetch all users associated with this team_id, regardless of product_id (second condition)
        $allUsersInTeam = User::whereHas('team', function($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })->get(['id', 'name']);
    
        // Get details for users assigned to the product
        $userDetails = $subTasks->map(function ($subTask) {
            return [
                'user_id' => $subTask->user->id, 
                'name' => $subTask->user->name,  
            ];
        });
    
        // Count of users assigned to this team for the specific product
        $userCount = $userDetails->count();
    
        // Total count of users in the team (regardless of product association)
        $totalUserCount = $allUsersInTeam->count();
    
        return [
            'team_id' => $teamId,                  // Include the team_id
            'team_name' => $teamName,              // Include the team name
            'users' => $userDetails,               // Users assigned to the product
            'user_count' => $userCount,            // Count of users in this team for the product
            'total_user_count' => $totalUserCount, // Total users in the team (regardless of product)
        ];
    });
    
    // Optionally, reset the keys
    $groupedTeams = $groupedTeams->values();
    $html = view('pm.utlization_section', compact('groupedTeams'))->render();

    return response()->json($html);
}



}
