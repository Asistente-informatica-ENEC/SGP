<?php

namespace App\Orchid\Layouts\User;

use App\Models\Asset;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ProfileBadConditionKardexLayout extends Table
{
    /**
     * Data source: bienes en mal estado asignados al encargado.
     *
     * @var string
     */
    protected $target = 'badConditionKardex';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('sicoin', 'SICOIN')
                ->sort(),

            TD::make('description', 'Descripción')
                ->style('max-width: 350px; white-space: normal;')
                ->render(fn (Asset $asset) =>
                    '<span title="' . e($asset->description) . '">'
                    . \Illuminate\Support\Str::limit($asset->description, 55)
                    . '</span>'
                ),

            TD::make('inventory_book', 'Libro')
                ->sort(),

            TD::make('folio_number', 'Folio')
                ->sort(),

            TD::make('value', 'Valor')
                ->sort()
                ->align(TD::ALIGN_RIGHT)
                ->render(fn (Asset $asset) => 'Q ' . number_format($asset->value, 2)),

            TD::make('category', 'Categoría')
                ->sort(),

            TD::make('date', 'Fecha de Alta')
                ->sort()
                ->render(fn (Asset $asset) =>
                    $asset->date ? \Carbon\Carbon::parse($asset->date)->format('d/m/Y') : '—'
                ),
        ];
    }

    protected function textNotFound(): string
    {
        return 'No hay bienes en mal estado asignados a su cargo actualmente.';
    }

    protected function subNotFound(): string
    {
        return 'Los bienes registrados como mal estado vinculados a usted aparecerán aquí.';
    }
}
