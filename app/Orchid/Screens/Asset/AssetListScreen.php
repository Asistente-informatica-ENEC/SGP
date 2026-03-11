<?php

namespace App\Orchid\Screens\Asset;

use App\Models\Asset;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Layout;

use Illuminate\Http\Request;

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
            Layout::rows([
                Group::make([
                    Input::make('search')
                        ->type('text')
                        ->value(request('search'))
                        ->title('Buscador General')
                        ->placeholder('SICOIN o descripción...'),

                    Select::make('state')
                        ->options([
                            ''              => 'Todos los estados',
                            'DISPONIBLE'    => 'DISPONIBLE',
                            'ASIGNADO'      => 'ASIGNADO',
                            'DE BAJA'       => 'DE BAJA',
                            'EN MAL ESTADO' => 'EN MAL ESTADO',
                            'SUSTRAÍDO'     => 'SUSTRAÍDO',
                        ])
                        ->value(request('state'))
                        ->title('Estado'),

                    Select::make('category')
                        ->options([
                            '' => 'Todas las categorías',
                            'DE PRODUCCIÓN' => 'DE PRODUCCIÓN',
                            'DE OFICINA Y MUEBLES' => 'DE OFICINA Y MUEBLES',
                            'MÉDICO SANITARIO Y DE LABORATORIO' => 'MÉDICO SANITARIO Y DE LABORATORIO',
                            'EDUCACIONAL, CULTURAL Y RECREATIVO' => 'EDUCACIONAL, CULTURAL Y RECREATIVO',
                            'DE TRANSPORTE, TRACCIÓN Y ELEVACIÓN' => 'DE TRANSPORTE, TRACCIÓN Y ELEVACIÓN',
                            'DE COMUNICACIONES' => 'DE COMUNICACIONES',
                            'EQUIPO DE COMPUTO' => 'EQUIPO DE COMPUTO',
                            'OTROS ACTIVOS' => 'OTROS ACTIVOS',
                        ])
                        ->value(request('category'))
                        ->title('Categoría'),

                    DateTimer::make('date')
                        ->format('Y-m-d')
                        ->value(request('date'))
                        ->title('Fecha de Alta')
                        ->placeholder('Seleccione fecha...'),
                ]),
                
                Group::make([
                    \Orchid\Screen\Actions\Button::make('Filtrar Resultados')
                        ->icon('bs.search')
                        ->method('handleFilter')
                        ->type(\Orchid\Support\Color::PRIMARY),

                    \Orchid\Screen\Actions\Button::make('Limpiar Filtros')
                        ->icon('bs.x-circle')
                        ->method('clearFilter')
                        ->type(\Orchid\Support\Color::SECONDARY),
                ])->alignEnd(),
            ]),
            \App\Orchid\Layouts\Asset\AssetListLayout::class
        ];
    }

    public function query(): iterable
    {
        $search = request('search');
        $state = request('state');
        $category = request('category');
        $date = request('date');

        return [
            'assets' => Asset::when($search, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('sicoin', 'like', "%$search%")
                          ->orWhere('description', 'like', "%$search%");
                    });
                })
                ->when($state, fn($q) => $q->where('state', $state))
                ->when($category, fn($q) => $q->where('category', $category))
                ->when($date, fn($q) => $q->whereDate('date', $date))
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate()
        ];
    }

    public function handleFilter(Request $request)
    {
        return redirect()->route('platform.asset.list', array_filter($request->only(['search', 'state', 'category', 'date'])));
    }

    public function clearFilter()
    {
        return redirect()->route('platform.asset.list');
    }
}
