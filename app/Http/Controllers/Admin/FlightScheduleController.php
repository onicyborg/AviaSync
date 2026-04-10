<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Models\CrewFlightSchedule;
use App\Models\FlightSchedule;
use App\Services\AutoAssignCrewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FlightScheduleController extends Controller
{
    public function __construct(
        protected AutoAssignCrewService $autoAssignCrewService
    ) {
    }

    public function index()
    {
        $flightSchedules = FlightSchedule::query()
            ->with('crews')
            ->latest('departure_time')
            ->get();

        $statusOptions = FlightSchedule::STATUSES;
        $requiredRoles = AutoAssignCrewService::REQUIRED_ROLES;
        $origins = $flightSchedules->pluck('origin')->filter()->unique()->sort()->values();
        $destinations = $flightSchedules->pluck('destination')->filter()->unique()->sort()->values();

        return view('admin.flight_schedules.index', compact(
            'flightSchedules',
            'statusOptions',
            'requiredRoles',
            'origins',
            'destinations'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $payload = $validated + [
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ];

        FlightSchedule::create($payload);

        return redirect()->route('admin.flight-schedules.index')->with('success', 'Jadwal penerbangan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $flight = FlightSchedule::query()
            ->with(['crews.user'])
            ->findOrFail($id);

        $requiredRoles = AutoAssignCrewService::REQUIRED_ROLES;
        $roleCounts = $flight->crews
            ->groupBy(fn ($crew) => $crew->pivot->role_in_flight)
            ->map->count();

        $missingRoles = [];
        foreach ($requiredRoles as $role => $requiredCount) {
            $current = $roleCounts[$role] ?? 0;
            if ($current < $requiredCount) {
                $missingRoles[] = $role;
            }
        }

        $isCrewComplete = empty($missingRoles);

        $availableCrews = Crew::query()
            ->with('user')
            ->where('status', 'active')
            ->whereIn('position', array_keys($requiredRoles))
            ->whereDoesntHave('flightSchedules', fn ($query) => $query->where('flight_schedules.id', $flight->id))
            ->orderBy('position')
            ->orderBy('employee_id')
            ->get();

        return view('admin.flight_schedules.show', compact(
            'flight',
            'requiredRoles',
            'missingRoles',
            'isCrewComplete',
            'availableCrews'
        ));
    }

    public function update(Request $request, string $id)
    {
        $flight = FlightSchedule::query()->findOrFail($id);

        $validated = $this->validatePayload($request, $flight->id);

        $flight->update($validated + ['updated_by' => Auth::id()]);

        return redirect()->route('admin.flight-schedules.index')->with('success', 'Jadwal penerbangan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $flight = FlightSchedule::query()->findOrFail($id);
        $flight->delete();

        return redirect()->route('admin.flight-schedules.index')->with('success', 'Jadwal penerbangan berhasil dihapus.');
    }

    public function autoAssign(Request $request, string $id)
    {
        $flight = FlightSchedule::query()->with('crews')->findOrFail($id);

        $result = $this->autoAssignCrewService->assign($flight);
        $unfilled = collect($result['unfilled_roles'] ?? [])->unique()->values();

        return match ($result['status']) {
            'success' => redirect()
                ->route('admin.flight-schedules.show', $flight->id)
                ->with('success', 'Penugasan otomatis berhasil. Seluruh posisi telah terisi sesuai regulasi.'),
            'partial' => redirect()
                ->route('admin.flight-schedules.show', $flight->id)
                ->with('warning', 'Penugasan otomatis berhasil sebagian. Posisi berikut masih kosong: ' . $unfilled->implode(', ') . '. Tidak ada kru yang memenuhi syarat ketersediaan/kesehatan.'),
            default => redirect()
                ->route('admin.flight-schedules.show', $flight->id)
                ->with('error', 'Penugasan otomatis gagal. Tidak ada kru yang tersedia atau memenuhi syarat regulasi.'),
        };
    }

    public function assignCrew(Request $request, string $id)
    {
        $flight = FlightSchedule::query()->with('crews')->findOrFail($id);

        $validated = $request->validate([
            'crew_id' => ['required', 'exists:crews,id'],
        ]);

        if ($flight->crews->contains('id', $validated['crew_id'])) {
            return redirect()
                ->back()
                ->with('error', 'Crew sudah ditugaskan pada penerbangan ini.');
        }

        $crew = Crew::query()->with(['healthRecords' => fn ($q) => $q->latest('checkup_date'), 'certifications'])->findOrFail($validated['crew_id']);

        $role = $this->determineCrewRole($crew);

        if (!$role) {
            return redirect()
                ->back()
                ->with('error', 'Crew tidak memiliki role yang valid untuk penerbangan ini.');
        }

        if (!$this->crewMeetsRegulations($crew, $flight)) {
            return redirect()
                ->back()
                ->with('error', 'Crew tidak memenuhi regulasi (status aktif, kesehatan fit, sertifikasi valid, atau jadwal bentrok).');
        }

        $flight->crews()->attach($crew->id, [
            'id' => (string) Str::uuid(),
            'role_in_flight' => $role,
            'assigned_at' => now(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.flight-schedules.show', $flight->id)->with('success', 'Crew berhasil ditugaskan.');
    }

    public function removeCrew(string $flightScheduleId, string $assignmentId)
    {
        $flight = FlightSchedule::query()->findOrFail($flightScheduleId);
        $assignment = CrewFlightSchedule::query()
            ->where('flight_schedule_id', $flight->id)
            ->findOrFail($assignmentId);

        $assignment->update(['updated_by' => Auth::id()]);
        $assignment->delete();

        return redirect()->route('admin.flight-schedules.show', $flight->id)->with('success', 'Crew berhasil dihapus dari penerbangan.');
    }

    protected function validatePayload(Request $request, ?string $id = null): array
    {
        return $request->validate([
            'flight_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('flight_schedules', 'flight_number')->ignore($id)->whereNull('deleted_at'),
            ],
            'origin' => ['required', 'string', 'max:10'],
            'destination' => ['required', 'string', 'max:10'],
            'departure_time' => ['required', 'date'],
            'arrival_time' => ['required', 'date', 'after:departure_time'],
            'status' => ['required', Rule::in(FlightSchedule::STATUSES)],
        ]);
    }

    protected function crewMeetsRegulations(Crew $crew, FlightSchedule $flight): bool
    {
        if (($crew->status ?? 'inactive') !== 'active') {
            return false;
        }

        $hasConflict = $crew->flightSchedules()
            ->where('flight_schedules.id', '!=', $flight->id)
            ->where(function ($query) use ($flight) {
                $query->whereBetween('departure_time', [$flight->departure_time, $flight->arrival_time])
                    ->orWhereBetween('arrival_time', [$flight->departure_time, $flight->arrival_time])
                    ->orWhere(function ($cover) use ($flight) {
                        $cover->where('departure_time', '<=', $flight->departure_time)
                            ->where('arrival_time', '>=', $flight->arrival_time);
                    });
            })
            ->exists();

        if ($hasConflict) {
            return false;
        }

        $latestHealth = $crew->healthRecords()->latest('checkup_date')->first();
        if (!$latestHealth || $latestHealth->status !== 'fit') {
            return false;
        }

        $hasValidCert = $crew->certifications()
            ->where('status', 'valid')
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            })
            ->exists();

        return $hasValidCert;
    }

    protected function determineCrewRole(Crew $crew): ?string
    {
        $position = strtolower($crew->position ?? '');

        return match (true) {
            str_contains($position, 'captain') => 'Captain',
            str_contains($position, 'first officer') || str_contains($position, 'fo') => 'First Officer',
            str_contains($position, 'purser') => 'Purser',
            str_contains($position, 'attendant') || str_contains($position, 'fa') => 'Flight Attendant',
            default => null,
        };
    }
}
