<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManageCrewController;
use App\Http\Controllers\Admin\CrewCertificationController;
use App\Http\Controllers\Admin\CrewHealthRecordController;
use App\Http\Controllers\Admin\FlightScheduleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SystemLogController;

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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('crew', ManageCrewController::class, ['as' => 'admin'])->except(['create', 'edit']);

    Route::resource('crew.certifications', CrewCertificationController::class, ['as' => 'admin'])
        ->except(['index', 'show']);
    Route::resource('crew.health-records', CrewHealthRecordController::class, ['as' => 'admin'])
        ->except(['index', 'show']);

    Route::resource('flight-schedules', FlightScheduleController::class, ['as' => 'admin']);
    Route::post('flight-schedules/{flight_schedule}/auto-assign', [FlightScheduleController::class, 'autoAssign'])
        ->name('admin.flight-schedules.auto-assign');
    Route::post('flight-schedules/{flight_schedule}/assign-crew', [FlightScheduleController::class, 'assignCrew'])
        ->name('admin.flight-schedules.assign-crew');
    Route::delete('flight-schedules/{flight_schedule}/crew/{assignment}', [FlightScheduleController::class, 'removeCrew'])
        ->name('admin.flight-schedules.remove-crew');

    Route::get('reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::post('reports/export', [ReportController::class, 'export'])->name('admin.reports.export');

    Route::get('system-logs', [SystemLogController::class, 'index'])->name('admin.system-logs.index');
    // Rute CRUD Crew, dll
});

Route::prefix('crew')->middleware(['auth', 'role:crew'])->group(function () {
    Route::get('/dashboard', function () {
        return view('crew.dashboard');
    })->name('crew.dashboard');
    // Route::get('/my-schedule', [CrewController::class, 'mySchedule'])->name('crew.schedule');
    // Rute view certification, dll
});