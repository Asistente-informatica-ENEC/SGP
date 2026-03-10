<?php

namespace App\Orchid\Layouts\Position;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class PositionEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Input::make('position.name')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Nombre del Cargo')
                ->placeholder('Ej. Analista de Sistemas, Contador, etc.'),
        ];
    }
}
