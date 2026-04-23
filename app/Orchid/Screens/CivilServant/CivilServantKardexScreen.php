<?php

namespace App\Orchid\Screens\CivilServant;

use App\Models\CivilServant;
use App\Models\Asset;
use App\Models\ResponsabilityCard;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;

class CivilServantKardexScreen extends Screen
{
    public $civilServant;

    public function query(CivilServant $civilServant): iterable
    {
        $civilServant->load('position');

        $search = request('search');

        $activeAssets = Asset::where('state', 'ASIGNADO')
            ->whereHas('latestAssignment', function ($q) use ($civilServant) {
                $q->whereHas('responsabilityCard', function ($q2) use ($civilServant) {
                    $q2->where('civil_servant_id', $civilServant->id);
                });
            })
            ->when($search, function ($query, $search) {
                $query->where('sicoin', 'like', "%$search%");
            })
            ->get();

        return [
            'civilServant' => $civilServant,
            'activeAssets' => $activeAssets,
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

            \App\Orchid\Layouts\CivilServant\KardexAssetsListLayout::class,

            Layout::modal('dischargeModal', [
                Layout::rows([
                    Input::make('discharge_asset_id')->type('hidden'),
                    Input::make('observation')
                        ->title('Motivo de Descarga')
                        ->placeholder('Ej. Devolución por fin de labores, mal estado, etc.')
                        ->required()
                        ->maxlength(100),
                ]),
            ])->async('asyncDischargeData')
              ->title('Registrar Descargo de Bien')
              ->applyButton('Confirmar Descargo'),
        ];
    }

    public function asyncDischargeData(Asset $asset): array
    {
        return [
            'discharge_asset_id' => $asset->id,
        ];
    }

    public function dischargeAsset(CivilServant $civilServant, Request $request)
    {
        $request->validate([
            'discharge_asset_id' => 'required|exists:assets,id',
            'observation' => 'required|string|max:100',
        ]);

        $asset = Asset::findOrFail($request->input('discharge_asset_id'));

        $lastCard = ResponsabilityCard::orderBy('id', 'desc')->first();
        $newCode = '1';
        if ($lastCard && $lastCard->assignment_code) {
            // Extraer el número más grande o incrementar si es mixto
            preg_match('/\d+/', $lastCard->assignment_code, $matches);
            if (!empty($matches)) {
                $newCode = (string) (((int) $matches[0]) + 1);
            } else {
                $newCode = $lastCard->assignment_code;
                $newCode++;
            }
        }

        // Verifica si el código autogenerado ya existe (por seguridad)
        while(ResponsabilityCard::where('assignment_code', $newCode)->exists()) {
            $newCode = (string) (((int) $newCode) + 1);
        }

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

        Toast::info('El bien ha sido descargado exitosamente. Se ha generado la Constancia de Descargo ' . $newCode . '.');

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
