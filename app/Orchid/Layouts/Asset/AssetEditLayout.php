<?php

namespace App\Orchid\Layouts\Asset;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Layouts\Rows;

class AssetEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Input::make('asset.sicoin')
                ->type('text')
                ->required()
                ->title('Número SICOIN (Inventario)')
                ->placeholder('S-000-X'),

            TextArea::make('asset.description')
                ->rows(3)
                ->required()
                ->title('Descripción del Bien')
                ->placeholder('Detalles físicos, marca, modelo, etc.'),

            Input::make('asset.inventory_book')
                ->type('text')
                ->title('Libro de Inventarios')
                ->placeholder('Número de libro...'),

            Input::make('asset.folio_number')
                ->type('text')
                ->title('Número de Folio')
                ->placeholder('Número de folio...'),

            Input::make('asset.value')
                ->type('number')
                ->step(0.01)
                ->required()
                ->title('Valor Monetario (Q)')
                ->placeholder('0.00'),

            Select::make('asset.state')
                ->options([
                    'DISPONIBLE'    => 'DISPONIBLE',
                    'ASIGNADO'      => 'ASIGNADO',
                    'DE BAJA'       => 'DE BAJA',
                    'EN MAL ESTADO' => 'EN MAL ESTADO',
                    'SUSTRAÍDO'     => 'SUSTRAÍDO',
                ])
                ->required()
                ->title('Estado Actual'),

            Select::make('asset.category')
                ->options([
                    'DE PRODUCCIÓN' => 'DE PRODUCCIÓN',
                    'DE OFICINA Y MUEBLES' => 'DE OFICINA Y MUEBLES',
                    'MÉDICO SANITARIO Y DE LABORATORIO' => 'MÉDICO SANITARIO Y DE LABORATORIO',
                    'EDUCACIONAL, CULTURAL Y RECREATIVO' => 'EDUCACIONAL, CULTURAL Y RECREATIVO',
                    'DE TRANSPORTE, TRACCIÓN Y ELEVACIÓN' => 'DE TRANSPORTE, TRACCIÓN Y ELEVACIÓN',
                    'DE COMUNICACIONES' => 'DE COMUNICACIONES',
                    'EQUIPO DE COMPUTO' => 'EQUIPO DE COMPUTO',
                    'OTROS ACTIVOS' => 'OTROS ACTIVOS',
                ])
                ->required()
                ->title('Categoría'),

            DateTimer::make('asset.date')
                ->format('d-m-Y')
                ->serverFormat('Y-m-d')
                ->required()
                ->title('Fecha de Alta en Inventario')
                ->placeholder('Seleccione fecha...'),
        ];
    }
}
