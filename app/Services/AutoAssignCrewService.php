<?php

namespace App\Services;

use App\Models\Crew;
use App\Models\FlightSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AutoAssignCrewService
{
    public const REQUIRED_ROLES = [
        'Captain' => 1,
        'First Officer' => 1,
        'Purser' => 1,
        'Flight Attendant' => 2,
    ];

    public function assign(FlightSchedule $flight): array
    {
        $flight->loadMissing('crews');

        $neededRoles = $this->determineNeededRoles($flight);
        if (empty($neededRoles)) {
            return [
                'status' => 'success',
                'unfilled_roles' => [],
            ];
        }

        $assignedCount = 0;
        $unfilled = [];
        $blockedCrewIds = $flight->crews->pluck('id')->all();

        foreach ($neededRoles as $role => $slots) {
            for ($i = 0; $i < $slots; $i++) {
                $crew = $this->findEligibleCrew($role, $flight, $blockedCrewIds);
                if (!$crew) {
                    $unfilled[] = $role;
                    continue;
                }

                $flight->crews()->attach($crew->id, [
                    'id' => (string) Str::uuid(),
                    'role_in_flight' => $role,
                    'assigned_at' => now(),
                    'created_by' => Auth::id(),
                ]);

                $blockedCrewIds[] = $crew->id;
                $assignedCount++;
            }
        }

        $status = 'success';
        if (!empty($unfilled)) {
            $status = $assignedCount > 0 ? 'partial' : 'failed';
        }

        $flight->load('crews');

        return [
            'status' => $status,
            'unfilled_roles' => $unfilled,
        ];
    }

    protected function determineNeededRoles(FlightSchedule $flight): array
    {
        $currentCounts = $flight->crews
            ->groupBy(fn ($crew) => $crew->pivot->role_in_flight)
            ->map->count();

        $needs = [];
        foreach (self::REQUIRED_ROLES as $role => $requiredCount) {
            $existing = $currentCounts[$role] ?? 0;
            $missing = max($requiredCount - $existing, 0);
            if ($missing > 0) {
                $needs[$role] = $missing;
            }
        }

        return $needs;
    }

    protected function findEligibleCrew(string $role, FlightSchedule $flight, array $excludeIds = []): ?Crew
    {
        $position = $role === 'Flight Attendant' ? 'Flight Attendant' : $role;

        $query = Crew::query()
            ->where('status', 'active')
            ->where('position', $position)
            ->when(!empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->whereDoesntHave('flightSchedules', function ($q) use ($flight) {
                $q->where('flight_schedules.id', '!=', $flight->id)
                  ->where(function ($overlap) use ($flight) {
                      $overlap->whereBetween('departure_time', [$flight->departure_time, $flight->arrival_time])
                          ->orWhereBetween('arrival_time', [$flight->departure_time, $flight->arrival_time])
                          ->orWhere(function ($cover) use ($flight) {
                              $cover->where('departure_time', '<=', $flight->departure_time)
                                  ->where('arrival_time', '>=', $flight->arrival_time);
                          });
                  });
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('health_records as hr')
                    ->whereColumn('hr.crew_id', 'crews.id')
                    ->whereNull('hr.deleted_at')
                    ->orderByDesc('hr.checkup_date')
                    ->limit(1)
                    ->where('hr.status', 'fit');
            })
            ->whereHas('certifications', function ($cert) {
                $cert->where('status', 'valid')
                    ->where(function ($expiry) {
                        $expiry->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>', now());
                    });
            })
            ->orderByDesc('total_flight_hours')
            ->orderBy('created_at');

        return $query->first();
    }
}
