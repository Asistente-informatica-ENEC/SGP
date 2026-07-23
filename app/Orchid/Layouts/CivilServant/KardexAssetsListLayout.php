<?php

namespace App\Orchid\Layouts\CivilServant;

use App\Models\Asset;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;

class KardexAssetsListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'activeAssets';

    /**
     * @var string
     */
    protected $title = 'Bienes Actualmente Asignados';

    /**
     * Get the table cells to be rendered.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('sicoin', 'SICOIN'),
            TD::make('description', 'Descripción')->render(fn($asset) => '<span title="' . e($asset->description) . '">' . \Illuminate\Support\Str::limit($asset->description, 50) . '</span>'),
            TD::make('value', 'Valor')->render(fn($asset) => 'Q ' . number_format($asset->value, 2)),
            TD::make('tarjeta', 'Tarjeta de Responsabilidad')
                ->render(fn (Asset $asset) =>
                    $asset->latestAssignment?->responsabilityCard
                        ? 'No. ' . $asset->latestAssignment->responsabilityCard->formatted_code
                        : '—'
                ),
            TD::make('Acciones')
                ->align(TD::ALIGN_CENTER)
                ->width('320px')
                ->render(fn (Asset $asset) => Group::make([
                    ModalToggle::make('Descargar')
                        ->icon('bs.arrow-down-circle')
                        ->modal('dischargeModal')
                        ->method('dischargeAsset')
                        ->asyncParameters([
                            'asset' => $asset->id,
                        ])
                        ->type(Color::DANGER),

                    ModalToggle::make('Mal Estado')
                        ->icon('bs.exclamation-triangle')
                        ->modal('dischargeBadConditionModal')
                        ->method('dischargeAssetBadCondition')
                        ->asyncParameters([
                            'asset' => $asset->id,
                        ])
                        ->type(Color::WARNING),
                ])),
        ];
    }

    protected function textNotFound(): string
    {
        return 'No hay bienes asignados actualmente';
    }

    protected function subNotFound(): string
    {
        return 'El funcionario no tiene bienes cargados a su nombre en el inventario actual.';
    }
}
