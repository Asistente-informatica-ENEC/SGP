<?php

namespace App\Orchid\Screens\CivilServant;

use App\Models\CivilServant;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;

class CivilServantEditScreen extends Screen
{
    /**
     * @var CivilServant
     */
    public $civilServant;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(CivilServant $civilServant): iterable
    {
        return [
            'civilServant' => $civilServant
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->civilServant->exists ? 'Editar Funcionario' : 'Crear Funcionario';
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
                ->canSee($this->civilServant->exists),
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
            \App\Orchid\Layouts\CivilServant\CivilServantEditLayout::class
        ];
    }

    public function save(CivilServant $civilServant, Request $request)
    {
        $civilServant->fill($request->get('civilServant'))->save();

        Alert::info('Funcionario guardado correctamente.');

        return redirect()->route('platform.civil_servant.list');
    }

    public function remove(CivilServant $civilServant)
    {
        $civilServant->delete();

        Alert::info('Funcionario eliminado.');

        return redirect()->route('platform.civil_servant.list');
    }
}
