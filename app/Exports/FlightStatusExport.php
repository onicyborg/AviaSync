<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FlightStatusExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected Collection $data)
    {
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Flight Number',
            'Route',
            'Departure Time',
            'Arrival Time',
            'Status',
            'Crew Assigned',
        ];
    }

    public function map($row): array
    {
        return [
            $row['flight_number'] ?? '-',
            $row['route'] ?? '-',
            $row['departure_time'] ?? '-',
            $row['arrival_time'] ?? '-',
            $row['status'] ?? '-',
            $row['crew_count'] ?? 0,
        ];
    }
}
