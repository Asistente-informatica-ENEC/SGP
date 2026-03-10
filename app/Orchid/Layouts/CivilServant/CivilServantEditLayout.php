<?php

namespace App\Orchid\Layouts\CivilServant;

use App\Models\Position;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class CivilServantEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Input::make('civilServant.name')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Nombre Completo')
                ->placeholder('Nombre Apellido'),

            Input::make('civilServant.sede')
                ->type('text')
                ->max(150)
                ->required()
                ->title('Sede')
                ->placeholder('Ej. Sede Central, Edificio X'),

            Input::make('civilServant.nit')
                ->type('text')
                ->max(15)
                ->title('NIT')
                ->placeholder('1234567-8'),

            Relation::make('civilServant.position_id')
                ->fromModel(Position::class, 'name')
                ->required()
                ->title('Puesto o Cargo')
                ->placeholder('Seleccione un cargo'),

            Input::make('civilServant.unit')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Unidad Administrativa')
                ->placeholder('Ej. Dirección Técnica'),
        ];
    }
}
