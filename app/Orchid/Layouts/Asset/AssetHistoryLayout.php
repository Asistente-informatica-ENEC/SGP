<?php

namespace App\Orchid\Layouts\Asset;

use App\Models\Assignment;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class AssetHistoryLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'assetHistory';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('date', 'Fecha')
                ->render(fn (Assignment $assignment) => \Carbon\Carbon::parse($assignment->date)->format('d/m/Y g:i A')),

            TD::make('responsabilityCard.type', 'Movimiento')
                ->render(function (Assignment $assignment) {
                    $type = $assignment->responsabilityCard->type ?? 'asignacion';
                    return $type === 'descargo'
                        ? '<span class="badge bg-danger text-white">Descargo</span>'
                        : '<span class="badge bg-success text-white">Asignación</span>';
                }),

            TD::make('responsabilityCard.civilServant.name', 'Funcionario Responsable')
                ->render(function (Assignment $assignment) {
                    return $assignment->responsabilityCard->civilServant->name ?? 'Desconocido';
                }),

            TD::make('responsabilityCard.assignment_code', 'Comprobante')
                ->render(function (Assignment $assignment) {
                    return 'No. ' . ($assignment->responsabilityCard->assignment_code ?? 'N/A');
                }),

            TD::make('observation', 'Observaciones')
                ->style('max-width: 250px; white-space: normal;')
                ->render(fn (Assignment $assignment) => $assignment->observation ?? '-'),
        ];
    }

    protected function textNotFound(): string
    {
        return 'No hay historial de movimientos para este bien.';
    }
}
