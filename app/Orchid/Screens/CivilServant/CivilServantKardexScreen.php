<?php

namespace App\Orchid\Screens\CivilServant;

use App\Models\CivilServant;
use App\Models\Asset;
use App\Models\ResponsabilityCard;
use App\Models\Assignment;
use App\Orchid\Layouts\CivilServant\KardexAssetsListLayout;
use App\Orchid\Layouts\CivilServant\KardexBadConditionLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;
use Illuminate\Support\Facades\DB;

class CivilServantKardexScreen extends Screen
{
    public $civilServant;

    public function query(CivilServant $civilServant): iterable
    {
        $civilServant->load('position');

        $search = request('search');

        $activeAssets = Asset::where('state', 'ASIGNADO')
            ->with('latestAssignment.responsabilityCard')
            ->whereHas('latestAssignment', function ($q) use ($civilServant) {
                $q->whereHas('responsabilityCard', function ($q2) use ($civilServant) {
                    $q2->where('civil_servant_id', $civilServant->id);
                });
            })
            ->when($search, function ($query, $search) {
                $query->where('sicoin', 'like', "%$search%");
            })
            ->get();

        $badConditionAssets = Asset::where('state', 'EN MAL ESTADO')
            ->with(['assignments' => function ($q) use ($civilServant) {
                $q->whereHas('responsabilityCard', function ($q2) use ($civilServant) {
                    $q2->where('civil_servant_id', $civilServant->id)
                       ->where('type', 'mal_estado');
                });
                $q->with('responsabilityCard');
            }])
            ->whereHas('assignments', function ($q) use ($civilServant) {
                $q->whereHas('responsabilityCard', function ($q2) use ($civilServant) {
                    $q2->where('civil_servant_id', $civilServant->id)
                       ->where('type', 'mal_estado');
                });
            })
            ->when($search, function ($query, $search) {
                $query->where('sicoin', 'like', "%$search%");
            })
            ->get();

        return [
            'civilServant'       => $civilServant,
            'activeAssets'       => $activeAssets,
            'badConditionAssets' => $badConditionAssets,
        ];
    }

    public function name(): ?string
    {
        return 'Kardex de Activos - ' . $this->civilServant->name;
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        // Solo mostrar pestaña de mal estado si este funcionario tiene tarjetas de ese tipo
        $hasBadCondition = ResponsabilityCard::where('civil_servant_id', $this->civilServant->id)
            ->where('type', 'mal_estado')
            ->exists();

        $tabs = [
            'Bienes Asignados' => KardexAssetsListLayout::class,
        ];

        if ($hasBadCondition) {
            $tabs['Bienes en Mal Estado'] = KardexBadConditionLayout::class;
        }

        return [
            Layout::legend('civilServant', [
                Sight::make('name', 'Funcionario'),
                Sight::make('nit', 'NIT'),
                Sight::make('unit', 'Unidad'),
                Sight::make('position.name', 'Cargo'),
            ])->title('Información del Funcionario'),

            Layout::rows([
                Group::make([
                    Input::make('search')
                        ->type('text')
                        ->value(request('search'))
                        ->title('Buscador de Bienes')
                        ->placeholder('Buscar por código SICOIN...')
                        ->help('Ingrese un SICOIN y pulse Filtrar.'),

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

            // Si solo hay una pestaña, mostramos la tabla directamente sin tabs
            $hasBadCondition
                ? Layout::tabs($tabs)
                : KardexAssetsListLayout::class,

            // Modal para descargo normal (botón rojo)
            Layout::modal('dischargeModal', [
                Layout::rows([
                    Input::make('discharge_asset_id')->type('hidden'),
                    Input::make('observation')
                        ->title('Motivo de Descarga')
                        ->placeholder('Ej. Devolución por fin de labores, traslado, etc.')
                        ->required()
                        ->maxlength(100),
                ]),
            ])->async('asyncDischargeData')
              ->title('Registrar Descargo de Bien')
              ->applyButton('Confirmar Descargo'),

            // Modal para descargo por mal estado (botón amarillo)
            Layout::modal('dischargeBadConditionModal', [
                Layout::rows([
                    Input::make('discharge_asset_id')->type('hidden'),
                    Input::make('observation')
                        ->title('Motivo de Descarga por Mal Estado')
                        ->placeholder('Ej. Equipo dañado, deterioro por uso, etc.')
                        ->required()
                        ->maxlength(100)
                        ->help('El bien será descargado del funcionario actual y asignado en una tarjeta de mal estado a nombre del encargado de activos fijos.'),
                ]),
            ])->async('asyncDischargeData')
              ->title('Descargar Bien por Mal Estado')
              ->applyButton('Confirmar Descargo por Mal Estado'),
        ];
    }

    public function asyncDischargeData(Asset $asset): array
    {
        return [
            'discharge_asset_id' => $asset->id,
        ];
    }

    /**
     * Genera el siguiente código correlativo global.
     * asginacion y descargo comparten secuencia; mal_estado y otros tipos
     * tienen su propia secuencia global.
     */
    private function generateNextAssignmentCodeForServant(int $civilServantId, ?string $type = null): string
    {
        if ($type === 'descargo' || $type === 'asignacion') {
            $lastCard = ResponsabilityCard::whereIn('type', ['asignacion', 'descargo'])
                ->orderBy('id', 'desc')->first();
        } elseif ($type !== null) {
            $lastCard = ResponsabilityCard::where('type', $type)
                ->orderBy('id', 'desc')->first();
        } else {
            $lastCard = ResponsabilityCard::orderBy('id', 'desc')->first();
        }

        $newCode = '1';
        if ($lastCard && $lastCard->assignment_code) {
            $newCode = (string) (((int) $lastCard->assignment_code) + 1);
        }

        if ($type === 'descargo' || $type === 'asignacion') {
            while (ResponsabilityCard::whereIn('type', ['asignacion', 'descargo'])
                ->where('assignment_code', $newCode)->exists()) {
                $newCode = (string) (((int) $newCode) + 1);
            }
        } elseif ($type !== null) {
            while (ResponsabilityCard::where('type', $type)
                ->where('assignment_code', $newCode)->exists()) {
                $newCode = (string) (((int) $newCode) + 1);
            }
        } else {
            while (ResponsabilityCard::where('assignment_code', $newCode)->exists()) {
                $newCode = (string) (((int) $newCode) + 1);
            }
        }

        return $newCode;
    }

    /**
     * Descarga a disponible: el bien pasa a estado DISPONIBLE.
     */
    public function dischargeAsset(CivilServant $civilServant, Request $request)
    {
        $request->validate([
            'discharge_asset_id' => 'required|exists:assets,id',
            'observation' => 'required|string|max:100',
        ]);

        $asset = Asset::findOrFail($request->input('discharge_asset_id'));

        $result = DB::transaction(function () use ($civilServant, $asset, $request) {
            $newCode = $this->generateNextAssignmentCodeForServant($civilServant->id, 'descargo');

            $card = ResponsabilityCard::create([
                'civil_servant_id' => $civilServant->id,
                'assign_name' => $civilServant->name,
                'role' => $civilServant->position?->name ?? 'Sin Puesto',
                'assignment_code' => $newCode,
                'type' => 'descargo',
                'assign_date' => now(),
                'update_date' => now(),
            ]);

            Assignment::create([
                'responsability_card_id' => $card->id,
                'asset_id' => $asset->id,
                'observation' => $request->input('observation'),
                'date' => now(),
            ]);

            $asset->state = 'DISPONIBLE';
            $asset->save();

            return $newCode;
        });

        Toast::info('El bien ha sido descargado exitosamente. Se ha generado la Constancia de Descargo ' . $result . '.');

        return redirect()->route('platform.civil_servant.kardex', $civilServant->id);
    }

    /**
     * Descarga por mal estado: el bien se descarga del funcionario actual y se asigna
     * a una tarjeta de mal estado a nombre del encargado de activos fijos (usuario en sesión).
     */
    public function dischargeAssetBadCondition(CivilServant $civilServant, Request $request)
    {
        $request->validate([
            'discharge_asset_id' => 'required|exists:assets,id',
            'observation' => 'required|string|max:100',
        ]);

        // Buscar el funcionario correspondiente al usuario autenticado
        $currentUser = $request->user();
        $encargado = CivilServant::where('name', $currentUser->name)->first();

        if (!$encargado) {
            throw ValidationException::withMessages([
                'observation' => 'No se encontró un registro de funcionario con el nombre "' . $currentUser->name . '". El administrador debe crear el funcionario correspondiente al usuario encargado de activos fijos antes de poder realizar esta operación.',
            ]);
        }

        $asset = Asset::findOrFail($request->input('discharge_asset_id'));

        $result = DB::transaction(function () use ($civilServant, $encargado, $asset, $request) {
            // 1. Generar tarjeta de descargo para el funcionario original (filtrando solo por tipo 'descargo')
            $descargoCode = $this->generateNextAssignmentCodeForServant($civilServant->id, 'descargo');

            $descargoCard = ResponsabilityCard::create([
                'civil_servant_id' => $civilServant->id,
                'assign_name' => $civilServant->name,
                'role' => $civilServant->position?->name ?? 'Sin Puesto',
                'assignment_code' => $descargoCode,
                'type' => 'descargo',
                'assign_date' => now(),
                'update_date' => now(),
            ]);

            Assignment::create([
                'responsability_card_id' => $descargoCard->id,
                'asset_id' => $asset->id,
                'observation' => $request->input('observation'),
                'date' => now(),
            ]);

            // 2. Cambiar el estado del bien a EN MAL ESTADO
            $asset->state = 'EN MAL ESTADO';
            $asset->save();

            // 3. Generar tarjeta de mal estado para el encargado de activos fijos (filtrando solo por tipo 'mal_estado')
            $encargado->load('position');
            $malEstadoCode = $this->generateNextAssignmentCodeForServant($encargado->id, 'mal_estado');

            $malEstadoCard = ResponsabilityCard::create([
                'civil_servant_id' => $encargado->id,
                'assign_name' => $encargado->name,
                'role' => $encargado->position?->name ?? 'Sin Puesto',
                'assignment_code' => $malEstadoCode,
                'type' => 'mal_estado',
                'assign_date' => now(),
                'update_date' => now(),
            ]);

            // 4. Asignar el bien a la nueva tarjeta de mal estado
            Assignment::create([
                'responsability_card_id' => $malEstadoCard->id,
                'asset_id' => $asset->id,
                'observation' => $request->input('observation'),
                'date' => now(),
            ]);

            return [
                'descargoCode' => $descargoCode,
                'malEstadoCode' => $malEstadoCode,
                'encargadoName' => $encargado->name,
            ];
        });

        Toast::info(
            'Bien descargado por mal estado. Constancia de Descargo No. ' . $result['descargoCode'] .
            ' generada. Tarjeta de Mal Estado No. ' . $result['malEstadoCode'] .
            ' asignada a ' . $result['encargadoName'] . '.'
        );

        return redirect()->route('platform.civil_servant.kardex', $civilServant->id);
    }

    public function handleFilter(CivilServant $civilServant, Request $request)
    {
        return redirect()->route('platform.civil_servant.kardex', [
            'civilServant' => $civilServant->id,
            'search' => $request->input('search')
        ]);
    }

    public function clearFilter(CivilServant $civilServant)
    {
        return redirect()->route('platform.civil_servant.kardex', $civilServant->id);
    }
}
