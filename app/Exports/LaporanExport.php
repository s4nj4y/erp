<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private string $title,
        private array $headings,
        private array $rows,
        private array $summary = [],
    ) {}

    public function array(): array
    {
        $rows = $this->rows;

        if ($this->summary) {
            $rows[] = array_fill(0, max(1, count($this->headings)), '');
            foreach ($this->summary as $label => $value) {
                $rows[] = [$label, $value];
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return substr($this->title, 0, 31);
    }
}
