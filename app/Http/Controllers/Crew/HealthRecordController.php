<?php

namespace App\Http\Controllers\Crew;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HealthRecordController extends Controller
{
    public function index(): View
    {
        $crew = Auth::user()?->crew;

        $healthRecords = collect();
        if ($crew) {
            $healthRecords = $crew->healthRecords()
                ->orderBy('checkup_date', 'desc')
                ->get();
        }

        return view('crew.health_records.index', [
            'healthRecords' => $healthRecords,
        ]);
    }
}
