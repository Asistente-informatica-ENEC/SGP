<?php

namespace App\Orchid\Layouts\CivilServant;

use App\Models\CivilServant;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;

class CivilServantListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'civilServants';

    /**
     * Get the table cells to be rendered.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Nombre')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn (CivilServant $civilServant) => Link::make($civilServant->name)
                    ->route('platform.civil_servant.edit', ['civilServant' => $civilServant])),

            TD::make('sede', 'Sede')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('nit', 'NIT')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('position.name', 'Puesto')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn (CivilServant $cs) => $cs->position?->name),

            TD::make('unit', 'Unidad')
                ->sort()
                ->filter(TD::FILTER_TEXT),
                
            TD::make('created_at', 'Creado')
                ->sort()
                ->render(fn (CivilServant $civilServant) => $civilServant->created_at->toDateTimeString()),
        ];
    }
}
