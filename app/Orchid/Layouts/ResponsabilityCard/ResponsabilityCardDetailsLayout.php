<?php

namespace App\Orchid\Layouts\ResponsabilityCard;

use App\Models\Assignment;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ResponsabilityCardDetailsLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'cardAssignments';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('asset.sicoin', 'SICOIN')
                ->width('100px')
                ->render(fn (Assignment $assignment) => $assignment->asset?->sicoin),

            TD::make('asset.description', 'Descripción')
                ->render(fn (Assignment $assignment) => $assignment->asset?->description),

            TD::make('asset.inventory_book', 'Libro')
                ->width('100px')
                ->render(fn (Assignment $assignment) => $assignment->asset?->inventory_book),

            TD::make('asset.folio_number', 'Folio')
                ->width('70px')
                ->render(fn (Assignment $assignment) => $assignment->asset?->folio_number),

            TD::make('asset.value', 'Valor')
                ->width('140px')
                ->render(fn (Assignment $assignment) => 
                     "<div style='white-space: nowrap;'>Q " . number_format($assignment->asset?->value ?? 0, 2) . "</div>"
                ),

            TD::make('asset.state', 'Estado')
                ->width('100px')
                ->render(fn (Assignment $assignment) => 
                    "<div style='white-space: nowrap;'>" . ($assignment->asset?->state ?? 'N/A') . "</div>"
                ),

            TD::make('date', 'Fecha')
                ->width('130px')
                ->render(fn (Assignment $assignment) => 
                    "<div style='white-space: nowrap;'>" . ($assignment->date?->format('d/m/Y') ?? 'N/A') . "</div>"
                ),
        ];
    }
}
