<?php

namespace App\Orchid\Screens\ResponsabilityCard;

use App\Models\ResponsabilityCard;
use App\Orchid\Layouts\ResponsabilityCard\ResponsabilityCardDetailsLayout;
use Orchid\Support\Facades\Layout;
use App\Orchid\Layouts\ResponsabilityCard\ResponsabilityCardListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Alert;
use App\Exports\ResponsabilityCardExport;
use Barryvdh\DomPDF\Facade\Pdf;
class ResponsabilityCardListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'responsabilityCards' => ResponsabilityCard::with('civilServant')
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Tarjetas de Responsabilidad';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Crear Nueva')
                ->icon('bs.plus-circle')
                ->route('platform.responsability_card.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            ResponsabilityCardListLayout::class,

            Layout::modal('modalResponsabilityCard', [
                Layout::rows([
                    \Orchid\Screen\Actions\Button::make('Emitir Tarjeta (Excel)')
                        ->icon('bs.file-earmark-excel')
                        ->method('exportExcel')
                        ->parameters([
                            'card' => request('card'),
                        ])
                        ->rawClick()
                        ->canSee(request()->has('card')),

                    \Orchid\Screen\Actions\Button::make('Exportar PDF')
                        ->icon('bs.file-earmark-pdf')
                        ->method('exportPdf')
                        ->parameters([
                            'card' => request('card'),
                        ])
                        ->rawClick()
                        ->canSee(request()->has('card')),
                ]),
                ResponsabilityCardDetailsLayout::class,
            ])
                ->title('Detalles de la Tarjeta de Responsabilidad')
                ->async('asyncGetResponsabilityCard')
                ->withoutApplyButton()
                ->size('modal-xl'),
        ];
    }

    /**
     * @param ResponsabilityCard $card
     *
     * @return array
     */
    public function asyncGetResponsabilityCard(ResponsabilityCard $card): iterable
    {
        return [
            'cardAssignments' => $card->assignments()->with('asset')->get(),
        ];
    }

    /**
     * @param ResponsabilityCard $card
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportExcel(ResponsabilityCard $card)
    {
        return (new ResponsabilityCardExport($card))->download();
    }

    /**
     * @param ResponsabilityCard $card
     */
    public function exportPdf(ResponsabilityCard $card)
    {
        $card->load(['civilServant.position', 'assignments.asset']);

        $vienenCard = ResponsabilityCard::where('civil_servant_id', $card->civil_servant_id)
            ->where('id', '<', $card->id)
            ->orderBy('id', 'desc')
            ->first();

        $vanCard = ResponsabilityCard::where('civil_servant_id', $card->civil_servant_id)
            ->where('id', '>', $card->id)
            ->orderBy('id', 'asc')
            ->first();

        // Calcular el Sumatorio Histórico de Vienen
        $vienenAmount = \App\Models\Assignment::whereHas('responsabilityCard', function($q) use ($card) {
            $q->where('civil_servant_id', $card->civil_servant_id)
              ->where('id', '<', $card->id);
        })->with('asset')->get()->sum(function($assignment) {
            return (float) optional($assignment->asset)->value;
        });

        // Sumar lo de esta tarjeta
        $cardAmount = $card->assignments->sum(function($assignment) {
            return (float) optional($assignment->asset)->value;
        });

        $vanAmount = $vienenAmount + $cardAmount;

        $pdf = Pdf::loadView('pdf.responsability_card', [
            'card' => $card,
            'vienen' => $vienenCard ? 'TARJETA No. ' . $vienenCard->assignment_code : '...............',
            'van'    => $vanCard ? 'TARJETA No. ' . $vanCard->assignment_code : '...............',
            'vienenAmount' => $vienenAmount,
            'vanAmount' => $vanAmount,
        ])->setPaper(array(0, 0, 612, 936), 'landscape');

        return $pdf->stream('Tarjeta_Responsabilidad_' . $card->assignment_code . '.pdf');
    }

    /**
     * @param Request $request
     */
    public function remove(Request $request): void
    {
        ResponsabilityCard::findOrFail($request->get('id'))->delete();

        Alert::info('La tarjeta de responsabilidad ha sido eliminada.');
    }
}
