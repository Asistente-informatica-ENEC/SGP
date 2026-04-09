<?php

namespace App\Orchid\Layouts\ResponsabilityCard;

use App\Models\CivilServant;
use App\Models\Asset;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Layouts\Rows;

class ResponsabilityCardEditLayout extends Rows
{
    /**
     * Views.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Relation::make('responsabilityCard.civil_servant_id')
                ->fromModel(CivilServant::class, 'name')
                ->title('Funcionario')
                ->required()
                ->help('Seleccione el funcionario responsable.'),

            Input::make('responsabilityCard.assignment_code')
                ->title('Tarjeta')
                ->placeholder('Ej: No. 100')
                ->required(),

            DateTimer::make('responsabilityCard.assign_date')
                ->title('Fecha de Emisión')
                ->placeholder('Seleccionar fecha')
                ->format('Y-m-d')
                ->required(),
        ];
    }
}
