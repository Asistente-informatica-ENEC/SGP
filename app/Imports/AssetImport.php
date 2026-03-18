<?php

namespace App\Imports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class AssetImport implements ToModel, WithHeadingRow
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
        // Mapear y limpiar datos
        $state = mb_strtoupper($row['estado'] ?? 'DISPONIBLE');
        $category = mb_strtoupper($row['categoria'] ?? $row['categoría'] ?? 'OTROS ACTIVOS');
        
        // Manejo de fecha
        $date = null;
        if (isset($row['fecha'])) {
            try {
                // Si viene como número de Excel, lo convertimos. Si no, intentamos parsear.
                $date = is_numeric($row['fecha']) 
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha'])
                    : Carbon::parse($row['fecha']);
            } catch (\Exception $e) {
                $date = now();
            }
        }

        $asset = Asset::updateOrCreate(
            ['sicoin'      => $row['sicoin']], // Identificador único
            [
                'description'    => $row['descripcion'] ?? $row['descripción'],
                'inventory_book' => $row['libro'] ?? null,
                'folio_number'   => $row['folio'] ?? null,
                'value'          => $row['valor'] ?? $row['precio'] ?? 0,
                'state'          => $state,
                'category'       => $category,
                'date'           => $date ?? now(),
            ]
        );

        if ($asset->wasRecentlyCreated) {
            $this->imported++;
        } else {
            $this->updated++;
        }

        return $asset;
    }
}
