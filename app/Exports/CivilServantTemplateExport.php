<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class CivilServantTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'Juan Perez',
                'Sede Central',
                '1234567-8',
                'SECRETARIO',
                'UNIDAD DE INFORMÁTICA'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'nombre',
            'sede',
            'nit',
            'cargo',
            'unidad'
        ];
    }
}
