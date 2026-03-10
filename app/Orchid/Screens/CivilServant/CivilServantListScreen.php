<?php

namespace App\Orchid\Screens\CivilServant;

use App\Models\CivilServant;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;

class CivilServantListScreen extends Screen
{
    public function name(): ?string
    {
        return 'Funcionarios';
    }

    public function description(): ?string
    {
        return 'Listado de todos los funcionarios de la institución.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Crear Nuevo')
                ->icon('bs.plus-circle')
                ->route('platform.civil_servant.create'),
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
            \App\Orchid\Layouts\CivilServant\CivilServantListLayout::class
        ];
    }

    public function query(): iterable
    {
        return [
            'civilServants' => \App\Models\CivilServant::with('position')->filters()->defaultSort('id', 'desc')->paginate()
        ];
    }
}
