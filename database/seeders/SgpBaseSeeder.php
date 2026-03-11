<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SgpBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunos cargos primero
        $puesto1 = \App\Models\Position::firstOrCreate(['name' => 'Secretario Administrativo']);
        $puesto2 = \App\Models\Position::firstOrCreate(['name' => 'Técnico de Bodega']);

        \App\Models\CivilServant::create([
            'name' => 'Juan Pérez',
            'sede' => 'Sede Central',
            'nit' => '1234567-8',
            'position_id' => $puesto1->id,
            'unit' => 'Dirección General'
        ]);

        \App\Models\CivilServant::create([
            'name' => 'Maria López',
            'sede' => 'Sede Regional Sur',
            'nit' => '8765432-1',
            'position_id' => $puesto2->id,
            'unit' => 'Departamento de Compras'
        ]);

        \App\Models\Asset::create([
            'sicoin' => 'S-001-A',
            'description' => 'Computadora Dell Latitude 5420, i7 16GB RAM',
            'value' => 8500.50,
            'state' => 'DISPONIBLE',
            'category' => 'EQUIPO DE COMPUTO',
            'date' => now()
        ]);

        \App\Models\Asset::create([
            'sicoin' => 'S-001-B',
            'description' => 'Escritorio de Madera de Cedro, 3 gavetas',
            'value' => 2500.00,
            'state' => 'EN MAL ESTADO',
            'category' => 'DE OFICINA Y MUEBLES',
            'date' => now()
        ]);
    }
}
