<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Product;
use App\Models\subtask;
use App\Models\Team;
use App\Models\Project;
use App\Models\User;
use App\Models\EmployeeLeave;
use App\Models\SubTaskUserTimeline;
use Yajra\DataTables\DataTables;
use DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = Auth::user()->role_id;

        $products = Product::get();
        // ----------------------------------------------------------------------------------
        // Attendance Section
        // Current time
        $currentTime = Carbon::now();
        $cutoffTime = Carbon::createFromTime(13, 01); // 1:30 PM cutoff

        // Total strength of all users
        $totalStrength = User::count();

        // Total absent employees with full-day leave (day_type = 1)
        $totalAbsentEmployees = EmployeeLeave::where('day_type', 1) // Full day leave
            ->orWhere(function ($query) use ($currentTime, $cutoffTime) {
                // Half day (first half) leave counted as absent only if after 1:30 PM
                $query->where('day_type', 2)
                    ->where('half_type', 1)
                    ->whereRaw('? < ?', [$currentTime, $cutoffTime]);
            })
            ->count();
        $totalPresentEmployees = $totalStrength - $totalAbsentEmployees;
        // dd($totalAbsentEmployees);

        // Team-wise attendance with absent/present employee details
        $teamWiseAttendance = User::with(['team', 'attendances'])
            ->get()
            ->groupBy('team_id')
            ->map(function ($teamUsers) use ($currentTime, $cutoffTime) {
                $teamId = $teamUsers->first()->team_id;
                $teamName = $teamUsers->first()->team->name ?? 'N/A';

                // Classify employees as absent or present based on leave type and check time
                $absentEmployees = [];
                $presentEmployees = [];

                foreach ($teamUsers as $user) {
                    $isAbsent = false;

                    foreach ($user->attendances as $attendance) {
                        if ($attendance->day_type === 1) {
                            // Full day leave: always absent
                            $isAbsent = true;
                            break;
                        } elseif ($attendance->day_type === 2) {
                            if ($attendance->half_type === 1) {
                                // First half leave: Absent if checked after 1:30 PM
                                $isAbsent = $currentTime->lt($cutoffTime);
                            } elseif ($attendance->half_type === 2) {
                                // Second half leave: Absent only if checked after 1:30 PM
                                $isAbsent = $currentTime->gte($cutoffTime) ? false : true;
                            }
                        }
                    }

                    // Add to appropriate list based on absence status
                    if ($isAbsent) {
                        $absentEmployees[] = [
                            'employee_id' => $user->id,
                            'employee_name' => $user->name,
                        ];
                    } else {
                        $presentEmployees[] = [
                            'employee_id' => $user->id,
                            'employee_name' => $user->name,
                        ];
                    }
                }

                return [
                    'team_id' => $teamId,
                    'team_name' => $teamName,
                    'total_team_count' => count($absentEmployees) + count($presentEmployees),
                    'team_absent_count' => count($absentEmployees),
                    'team_present_count' => count($presentEmployees),
                    'absent_employees' => $absentEmployees,
                    'present_employees' => $presentEmployees,
                ];
            });

        // Final results
        $result = [
            'total_strength' => $totalStrength,
            'total_present_employees' => $totalPresentEmployees,
            'total_absent_employees' => $totalAbsentEmployees,
            'team_wise_attendance' => $teamWiseAttendance->values(),
        ];

        // Output or return result
        // Attendance Section
        // ----------------------------------------------------------------------------------
        // Employee Section
        // Helper function to convert H:i:s to seconds
        function timeToSeconds($time)
        {
            list($hours, $minutes, $seconds) = explode(':', $time);
            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        // Fetch subtasks for the authenticated user
        $subTasks = SubTask::with('project')
            ->where('user_id', Auth::user()->id)
            ->where('status', 1)
            ->get()
            ->map(function ($subTask) {

                $estimatedSeconds = timeToSeconds($subTask->estimated_hours);
                $workedSeconds = timeToSeconds($subTask->total_hours_worked);
                $extendedSeconds = timeToSeconds($subTask->extended_hours);


                $remainingSeconds = $estimatedSeconds - $workedSeconds;

                if ($extendedSeconds > $estimatedSeconds) {
                    $remainingHours = "00:00";
                } else {

                    $remainingHours = gmdate("H:i", max(0, $remainingSeconds));
                }

                return [
                    'project_name' => $subTask->project->name,
                    'subtask_name' => $subTask->name,
                    'remaining_hours' => $remainingHours,
                ];
            });



        $dailyBreakdown = SubTaskUserTimeline::with(['project', 'subtask'])
            ->where('user_id', Auth::user()->id)
            ->whereDate('created_at', Carbon::today())
            ->get()
            ->map(function ($subTask) {
                $startTime = Carbon::parse($subTask->start_time);
                $endTime = Carbon::parse($subTask->end_time);

                // Format times as h:i A
                $formattedStartTime = $startTime->format('h:i A');
                $formattedEndTime = $endTime->format('h:i A');

                // Calculate the duration and format as HH:MM:SS
                $duration = $startTime->diff($endTime);
                $formattedDuration = sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);

                return [
                    'startTime' => $formattedStartTime, 
                    'endTime' => $formattedEndTime,     
                    'project_name' => $subTask->project->name, 
                    'subtask_name' => $subTask->subtask->name, 
                    'duration' => $formattedDuration,     
                ];
            });
        // Employee Section

        if ($role == '3') {
            return view('dashboard.index_PM', compact('products', 'result')); //Mangers
        } elseif ($role == '2') {
            return view('dashboard.index_TL'); //Team Lead

        } else {
            return view('dashboard.index_EM', compact('subTasks','dailyBreakdown')); // Employees

        }
    }
    public function viewProducts(Request $request, $id)
    {
        // Fetch products and teams
        $projects = Project::all(); // Use all() to get all projects
        $teams = Team::all();
        $rating = round(SubTask::where('product_id', $id)->avg('rating'));      // Use all() to get all teams

        return view('dashboard.product_PM', compact('projects', 'teams', 'id', 'rating'));
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
                'status' => ($subTask->status == 0) ? 'In Progress' : 'Completed',
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
            $allUsersInTeam = User::whereHas('team', function ($query) use ($teamId) {
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
