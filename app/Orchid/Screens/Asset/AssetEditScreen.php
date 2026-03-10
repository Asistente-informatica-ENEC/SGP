<?php

namespace App\Orchid\Screens\Asset;

use App\Models\Asset;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;

class AssetEditScreen extends Screen
{
    /**
     * @var Asset
     */
    public $asset;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Asset $asset): iterable
    {
        return [
            'asset' => $asset
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->asset->exists ? 'Editar Bien' : 'Registrar Nuevo Bien';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Guardar')
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Eliminar')
                ->icon('bs.trash')
                ->method('remove')
                ->canSee($this->asset->exists),
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
            \App\Orchid\Layouts\Asset\AssetEditLayout::class
        ];
    }

    public function save(Asset $asset, Request $request)
    {
        $asset->fill($request->get('asset'))->save();

        Alert::info('Bien guardado correctamente.');

        return redirect()->route('platform.asset.list');
    }

    public function remove(Asset $asset)
    {
        $asset->delete();

        Alert::info('Bien eliminado del sistema.');

        return redirect()->route('platform.asset.list');
    }
}
