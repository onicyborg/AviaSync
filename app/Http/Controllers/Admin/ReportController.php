<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ComplianceExport;
use App\Exports\CrewFlightHoursExport;
use App\Exports\FlightStatusExport;
use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\Crew;
use App\Models\FlightSchedule;
use App\Models\HealthRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected array $reportTitles = [
        'flight_hours' => 'Flight Hours Summary',
        'flight_status' => 'Flight Status Overview',
        'compliance' => 'Compliance Monitoring',
    ];

    public function index()
    {
        $reportTypes = $this->reportTitles;
        $statusOptions = FlightSchedule::STATUSES;

        return view('admin.reports.index', compact('reportTypes', 'statusOptions'));
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'report_type' => ['required', Rule::in(array_keys($this->reportTitles))],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'export_format' => ['required', Rule::in(['pdf', 'excel'])],
        ]);

        $startDate = !empty($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;

        [$data, $extraMeta] = match ($validated['report_type']) {
            'flight_status' => [$this->buildFlightStatusData($startDate, $endDate), ['statusOptions' => FlightSchedule::STATUSES]],
            'compliance' => [$this->buildComplianceData($startDate, $endDate), []],
            default => [$this->buildFlightHoursData($startDate, $endDate), []],
        };

        $meta = array_merge($extraMeta, [
            'title' => $this->reportTitles[$validated['report_type']],
            'period' => $this->formatPeriod($startDate, $endDate),
            'generated_at' => now(),
        ]);

        if ($validated['export_format'] === 'pdf') {
            $view = $this->pdfViewFor($validated['report_type']);
            $pdf = Pdf::loadView($view, [
                'data' => $data,
                'meta' => $meta,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($this->makeFilename($validated['report_type'], 'pdf'));
        }

        $export = $this->makeExcelExport($validated['report_type'], $data);

        return Excel::download($export, $this->makeFilename($validated['report_type'], 'xlsx'));
    }

    protected function buildFlightHoursData(?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return Crew::query()
            ->with('user')
            ->when($startDate, fn ($query) => $query->whereDate('updated_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('updated_at', '<=', $endDate))
            ->orderBy('employee_id')
            ->get()
            ->map(function (Crew $crew) {
                return [
                    'employee_id' => $crew->employee_id,
                    'name' => $crew->user->name ?? '-',
                    'position' => $crew->position ?? '-',
                    'base_location' => $crew->base_location ?? '-',
                    'total_hours' => (float) ($crew->total_flight_hours ?? 0),
                    'status' => ucfirst($crew->status ?? '-'),
                    'updated_at' => optional($crew->updated_at)->format('d M Y'),
                ];
            });
    }

    protected function buildFlightStatusData(?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return FlightSchedule::query()
            ->withCount('crews')
            ->when($startDate, fn ($query) => $query->where('departure_time', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('departure_time', '<=', $endDate))
            ->orderBy('departure_time')
            ->get()
            ->map(function (FlightSchedule $flight) {
                return [
                    'flight_number' => $flight->flight_number,
                    'origin' => $flight->origin,
                    'destination' => $flight->destination,
                    'route' => $flight->origin . ' → ' . $flight->destination,
                    'departure_time' => optional($flight->departure_time)->format('d M Y H:i'),
                    'arrival_time' => optional($flight->arrival_time)->format('d M Y H:i'),
                    'status' => ucfirst($flight->status ?? '-'),
                    'crew_count' => $flight->crews_count,
                ];
            });
    }

    protected function buildComplianceData(?Carbon $startDate, ?Carbon $endDate): Collection
    {
        $now = now();
        $threshold = now()->addDays(60);

        $certifications = Certification::query()
            ->with(['crew.user'])
            ->where(function ($query) use ($now, $threshold) {
                $query->whereBetween('expiry_date', [$now, $threshold])
                    ->orWhere('expiry_date', '<', $now)
                    ->orWhere('status', 'expired');
            })
            ->when($startDate, fn ($query) => $query->whereDate('expiry_date', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('expiry_date', '<=', $endDate))
            ->orderBy('expiry_date')
            ->get()
            ->map(function (Certification $cert) use ($now) {
                $expiry = $cert->expiry_date ? Carbon::parse($cert->expiry_date) : null;
                $daysRemaining = $expiry ? $now->diffInDays($expiry, false) : null;

                return [
                    'crew_name' => $cert->crew?->user?->name ?? '-',
                    'employee_id' => $cert->crew?->employee_id ?? '-',
                    'type' => 'Certification',
                    'item_name' => $cert->certificate_name,
                    'status' => ucfirst($cert->status ?? '-'),
                    'due_date' => optional($cert->expiry_date)->format('d M Y'),
                    'days_remaining' => $daysRemaining,
                    'notes' => $cert->certificate_number,
                ];
            });

        $healthRecords = HealthRecord::query()
            ->with(['crew.user'])
            ->where(function ($query) use ($now, $threshold) {
                $query->whereBetween('next_checkup_date', [$now, $threshold])
                    ->orWhere('next_checkup_date', '<', $now)
                    ->orWhere('status', '!=', 'fit');
            })
            ->when($startDate, fn ($query) => $query->whereDate('next_checkup_date', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('next_checkup_date', '<=', $endDate))
            ->orderBy('next_checkup_date')
            ->get()
            ->map(function (HealthRecord $record) use ($now) {
                $due = $record->next_checkup_date ? Carbon::parse($record->next_checkup_date) : null;
                $daysRemaining = $due ? $now->diffInDays($due, false) : null;

                return [
                    'crew_name' => $record->crew?->user?->name ?? '-',
                    'employee_id' => $record->crew?->employee_id ?? '-',
                    'type' => 'Health Record',
                    'item_name' => 'Medical Checkup',
                    'status' => ucfirst($record->status ?? '-'),
                    'due_date' => optional($record->next_checkup_date)->format('d M Y'),
                    'days_remaining' => $daysRemaining,
                    'notes' => $record->notes,
                ];
            });

        return $certifications->merge($healthRecords)->sortBy('due_date')->values();
    }

    protected function formatPeriod(?Carbon $startDate, ?Carbon $endDate): string
    {
        if (!$startDate && !$endDate) {
            return 'All Time';
        }

        $start = $startDate ? $startDate->format('d M Y') : ' - ';
        $end = $endDate ? $endDate->format('d M Y') : ' - ';

        return trim($start . ' s/d ' . $end);
    }

    protected function pdfViewFor(string $reportType): string
    {
        return match ($reportType) {
            'flight_status' => 'admin.reports.pdf.flight_status',
            'compliance' => 'admin.reports.pdf.compliance',
            default => 'admin.reports.pdf.flight_hours',
        };
    }

    protected function makeFilename(string $reportType, string $extension): string
    {
        return sprintf('%s-%s.%s', $reportType, now()->format('Ymd_His'), $extension);
    }

    protected function makeExcelExport(string $reportType, Collection $data)
    {
        return match ($reportType) {
            'flight_status' => new FlightStatusExport($data),
            'compliance' => new ComplianceExport($data),
            default => new CrewFlightHoursExport($data),
        };
    }
}
