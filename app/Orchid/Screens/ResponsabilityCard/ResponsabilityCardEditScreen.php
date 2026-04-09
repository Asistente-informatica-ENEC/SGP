<?php

namespace App\Orchid\Screens\ResponsabilityCard;

use App\Models\ResponsabilityCard;
use App\Models\CivilServant;
use App\Models\Assignment;
use App\Models\Asset;
use App\Orchid\Layouts\ResponsabilityCard\ResponsabilityCardEditLayout;
use App\Orchid\Layouts\ResponsabilityCard\AssetSelectionListener;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        
        $assetIds = $responsabilityCard->assignments->pluck('asset_id')->toArray();
        
        return [
            'responsabilityCard' => $responsabilityCard,
            'assets' => $assetIds,
            'selectedAssets' => \App\Models\Asset::whereIn('id', $assetIds)->get(),
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
            ResponsabilityCardEditLayout::class,
            AssetSelectionListener::class,
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
            
            // Si es edición, primero liberamos previos para evitar duplicidad y mantener estado limpio
            if ($responsabilityCard->exists) {
                $oldAssetIds = $responsabilityCard->assignments()->pluck('asset_id');
                Asset::whereIn('id', $oldAssetIds)->update(['state' => 'DISPONIBLE']);
                $responsabilityCard->assignments()->delete();
            }

            // 2. Algoritmo de Empaquetado Inteligente
            $assets = Asset::whereIn('id', $assetIds)->get()->keyBy('id');
            $chunks = [];
            $currentChunk = [];
            $currentLines = 0;
            $maxLines = 18; // ~18 líneas de texto por hoja (bastante holgado)

            foreach ($assetIds as $id) {
                if (!isset($assets[$id])) continue;
                $asset = $assets[$id];
                
                // Calcula cuantas líneas de 80 caracteres toma, sumando 1 línea de 'padding'
                $lines = ceil(mb_strlen($asset->description ?? '') / 80) + 1;
                
                if ($currentLines + $lines > $maxLines && count($currentChunk) > 0) {
                    $chunks[] = $currentChunk;
                    $currentChunk = [];
                    $currentLines = 0;
                }
                
                $currentChunk[] = $id;
                $currentLines += $lines;
            }
            if (!empty($currentChunk)) {
                $chunks[] = $currentChunk;
            }

            // 3. Crear tarjetas por cada página
            $currentCode = ltrim(rtrim($data['assignment_code']));
            $isFirst = true;

            foreach ($chunks as $chunkAssetIds) {
                if ($isFirst) {
                    $card = $responsabilityCard;
                    $isFirst = false;
                } else {
                    $card = new ResponsabilityCard();
                    $currentCode++; // Incrementa alfabético/numérico (Ej. 270 -> 271)
                    
                    if (ResponsabilityCard::where('assignment_code', $currentCode)->exists()) {
                        throw ValidationException::withMessages([
                            'responsabilityCard.assignment_code' => "El empaquetado automático necesita crear la hoja '{$currentCode}', pero ya está siendo utilizada en el sistema. Selecciona menos bienes o asegúrate de que haya correlativos libres."
                        ]);
                    }
                }
                
                $card->fill($data);
                $card->assignment_code = $currentCode;
                $card->assign_name = $servant->name;
                $card->role = $servant->position?->name ?? 'Sin Puesto';
                $card->save();

                // Crear Asignaciones y bloquear activo
                foreach ($chunkAssetIds as $aId) {
                    Assignment::create([
                        'responsability_card_id' => $card->id,
                        'asset_id' => $aId,
                        'date' => $card->assign_date,
                    ]);
                    Asset::where('id', $aId)->update(['state' => 'ASIGNADO']);
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
