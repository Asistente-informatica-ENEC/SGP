<?php

namespace App\Orchid\Screens\Position;

use App\Models\Position;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;

class PositionEditScreen extends Screen
{
    /**
     * @var Position
     */
    public $position;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Position $position): iterable
    {
        return [
            'position' => $position
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->position->exists ? 'Editar Cargo' : 'Registrar Nuevo Cargo';
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
                ->canSee($this->position->exists),
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
            \App\Orchid\Layouts\Position\PositionEditLayout::class
        ];
    }

    public function save(Position $position, Request $request)
    {
        $position->fill($request->get('position'))->save();

        Alert::info('Cargo guardado correctamente.');

        return redirect()->route('platform.position.list');
    }

    public function remove(Position $position)
    {
        $position->delete();

        Alert::info('Cargo eliminado del catálogo.');

        return redirect()->route('platform.position.list');
    }
}
