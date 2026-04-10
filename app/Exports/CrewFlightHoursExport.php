<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CrewFlightHoursExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Employee ID',
            'Nama Crew',
            'Posisi',
            'Base',
            'Total Flight Hours',
            'Status',
            'Terakhir Diperbarui',
        ];
    }

    public function map($row): array
    {
        return [
            $row['employee_id'] ?? '-',
            $row['name'] ?? '-',
            $row['position'] ?? '-',
            $row['base_location'] ?? '-',
            $row['total_hours'] ?? 0,
            $row['status'] ?? '-',
            $row['updated_at'] ?? '-',
        ];
    }
}
