<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class AssetTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                '68234',
                'COMPUTADORA DE ESCRITORIO CON MONITOR Y TECLADO',
                '5500',
                'DISPONIBLE',
                'EQUIPO DE COMPUTO',
                date('d-m-Y')
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'sicoin',
            'descripcion',
            'valor',
            'estado',
            'categoria',
            'fecha'
        ];
    }
}
