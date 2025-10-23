<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Route for the login page
Route::get('/login', function () {
    return view('login');})->name('login')->middleware('guest');
// Route for handling login form submission
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Route for the registration page
Route::get('/register', function () {
    return view('register');
})->name('register')->middleware('guest');

// Route for handling registration form submission
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Route for logging out
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route for the admin dashboard
Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin')->middleware('auth');

// Route for the vendor dashboard
Route::get('/dashboard/vendedor', [DashboardController::class, 'vendedor'])->name('dashboard.vendedor')->middleware('auth');