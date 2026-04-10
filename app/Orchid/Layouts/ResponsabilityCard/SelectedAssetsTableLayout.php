<?php

namespace App\Orchid\Layouts\ResponsabilityCard;

use App\Models\Asset;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SelectedAssetsTableLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'selectedAssets';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('sicoin', 'SICOIN')
                ->width('150px'),

            TD::make('description', 'Descripción'),

            TD::make('observation', 'Observación')
                ->render(function (Asset $asset) {
                    return \Orchid\Screen\Fields\Input::make("observations[{$asset->id}]")
                        ->type('text')
                        ->maxlength(100)
                        ->placeholder('Ej. Buen estado...');
                }),
        ];
    }

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('Aún no hay bienes seleccionados');
    }

    /**
     * @return string
     */
    protected function subNotFound(): string
    {
        return __('Utiliza el buscador superior para agregar bienes a esta tarjeta mediante su código SICOIN.');
    }
}
