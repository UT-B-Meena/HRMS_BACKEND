<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::get('/dashboard', function () {
    return view('dashboard.index'); 
})->middleware('auth:sanctum')->name('dashboard');