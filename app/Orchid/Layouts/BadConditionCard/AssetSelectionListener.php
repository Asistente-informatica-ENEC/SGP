<?php

namespace App\Orchid\Layouts\BadConditionCard;

use App\Models\Asset;
use Orchid\Screen\Fields\Relation;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Illuminate\Http\Request;

class AssetSelectionListener extends Listener
{
    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'assets.',
    ];

    /**
     * @param \Orchid\Screen\Repository $repository
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Orchid\Screen\Repository
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        $assets = $request->input('assets', []);
        $observations = $request->input('observations', []);

        return $repository
            ->set('assets', $assets)
            ->set('observations', $observations)
            ->set('selectedAssets', Asset::whereIn('id', $assets ?? [])->get());
    }

    /**
     * @return Layout[]
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Relation::make('assets.')
                    ->fromModel(Asset::class, 'sicoin', 'id')
                    ->applyScope('disponible')
                    ->multiple()
                    ->title('Bienes a Asignar (Buscar por SICOIN)')
                    ->help('Seleccione los bienes que desea vincular a esta tarjeta. Solo se muestran bienes disponibles.'),
            ]),
            SelectedAssetsTableLayout::class,
        ];
    }
}
