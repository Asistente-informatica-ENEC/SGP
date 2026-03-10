<?php

namespace App\Orchid\Layouts\Asset;

use App\Models\Asset;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;

class AssetListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'assets';

    /**
     * Get the table cells to be rendered.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('sicoin', 'SICOIN')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn (Asset $asset) => Link::make($asset->sicoin)
                    ->route('platform.asset.edit', ['asset' => $asset])),

            TD::make('description', 'Descripción')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('value', 'Valor')
                ->sort()
                ->render(fn (Asset $asset) => 'Q ' . number_format($asset->value, 2)),

            TD::make('state', 'Estado')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('category', 'Categoría')
                ->sort()
                ->filter(TD::FILTER_TEXT),
                
            TD::make('date', 'Fecha de Alta')
                ->sort()
                ->render(fn (Asset $asset) => $asset->date ? \Carbon\Carbon::parse($asset->date)->toDateString() : ''),
        ];
    }
}
