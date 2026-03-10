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

            Input::make('asset.value')
                ->type('number')
                ->step(0.01)
                ->required()
                ->title('Valor Monetario (Q)')
                ->placeholder('0.00'),

            Select::make('asset.state')
                ->options([
                    'disponible' => 'Disponible',
                    'asignado'   => 'Asignado',
                    'de baja'    => 'De Baja',
                    'en reparación' => 'En Reparación',
                ])
                ->required()
                ->title('Estado Actual'),

            Input::make('asset.category')
                ->type('text')
                ->required()
                ->title('Categoría')
                ->placeholder('Ej. Equipo de Cómputo, Mobiliario'),

            DateTimer::make('asset.date')
                ->format('Y-m-d')
                ->required()
                ->title('Fecha de Alta en Inventario'),
        ];
    }
}
