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


        // statistics
        $taskCounts = SubTask::select(
            DB::raw('COUNT(*) as total_task_count'),
            DB::raw('SUM(status = 1) as in_progress_task_count'),
            DB::raw('SUM(status = 3) as completed_task_count')
        )
            ->where('user_id', Auth::user()->id)
            ->first();

        // Result array
        $statisticsResult = [
            'total_task_count' => $taskCounts->total_task_count,
            'in_progress_task_count' => $taskCounts->in_progress_task_count,
            'completed_task_count' => $taskCounts->completed_task_count,
        ];
        // statistics
        // Employee Section




        // TL Section
        // Attendance
        $authUserTeamId = Auth::user()->team_id;
        $currentTime = Carbon::now();
        $cutoffTime = Carbon::createFromTime(13, 30); // 1:30 PM cutoff
        $today = Carbon::today(); // Current date without time


        $totalStrength = User::where('team_id', $authUserTeamId)->count();

        // 2. Get absent employees' details from EmployeeLeave
        $absentEmployees = EmployeeLeave::with('user:id,name') // Load user details
            ->whereDate('created_at', $today)
            ->where(function ($query) use ($currentTime, $cutoffTime) {
                $query->where('day_type', 1) // Full-day leave
                    ->orWhere(function ($subQuery) use ($currentTime, $cutoffTime) {
                        $subQuery->where('day_type', 2)
                            ->where('half_type', 1)
                            ->whereRaw('? < ?', [$currentTime, $cutoffTime]);
                    });
            })
            ->whereHas('user', function ($query) use ($authUserTeamId) {
                $query->where('team_id', $authUserTeamId);
            })
            ->get()
            ->map(function ($leave) {
                return [
                    'employee_id' => $leave->user->id,
                    'employee_name' => $leave->user->name,
                    'status' => 'Absent',
                ];
            });


        $absentEmployeeIds = $absentEmployees->pluck('employee_id')->toArray();

        // 3. Get present employees' details by excluding absent ones
        $presentEmployees = User::where('team_id', $authUserTeamId)
            ->whereNotIn('id', $absentEmployeeIds)
            ->get(['id', 'name'])
            ->map(function ($user) {
                return [
                    'employee_id' => $user->id,
                    'employee_name' => $user->name,
                    'status' => 'Present',
                ];
            });


        $attendanceList = $absentEmployees->isEmpty() ? $presentEmployees : $absentEmployees->merge($presentEmployees)
            ->map(function ($employee) {
                return [
                    'employee_id' => $employee['employee_id'],
                    'employee_name' => $employee['employee_name'],
                    'status' => $employee['status'],
                ];
            });
        $attendanceList = $attendanceList->map(function ($list) {
            $nameParts = explode(' ', $list['employee_name']);

            // Generate initials
            $initials = '';
            if (count($nameParts) > 1) {
                // For multiple parts, take the first letter of each
                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
            } else {
                // For single-part names, take the first two letters
                $initials = strtoupper(substr($nameParts[0], 0, 2));
            }

            $list['initials'] = $initials;
            return $list;
        });


        $totalAbsentEmployees = $absentEmployees->count();
        $totalPresentEmployees = $presentEmployees->count();
        $presentPercentage = $totalStrength ? round(($totalPresentEmployees / $totalStrength) * 100, 2) : 0;
        $absentPercentage = $totalStrength ? round(($totalAbsentEmployees / $totalStrength) * 100, 2) : 0;


        $resultss = [
            'total_strength' => $totalStrength,
            'total_present_employees' => $totalPresentEmployees,
            'total_absent_employees' => $totalAbsentEmployees,
            'present_percentage' => $presentPercentage,
            'absent_percentage' => $absentPercentage,
            'attendance_list' => $attendanceList,
        ];

        // Attendance

        // project Section
        $projects = SubTask::with(['product', 'user'])
            ->where('team_id', $authUserTeamId)
            ->get()
            ->groupBy('product.id') // Group by product ID
            ->map(function ($subtasks, $productId) {
                // Extract the product name and ID (assuming all subtasks have the same product name and ID)
                $productName = $subtasks->first()->product->name;
                $productId = $subtasks->first()->product->id;

                // Count unique users working on this product
                $numberOfPeople = $subtasks->pluck('user.id')->unique()->count();

                // Calculate total subtasks and completed subtasks
                $totalSubtasks = $subtasks->count();
                $completedSubtasks = $subtasks->where('status', 3)->count();

                // Calculate completion rate as the percentage of completed subtasks
                $completionRate = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;

                return [
                    'product_name' => $productName,
                    'product_id' => $productId,
                    'number_of_people' => $numberOfPeople,
                    'completion_rate' => round($completionRate),
                ];
            })
            ->values();

        $project_Section = [
            'projects' => $projects,
        ];
        // project Section
        // TL Section

        if ($role == '3') {
            return view('dashboard.index_PM', compact('products', 'result')); //Managers
        } elseif ($role == '1') {
            return view('dashboard.index_TL', compact('resultss', 'project_Section', 'products')); //Team Lead

        } else {
            return view('dashboard.index_EM', compact('subTasks', 'dailyBreakdown', 'statisticsResult')); // Employees

        }
    }
    public function viewProducts(Request $request, $id)
    {
        // Fetch products and teams
        $projects = Project::all();
        $teams = Team::all();
        $completedSubtasks = SubTask::where('product_id', $id)
            ->where('status', 3) // Only consider completed tasks
            ->whereNotNull('rating') // Ensure rating exists
            ->count(); // Count completed subtasks with a rating

        $totalSubtasks = SubTask::where('product_id', $id)->count(); // Total subtasks for the product

        // Calculate the completion rate
        $rating = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;      // Use all() to get all teams

        return view('dashboard.product_PM', compact('projects', 'teams', 'id', 'rating'));
    }
    public function viewtlProducts(Request $request, $id)
    {
        // Fetch products and teams
        $projects = Project::all();
        $teams = Team::all();
        $completedSubtasks = SubTask::where('product_id', $id)
            ->where('status', 3) // Only consider completed tasks
            ->whereNotNull('rating') // Ensure rating exists
            ->count(); // Count completed subtasks with a rating

        $totalSubtasks = SubTask::where('product_id', $id)->count(); // Total subtasks for the product

        // Calculate the completion rate
        $rating = round($totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0);      // Use all() to get all teams

        return view('dashboard.product_TL', compact('projects', 'teams', 'id', 'rating'));
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
                    ->orWhereHas('user', function ($q) use ($searchValue) {
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
                'date' => optional($subTask->created_at)->format('d-m-Y'),
                'employee_name' => optional($subTask->user)->name,
                'team_name' => optional($subTask->team)->name,
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
                'team_id' => $teamId,
                'team_name' => $teamName,
                'users' => $userDetails,
                'user_count' => $userCount,
                'total_user_count' => $totalUserCount,
            ];
        });

        // Optionally, reset the keys
        $groupedTeams = $groupedTeams->values();
        $html = view('pm.utlization_section', compact('groupedTeams'))->render();

        return response()->json($html);
    }
    public function fetchEmployeeTaskData(Request $request)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Get task counts grouped by week within the current month
        $weekTaskCounts = SubTask::select(
            DB::raw('WEEK(created_at, 3) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 3) + 1 as week_of_month'),
            DB::raw('COUNT(*) as total_task_count'),
            DB::raw('SUM(status = 1) as in_progress_task_count'),
            DB::raw('SUM(status = 3) as completed_task_count')
        )
            ->where('user_id', Auth::user()->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('week_of_month')
            ->orderBy('week_of_month')
            ->get()
            ->mapWithKeys(function ($task) {
                return [
                    'week_' . $task->week_of_month => [
                        'total_task_count' => $task->total_task_count,
                        'in_progress_task_count' => $task->in_progress_task_count,
                        'completed_task_count' => $task->completed_task_count,
                    ]
                ];
            });

        // Output the result
        dd($weekTaskCounts);

        // return response()->json($statisticsResult);
    }
    public function fetchEmployeeListData(Request $request)
    {

        $employeeName = $request->input('name'); // Retrieve the name input
        $authUserTeamId = Auth::user()->team_id;
        $currentTime = Carbon::now();
        $cutoffTime = Carbon::createFromTime(13, 30); // 1:30 PM cutoff
        $today = Carbon::today(); // Current date without time

        // Absent employees based on EmployeeLeave entries
        $absentEmployees = EmployeeLeave::with('user:id,name')
            ->whereDate('created_at', $today)
            ->where(function ($query) use ($currentTime, $cutoffTime) {
                $query->where('day_type', 1) // Full day leave
                    ->orWhere(function ($subQuery) use ($currentTime, $cutoffTime) {
                        $subQuery->where('day_type', 2)
                            ->where('half_type', 1)
                            ->whereRaw('? < ?', [$currentTime, $cutoffTime]);
                    });
            })
            ->whereHas('user', function ($query) use ($authUserTeamId, $employeeName) {
                $query->where('team_id', $authUserTeamId);

                // Apply name filter if provided
                if (!empty($employeeName)) {
                    $query->where('name', 'LIKE', "%{$employeeName}%");
                }
            })
            ->get()
            ->map(function ($leave) {
                return [
                    'employee_id' => $leave->user->id,
                    'employee_name' => $leave->user->name,
                    'status' => 'Absent',
                ];
            });

        // Absent employee IDs for exclusion in present employees list
        $absentEmployeeIds = $absentEmployees->pluck('employee_id')->toArray();

        // Present employees based on team and name filter, excluding absent IDs
        $presentEmployees = User::where('team_id', $authUserTeamId)
            ->whereNotIn('id', $absentEmployeeIds)
            ->when($employeeName, function ($query) use ($employeeName) {
                return $query->where('name', 'LIKE', "%{$employeeName}%");
            })
            ->get(['id', 'name'])
            ->map(function ($user) {
                return [
                    'employee_id' => $user->id,
                    'employee_name' => $user->name,
                    'status' => 'Present',
                ];
            });

        // Combine present and absent lists
        $attendanceList = $absentEmployees->isEmpty() ? $presentEmployees : $absentEmployees->merge($presentEmployees)
            ->map(function ($employee) {
                return [
                    'employee_id' => $employee['employee_id'],
                    'employee_name' => $employee['employee_name'],
                    'status' => $employee['status'],
                ];
            });

        $attendanceList = $attendanceList->map(function ($list) {
            $nameParts = explode(' ', $list['employee_name']);

            // Generate initials
            $initials = '';
            if (count($nameParts) > 1) {
                // For multiple parts, take the first letter of each
                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
            } else {
                // For single-part names, take the first two letters
                $initials = strtoupper(substr($nameParts[0], 0, 2));
            }

            $list['initials'] = $initials;
            return $list;
        });

        $resultss = [
            'attendance_list' => $attendanceList,
        ];

        // Debugging output
        // dd($resultss);
        // Render the HTML view with attendance data
        $html = view('em.attendance_section', compact('resultss'))->render();

        // Return as JSON response
        return response()->json($html);

    }
    public function viewtlProduct(Request $request, $id)
    {
        $project_id = $request->input('project_id');
        $team_id = Auth::user()->team_id;
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

        // Define status mapping for search terms
        $statusMap = [
            'Pending' => 0,
            'in progress' => 1,
            'pending for approval' => 2,
            'completed' => 3
        ];

        // Apply search filter
        if ($searchValue) {
            $query->where(function ($query) use ($searchValue, $statusMap) {
                $query->where('name', 'like', "%$searchValue%")
                    ->orWhereHas('user', function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('product', function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('project', function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%$searchValue%");
                    })
                    ->orWhere(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y')"), 'like', "%$searchValue%");

                // Search for status by mapping the string to the corresponding numeric value
                foreach ($statusMap as $statusText => $statusValue) {
                    if (stripos($searchValue, $statusText) !== false) {
                        $query->orWhere('status', $statusValue);
                    }
                }
            });
        }

        // Get the results
        $subTasks = $query->get()->map(function ($subTask) {
            // Status Mapping
            $statusMap = [
                0 => 'Pending',
                1 => 'In Progress',
                2 => 'Pending For Approval',
                3 => 'Completed',
            ];

            return [
                'id' => $subTask->id,
                'date' => optional($subTask->created_at)->format('d-m-Y'),
                'employee_name' => optional($subTask->user)->name,
                'project_name' => optional($subTask->project)->name,
                'name' => $subTask->name,
                'status' => $statusMap[$subTask->status] ?? 'Unknown', // Default to 'Unknown' if status is not valid
            ];
        });

        return DataTables::of($subTasks)->make(true);
    }

    public function fetchTlProductData(Request $request)
    {
        $authUserTeamId = Auth::user()->team_id;
        $productIds = $request->input('productIds'); // Expecting an array of product IDs

        // Validate that productIds is an array and not empty
        if (empty($productIds) || !is_array($productIds)) {
            return response()->json(['error' => 'No products selected'], 400);
        }

        // Fetch the relevant projects based on selected product IDs
        $projects = SubTask::with(['product', 'user'])
            ->where('team_id', $authUserTeamId)
            ->whereIn('product_id', $productIds) // Filter by selected product IDs
            ->get()
            ->groupBy('product.id') // Group by product ID
            ->map(function ($subtasks, $productId) {
                // Extract the product name and ID (assuming all subtasks have the same product name and ID)
                $productName = $subtasks->first()->product->name;
                $productId = $subtasks->first()->product->id;

                // Count unique users working on this product
                $numberOfPeople = $subtasks->pluck('user.id')->unique()->count();

                // Calculate total subtasks and completed subtasks
                $totalSubtasks = $subtasks->count();
                $completedSubtasks = $subtasks->where('status', 3)->count();

                // Calculate completion rate as the percentage of completed subtasks
                $completionRate = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;

                return [
                    'product_name' => $productName,
                    'product_id' => $productId,
                    'number_of_people' => $numberOfPeople,
                    'completion_rate' => round($completionRate),
                ];
            })
            ->values();

        $project_Section = [
            'projects' => $projects,
        ];

        // Render the view with the data and return the HTML response
        $html = view('tl.product_section', compact('project_Section'))->render();

        return response()->json($html);
    }


}
