<?php

namespace App\Http\Controllers\Crew;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CrewDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $crew = $user?->crew;

        $nextFlight = null;
        $certValid = 0;
        $certExpired = 0;
        $latestHealth = null;
        $schedules = collect();
        $calendarEvents = [];

        if ($crew) {
            $nextFlight = $crew->flightSchedules()
                ->where('departure_time', '>', now())
                ->orderBy('departure_time')
                ->first();

            $certValid = $crew->certifications()->where('status', 'valid')->count();
            $certExpired = $crew->certifications()->where('status', 'expired')->count();

            $latestHealth = $crew->healthRecords()->orderByDesc('checkup_date')->first();

            $schedules = $crew->flightSchedules()
                ->with(['crews.user'])
                ->orderByDesc('departure_time')
                ->get();

            $calendarEvents = $schedules->map(function ($flight) {
                return [
                    'id' => $flight->id,
                    'title' => $flight->flight_number . ' (' . $flight->origin . ' - ' . $flight->destination . ')',
                    'start' => optional($flight->departure_time)->toIso8601String(),
                    'end' => optional($flight->arrival_time)->toIso8601String(),
                    'className' => 'fc-event-primary',
                ];
            })->values()->all();
            // Build schedule details for modal consumption on the frontend
            $scheduleDetails = $schedules->mapWithKeys(function ($f) {
                return [
                    $f->id => [
                        'id' => $f->id,
                        'flight_number' => $f->flight_number,
                        'origin' => $f->origin,
                        'destination' => $f->destination,
                        'departure_time' => optional($f->departure_time)?->format('d M Y H:i'),
                        'arrival_time' => optional($f->arrival_time)?->format('d M Y H:i'),
                        'status' => $f->status,
                        'crews' => $f->crews->map(function ($c) {
                            return [
                                'name' => optional($c->user)->name ?? 'N/A',
                                'position' => $c->position,
                                'role_in_flight' => $c->pivot?->role_in_flight,
                            ];
                        })->values()->all(),
                    ],
                ];
            })->all();
        }

        return view('crew.dashboard', [
            'crew' => $crew,
            'nextFlight' => $nextFlight,
            'certValid' => $certValid,
            'certExpired' => $certExpired,
            'latestHealth' => $latestHealth,
            'schedules' => $schedules,
            'calendarEvents' => $calendarEvents,
            'scheduleDetails' => $scheduleDetails ?? [],
        ]);
    }
}
