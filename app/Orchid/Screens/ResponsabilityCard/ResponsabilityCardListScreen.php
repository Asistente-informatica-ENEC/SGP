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
     * @param Request $request
     */
    public function remove(Request $request): void
    {
        ResponsabilityCard::findOrFail($request->get('id'))->delete();

        Alert::info('La tarjeta de responsabilidad ha sido eliminada.');
    }
}
