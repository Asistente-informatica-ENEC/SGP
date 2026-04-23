<?php

namespace App\Orchid\Layouts\ResponsabilityCard;

use App\Models\ResponsabilityCard;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Button;

class ResponsabilityCardListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'responsabilityCards';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('assignment_code', 'Tarjeta')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn (ResponsabilityCard $card) => ModalToggle::make('No. ' . $card->assignment_code)
                    ->modal('modalResponsabilityCard')
                    ->method('asyncGetResponsabilityCard')
                    ->asyncParameters([
                        'card' => $card->id,
                    ])),

            TD::make('assign_name', 'Funcionario (Histórico)')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('role', 'Puesto (Histórico)')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('assign_date', 'Fecha de Emisión')
                ->sort()
                ->render(fn (ResponsabilityCard $card) => $card->assign_date->format('d/m/Y')),

            TD::make('created_at', 'Creado')
                ->sort()
                ->render(fn (ResponsabilityCard $card) => $card->created_at->toDateTimeString()),

            TD::make('Acciones')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (ResponsabilityCard $card) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make('Editar')
                            ->route('platform.responsability_card.edit', [$card])
                            ->icon('bs.pencil'),

                        Button::make('Emitir Excel')
                            ->icon('bs.file-earmark-excel')
                            ->method('exportExcel', [
                                'card' => $card->id,
                            ])
                            ->rawClick(),

                        Button::make('Exportar PDF')
                            ->icon('bs.file-earmark-pdf')
                            ->method('exportPdf', [
                                'card' => $card->id,
                            ])
                            ->rawClick(),

                        Button::make('Eliminar')
                            ->icon('bs.trash')
                            ->confirm('Una vez eliminada la tarjeta, todos los registros asociados se borrarán permanentemente.')
                            ->method('remove', [
                                'id' => $card->id,
                            ]),
                    ])),
        ];
    }

    protected function textNotFound(): string
    {
        return 'No hay tarjetas de responsabilidad registradas actualmente';
    }

    protected function subNotFound(): string
    {
        return 'Genera una nueva tarjeta para visualizarla en este listado';
    }
}
