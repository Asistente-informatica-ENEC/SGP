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
                ->placeholder('Ej: RC-2026-001')
                ->required(),

            DateTimer::make('responsabilityCard.assign_date')
                ->title('Fecha de Emisión')
                ->format('Y-m-d')
                ->required(),

            Relation::make('assets.')
                ->fromModel(Asset::class, 'description', 'id')
                ->applyScope('disponible')
                ->multiple()
                ->title('Bienes a Asignar')
                ->help('Seleccione los bienes que desea vincular a esta tarjeta. Solo se muestran bienes disponibles.'),
        ];
    }
}
