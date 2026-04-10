<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComplianceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Crew Name',
            'Employee ID',
            'Type',
            'Item',
            'Status',
            'Due Date',
            'Days Remaining',
            'Notes',
        ];
    }

    public function map($row): array
    {
        return [
            $row['crew_name'] ?? '-',
            $row['employee_id'] ?? '-',
            $row['type'] ?? '-',
            $row['item_name'] ?? '-',
            $row['status'] ?? '-',
            $row['due_date'] ?? '-',
            $row['days_remaining'] ?? '-',
            $row['notes'] ?? '-',
        ];
    }
}
