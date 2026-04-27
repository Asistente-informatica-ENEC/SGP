<?php

namespace App\Orchid\Layouts\BadConditionCard;

use App\Models\Asset;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\ModalToggle;

class BadConditionAssetListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'badConditionAssets';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('sicoin', 'SICOIN')
                ->sort()
                ->render(fn (Asset $asset) => $asset->sicoin),

            TD::make('description', 'Descripción')
                ->sort()
                ->style('text-align: justify; max-width: 400px; white-space: normal;')
                ->render(fn (Asset $asset) => '<span title="' . e($asset->description) . '">' . \Illuminate\Support\Str::limit($asset->description, 50) . '</span>'),

            TD::make('inventory_book', 'Libro')
                ->sort(),

            TD::make('folio_number', 'Folio')
                ->sort(),

            TD::make('value', 'Valor')
                ->sort()
                ->render(fn (Asset $asset) => 'Q ' . number_format($asset->value, 2)),

            TD::make('category', 'Categoría')
                ->sort(),

            TD::make('Acciones')
                ->align(TD::ALIGN_CENTER)
                ->render(fn (Asset $asset) => ModalToggle::make('Ver Historial')
                    ->icon('bs.clock-history')
                    ->modal('assetHistoryModal')
                    ->method('asyncGetAssetHistory')
                    ->modalTitle('Historial del Bien - ' . $asset->sicoin)
                    ->asyncParameters([
                        'asset' => $asset->id,
                    ])
                ),
        ];
    }

    protected function textNotFound(): string
    {
        return 'No hay bienes individuales marcados en mal estado actualmente';
    }
}
