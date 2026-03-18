<?php

namespace App\Orchid\Screens\ResponsabilityCard;

use App\Models\ResponsabilityCard;
use App\Models\CivilServant;
use App\Models\Assignment;
use App\Models\Asset;
use App\Orchid\Layouts\ResponsabilityCard\ResponsabilityCardEditLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Illuminate\Support\Facades\DB;

class ResponsabilityCardEditScreen extends Screen
{
    /**
     * @var ResponsabilityCard
     */
    public $responsabilityCard;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(ResponsabilityCard $responsabilityCard): iterable
    {
        $responsabilityCard->load('assignments.asset');
        
        return [
            'responsabilityCard' => $responsabilityCard,
            'assets' => $responsabilityCard->assignments->pluck('asset_id')->toArray(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->responsabilityCard->exists ? 'Editar Tarjeta de Responsabilidad' : 'Crear Nueva Tarjeta';
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
                ->confirm('¿Desea eliminar esta tarjeta? Los bienes vinculados volverán a estar disponibles.')
                ->canSee($this->responsabilityCard->exists),
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
            ResponsabilityCardEditLayout::class
        ];
    }

    /**
     * @param ResponsabilityCard $responsabilityCard
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(ResponsabilityCard $responsabilityCard, Request $request)
    {
        $request->validate([
            'responsabilityCard.civil_servant_id' => 'required',
            'responsabilityCard.assignment_code' => 'required|unique:responsability_cards,assignment_code,' . ($responsabilityCard->id ?? 'NULL'),
            'responsabilityCard.assign_date' => 'required|date',
        ], [
            'responsabilityCard.assignment_code.unique' => 'Este código de asignación ya ha sido registrado en otra tarjeta.',
        ]);

        $data = $request->get('responsabilityCard');
        $assetIds = $request->get('assets', []);

        DB::transaction(function () use ($responsabilityCard, $data, $assetIds) {
            // 1. Obtener datos del funcionario para el snapshot
            $servant = CivilServant::with('position')->findOrFail($data['civil_servant_id']);
            
            $responsabilityCard->fill($data);
            $responsabilityCard->assign_name = $servant->name;
            $responsabilityCard->role = $servant->position?->name ?? 'Sin Puesto';
            $responsabilityCard->save();

            // 2. Gestionar Asignaciones
            // Si es edición, podrías querer manejar la desvinculación. 
            // Para simplicidad en esta fase, creamos nuevas asignaciones para los assets seleccionados.
            
            foreach ($assetIds as $assetId) {
                // Verificar si ya está asignado a esta tarjeta
                $exists = Assignment::where('responsability_card_id', $responsabilityCard->id)
                    ->where('asset_id', $assetId)
                    ->exists();

                if (!$exists) {
                    Assignment::create([
                        'responsability_card_id' => $responsabilityCard->id,
                        'asset_id' => $assetId,
                        'date' => $responsabilityCard->assign_date,
                    ]);

                    // Actualizar estado del Bien
                    Asset::where('id', $assetId)->update(['state' => 'ASIGNADO']);
                }
            }
        });

        Alert::info('Tarjeta de responsabilidad guardada exitosamente.');

        return redirect()->route('platform.responsability_card.list');
    }

    /**
     * @param ResponsabilityCard $responsabilityCard
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function remove(ResponsabilityCard $responsabilityCard)
    {
        DB::transaction(function () use ($responsabilityCard) {
            // Liberar bienes antes de eliminar
            $assetIds = $responsabilityCard->assignments()->pluck('asset_id');
            Asset::whereIn('id', $assetIds)->update(['state' => 'DISPONIBLE']);
            
            $responsabilityCard->delete();
        });

        Alert::info('Tarjeta eliminada y bienes liberados.');

        return redirect()->route('platform.responsability_card.list');
    }
}
