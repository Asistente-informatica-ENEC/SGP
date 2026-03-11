<?php

namespace App\Orchid\Screens\CivilServant;

use App\Models\CivilServant;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;

use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;

class CivilServantListScreen extends Screen
{
    /**
     * @var string
     */
    public $search;

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
            Layout::rows([
                Group::make([
                    Input::make('search')
                        ->type('text')
                        ->value(request('search'))
                        ->title('Buscador de Funcionarios')
                        ->placeholder('Buscar por nombre, NIT o unidad...')
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
            \App\Orchid\Layouts\CivilServant\CivilServantListLayout::class
        ];
    }

    public function query(): iterable
    {
        $search = request('search');

        return [
            'civilServants' => \App\Models\CivilServant::with('position')
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%$search%")
                          ->orWhere('nit', 'like', "%$search%")
                          ->orWhere('unit', 'like', "%$search%");
                })
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate()
        ];
    }

    public function handleFilter(Request $request)
    {
        return redirect()->route('platform.civil_servant.list', array_filter($request->only(['search'])));
    }

    public function clearFilter()
    {
        return redirect()->route('platform.civil_servant.list');
    }
}
