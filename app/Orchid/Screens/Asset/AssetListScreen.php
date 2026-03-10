<?php

namespace App\Orchid\Screens\Asset;

use App\Models\Asset;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class AssetListScreen extends Screen
{
    public function name(): ?string
    {
        return 'Inventario de Bienes';
    }

    public function description(): ?string
    {
        return 'Listado completo de bienes institucionales registrados.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Crear Nuevo Bien')
                ->icon('bs.plus-circle')
                ->route('platform.asset.create'),
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
            \App\Orchid\Layouts\Asset\AssetListLayout::class
        ];
    }

    public function query(): iterable
    {
        return [
            'assets' => Asset::filters()->defaultSort('id', 'desc')->paginate()
        ];
    }
}
