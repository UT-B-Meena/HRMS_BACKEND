<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IdleEmployeesController;
use App\Http\Controllers\ProductivityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SubTaskUserTimelineController;


Route::middleware(['web'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});


Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::group(['as' => 'pm.'], routes: function() {
        Route::get('/Products/{id}', [DashboardController::class, 'viewProduct'])->name('products');
        Route::get('/Product/{id}', [DashboardController::class, 'viewProducts'])->name('product');
        Route::get('/utilizeteamdata', [DashboardController::class, 'fetchTeamData'])->name('utilizeteamdata');
        Route::get('/attendancedata', [DashboardController::class, 'fetchAttendanceData'])->name('attendancedata');
    });
    Route::group(['as' => 'em.'], routes: function() {
        
        Route::get('/chartemployeetaskData', [DashboardController::class, 'fetchEmployeeTaskData'])->name('chartemployeetaskData');
        Route::get('/employeeAttendancelist', [DashboardController::class, 'fetchEmployeeListData'])->name('employeeAttendancelist');
    });

    Route::resource('products', ProductController::class);

    Route::resource('task', TaskController::class);
    Route::get('tasks-data', [TaskController::class, 'getTasksData'])->name('tasks.data');
    Route::resource('subtask', SubTaskController::class);
    Route::get('getSubtaskFilter', [SubTaskController::class, 'getSubtaskFilter'])->name('getSubtaskFilter');
    Route::get('team_emp', [SubTaskController::class, 'team_emp'])->name('team_emp');

    Route::resource('projects', ProjectController::class);
    Route::get('/project_request', [ProjectController::class, 'projectRequest'])->name('project_request');
    Route::get('/project_request/subtask/{id}', [ProjectController::class, 'getprojectRequestData'])->name('projectRequest.data');
    Route::put('/project_request/subtask/{id}', [ProjectController::class, 'updateProjectRequest'])->name('project_request.update');
    Route::get('/project_status', [ProjectController::class, 'getProjectStatusData'])->name('project.status.data');

    Route::resource('employeeAttendance', EmployeeAttendanceController::class);
    Route::resource('rating', RatingController::class);

    Route::resource('idle_employees', IdleEmployeesController::class);
    Route::resource('Productivity', ProductivityController::class);
    Route::get('/productivity_IndividualStatus', [ ProductivityController::class, 'IndividualStatus'])->name('productivity.individualStatus');

    Route::get('/closed_tasks', [SubTaskUserTimelineController::class, 'getClosedTasks'])->name('tasks.closed');

    Route::get('/change_password', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change_password', [AuthController::class, 'changePassword'])->name('password.update');
});
