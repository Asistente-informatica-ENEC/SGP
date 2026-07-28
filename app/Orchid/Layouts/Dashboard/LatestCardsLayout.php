<?php

namespace App\Orchid\Layouts\Dashboard;

use App\Models\ResponsabilityCard;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;

class LatestCardsLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'latestCards';

    /**
     * @var string
     */
    protected $title = 'Últimas Tarjetas Emitidas';

    /**
     * Get the table cells to be rendered.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('assignment_code', 'No. Tarjeta')
                ->render(fn (ResponsabilityCard $card) => 'No. ' . $card->formatted_code),

            TD::make('type', 'Tipo')
                ->render(fn (ResponsabilityCard $card) => 
                    $card->type === 'descargo' 
                        ? '<span class="badge bg-danger text-white">Descargo</span>'
                        : '<span class="badge bg-success text-white">Asignación</span>'
                ),

            TD::make('assign_name', 'Funcionario'),

            TD::make('created_at', 'Fecha')
                ->render(fn (ResponsabilityCard $card) => $card->created_at->format('d/m/Y')),

            TD::make('Acciones')
                ->align(TD::ALIGN_CENTER)
                ->render(function (ResponsabilityCard $card) {
                    return \Orchid\Screen\Actions\DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Button::make('Exportar PDF')
                                ->icon('bs.file-earmark-pdf')
                                ->method('exportPdf')
                                ->parameters(['card' => $card->id])
                                ->rawClick()
                                ->set('formtarget', '_blank'),

                            Button::make('Exportar Excel')
                                ->icon('bs.file-earmark-excel')
                                ->method('exportExcel')
                                ->parameters(['card' => $card->id])
                                ->rawClick(),
                        ]);
                }),
        ];
    }

    protected function textNotFound(): string
    {
        return 'Aún no se han emitido tarjetas de responsabilidad.';
    }
}
