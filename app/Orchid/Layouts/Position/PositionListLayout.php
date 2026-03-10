<?php

namespace App\Orchid\Layouts\Position;

use App\Models\Position;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;

class PositionListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'positions';

    /**
     * Get the table cells to be rendered.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Nombre del Cargo')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn (Position $position) => Link::make($position->name)
                    ->route('platform.position.edit', ['position' => $position])),

            TD::make('created_at', 'Creado')
                ->sort()
                ->render(fn (Position $position) => $position->created_at->toDateTimeString()),
        ];
    }
}
