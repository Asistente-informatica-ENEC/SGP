<?php

namespace App\Orchid\Screens\Position;

use App\Models\Position;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class PositionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'positions' => Position::filters()->defaultSort('id', 'desc')->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Cargos Institucionales';
    }

    public function description(): ?string
    {
        return 'Catálogo oficial de puestos y roles de cada funcionario.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Crear Nuevo Cargo')
                ->icon('bs.plus-circle')
                ->route('platform.position.create'),
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
            \App\Orchid\Layouts\Position\PositionListLayout::class
        ];
    }
}
