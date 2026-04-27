<?php

namespace App\Orchid\Screens\BadConditionCard;

use App\Models\ResponsabilityCard;
use App\Orchid\Layouts\BadConditionCard\BadConditionCardDetailsLayout;
use Orchid\Support\Facades\Layout;
use App\Orchid\Layouts\BadConditionCard\BadConditionCardListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Alert;
use App\Exports\ResponsabilityCardExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;
use App\Models\Asset;
use App\Orchid\Layouts\Asset\AssetHistoryLayout;
use App\Orchid\Layouts\BadConditionCard\BadConditionAssetListLayout;

class BadConditionCardListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $search = request('search');

        return [
            'badConditionCards' => ResponsabilityCard::with('civilServant')
                ->where('type', 'mal_estado')
                ->when($search, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('assignment_code', 'like', "%$search%")
                          ->orWhere('assign_name', 'like', "%$search%")
                          ->orWhere('role', 'like', "%$search%");
                    });
                })
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(),
            
            'badConditionAssets' => Asset::where('state', 'EN MAL ESTADO')
                ->when($search, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('sicoin', 'like', "%$search%")
                          ->orWhere('description', 'like', "%$search%");
                    });
                })
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
        return 'Bienes en Mal Estado';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Crear Nueva Tarjeta')
                ->icon('bs.plus-circle')
                ->route('platform.bad_condition_card.create'),
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
            Layout::rows([
                Group::make([
                    Input::make('search')
                        ->type('text')
                        ->value(request('search'))
                        ->title('Buscador General')
                        ->placeholder('Buscar por tarjeta, funcionario, SICOIN o descripción...')
                        ->help('Ingrese un término y pulse Filtrar.'),

                    \Orchid\Screen\Actions\Button::make('Filtrar')
                        ->icon('bs.search')
                        ->method('handleFilter')
                        ->type(Color::PRIMARY),

                    \Orchid\Screen\Actions\Button::make('Limpiar')
                        ->icon('bs.x-circle')
                        ->method('clearFilter')
                        ->type(Color::SECONDARY),
                ])->alignEnd(),
            ]),

            Layout::tabs([
                'Tarjetas de Mal Estado' => BadConditionCardListLayout::class,
                'Listado Individual de Bienes' => BadConditionAssetListLayout::class,
            ]),

            Layout::modal('modalBadConditionCard', [
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
                BadConditionCardDetailsLayout::class,
            ])
                ->title('Detalles de la Tarjeta de Mal Estado')
                ->async('asyncGetBadConditionCard')
                ->withoutApplyButton()
                ->size('modal-xl'),

            Layout::modal('assetHistoryModal', [
                AssetHistoryLayout::class
            ])->async('asyncGetAssetHistory')
              ->withoutApplyButton()
              ->size('modal-lg'),
        ];
    }

    public function asyncGetAssetHistory(Asset $asset): array
    {
        return [
            'view_asset' => $asset,
            'assetHistory' => $asset->assignments()
                ->with('responsabilityCard.civilServant')
                ->orderBy('date', 'desc')
                ->get(),
        ];
    }

    public function handleFilter(Request $request)
    {
        return redirect()->route('platform.bad_condition_card.list', array_filter($request->only(['search'])));
    }

    public function clearFilter()
    {
        return redirect()->route('platform.bad_condition_card.list');
    }

    /**
     * @param ResponsabilityCard $card
     *
     * @return array
     */
    public function asyncGetBadConditionCard(ResponsabilityCard $card): iterable
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

        // Cadena exclusiva de tarjetas de mal estado
        $vienenCard = ResponsabilityCard::where('civil_servant_id', $card->civil_servant_id)
            ->where('type', 'mal_estado')
            ->where('id', '<', $card->id)
            ->orderBy('id', 'desc')
            ->first();

        $vanCard = ResponsabilityCard::where('civil_servant_id', $card->civil_servant_id)
            ->where('type', 'mal_estado')
            ->where('id', '>', $card->id)
            ->orderBy('id', 'asc')
            ->first();

        // Calcular el Sumatorio Histórico de Vienen (solo tarjetas de mal estado)
        $vienenAmount = \App\Models\Assignment::whereHas('responsabilityCard', function($q) use ($card) {
            $q->where('civil_servant_id', $card->civil_servant_id)
              ->where('type', 'mal_estado')
              ->where('id', '<', $card->id);
        })->with(['asset', 'responsabilityCard'])->get()->sum(function($assignment) {
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
            'overrideUnit' => 'Bienes en mal estado',
        ])->setPaper(array(0, 0, 612, 936), 'landscape');

        return $pdf->stream('Tarjeta_Responsabilidad_' . $card->assignment_code . '.pdf');
    }

    public function remove(Request $request): void
    {
        $card = ResponsabilityCard::findOrFail($request->get('id'));
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($card) {
            $assetIds = $card->assignments()->pluck('asset_id');
            \App\Models\Asset::whereIn('id', $assetIds)->update(['state' => 'DISPONIBLE']);
            $card->delete();
        });

        Alert::info('La tarjeta de mal estado ha sido eliminada y sus bienes liberados.');
    }
}
