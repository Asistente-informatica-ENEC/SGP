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
        \App\Models\CivilServant::create([
            'name' => 'Juan Pérez',
            'dpi' => '1234567890101',
            'nit' => '1234567-8',
            'role' => 'Secretario Administrativo',
            'unit' => 'Dirección General'
        ]);

        \App\Models\CivilServant::create([
            'name' => 'Maria López',
            'dpi' => '9876543210101',
            'nit' => '8765432-1',
            'role' => 'Técnico de Bodega',
            'unit' => 'Departamento de Compras'
        ]);

        \App\Models\Asset::create([
            'sicoin' => 'S-001-A',
            'description' => 'Computadora Dell Latitude 5420, i7 16GB RAM',
            'value' => 8500.50,
            'state' => 'disponible',
            'category' => 'Equipo de Cómputo',
            'date' => now()
        ]);

        \App\Models\Asset::create([
            'sicoin' => 'S-001-B',
            'description' => 'Escritorio de Madera de Cedro, 3 gavetas',
            'value' => 2500.00,
            'state' => 'disponible',
            'category' => 'Mobiliario',
            'date' => now()
        ]);
    }
}
