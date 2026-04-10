<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Models\FlightSchedule;
use App\Models\SystemLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalCrew = Crew::count();
        $activeFlights = FlightSchedule::where('status', 'active')->count();
        $readyCrew = Crew::query()
            ->where('status', 'active')
            ->whereHas('healthRecords', fn ($query) => $query->where('status', 'fit'))
            ->count();
        $recentLogs = SystemLog::with('user')->latest()->limit(10)->get();

        return view('admin.dashboard', compact('totalCrew', 'activeFlights', 'readyCrew', 'recentLogs'));
    }
}
