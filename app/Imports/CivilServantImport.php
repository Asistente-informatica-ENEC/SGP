<?php

namespace App\Imports;

use App\Models\CivilServant;
use App\Models\Position;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CivilServantImport implements ToModel, WithHeadingRow
{
    public int $imported = 0;
    public int $updated = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Buscar o crear el cargo por nombre
        $positionName = $row['cargo'] ?? $row['puesto'] ?? 'Sin Cargo';
        $position = Position::firstOrCreate(['name' => mb_strtoupper($positionName)]);

        $civilServant = CivilServant::updateOrCreate(
            ['nit' => $row['nit']], // Identificador único
            [
                'name'        => $row['nombre'],
                'sede'        => $row['sede'] ?? 'Sede Central',
                'position_id' => $position->id,
                'unit'        => $row['unidad'] ?? 'Sin Unidad',
            ]
        );

        if ($civilServant->wasRecentlyCreated) {
            $this->imported++;
        } else {
            $this->updated++;
        }

        return $civilServant;
    }
}
