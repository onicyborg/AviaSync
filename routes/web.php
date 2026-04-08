<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManageCrewController;
use App\Http\Controllers\Admin\CrewCertificationController;
use App\Http\Controllers\Admin\CrewHealthRecordController;

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

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.dashboard');

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('crew', ManageCrewController::class, ['as' => 'admin'])->except(['create', 'edit']);

    Route::resource('crew.certifications', CrewCertificationController::class, ['as' => 'admin'])
        ->except(['index', 'show']);
    Route::resource('crew.health-records', CrewHealthRecordController::class, ['as' => 'admin'])
        ->except(['index', 'show']);
    // Rute CRUD Crew, dll
});

Route::prefix('crew')->middleware(['auth', 'role:crew'])->group(function () {
    Route::get('/dashboard', function () {
        return view('crew.dashboard');
    })->name('crew.dashboard');
    // Route::get('/my-schedule', [CrewController::class, 'mySchedule'])->name('crew.schedule');
    // Rute view certification, dll
});