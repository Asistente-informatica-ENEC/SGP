<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericReportExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $headings;

    public function __construct(array $headings, $data)
    {
        $this->headings = $headings;
        $this->data = collect($data);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function collection()
    {
        return $this->data;
    }
}
