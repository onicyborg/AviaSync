<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rute yang harus login dulu
Route::middleware(['auth'])->group(function () {
    
    // Rute Khusus Admin
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        // Rute CRUD Crew, dll
    });

    // Rute Khusus Crew
    Route::middleware(['role:crew'])->prefix('crew')->group(function () {
        // Route::get('/my-schedule', [CrewController::class, 'mySchedule'])->name('crew.schedule');
        // Rute view certification, dll
    });

});