<?php

namespace App\Orchid\Layouts\Asset;

use App\Models\Asset;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;

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
                ->render(fn (Asset $asset) => ModalToggle::make($asset->sicoin)
                    ->modal('viewAssetModal')
                    ->modalTitle('Detalles del Bien - ' . $asset->sicoin)
                    ->method('redirectToEdit')
                    ->asyncParameters([
                        'asset' => $asset->id,
                    ])),

            TD::make('description', 'Descripción')
                ->sort()
                ->style('text-align: justify; max-width: 400px; white-space: normal;')
                ->render(fn (Asset $asset) => '<span title="' . e($asset->description) . '">' . \Illuminate\Support\Str::limit($asset->description, 50) . '</span>')
                ->filter(TD::FILTER_TEXT),

            TD::make('inventory_book', 'Libro')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('folio_number', 'Folio')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('value', 'Valor')
                ->sort()
                ->width('150px')
                ->render(fn (Asset $asset) => 'Q ' . number_format($asset->value, 2)),

            TD::make('state', 'Estado')
                ->sort()
                ->width('150px')
                ->filter(TD::FILTER_TEXT),

            TD::make('category', 'Categoría')
                ->sort()
                ->filter(TD::FILTER_TEXT),
                
            TD::make('date', 'Fecha de Alta')
                ->sort()
                ->width('150px')
                ->render(fn (Asset $asset) => $asset->date ? \Carbon\Carbon::parse($asset->date)->format('d-m-Y') : ''),

            TD::make('Acciones')
                ->align(TD::ALIGN_CENTER)
                ->render(fn (Asset $asset) => \Orchid\Screen\Actions\DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Ver Historial')
                            ->icon('bs.clock-history')
                            ->modal('assetHistoryModal')
                            ->method('asyncGetAssetHistory')
                            ->modalTitle('Historial del Bien - ' . $asset->sicoin)
                            ->asyncParameters([
                                'asset' => $asset->id,
                            ]),
                    ])
                ),
        ];
    }

    protected function textNotFound(): string
    {
        return 'No hay bienes registrados actualmente';
    }

    protected function subNotFound(): string
    {
        return 'Importa o crea nuevos bienes para visualizarlos en este listado';
    }
}
