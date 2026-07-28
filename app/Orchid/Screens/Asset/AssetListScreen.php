<?php

namespace App\Orchid\Screens\Asset;

use App\Models\Asset;
use App\Models\CivilServant;
use App\Models\ResponsabilityCard;
use App\Models\Assignment;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;

use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Sight;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Layouts\Modal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

use App\Imports\AssetImport;
use App\Exports\AssetTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Support\Facades\Alert;

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
            \Orchid\Screen\Actions\DropDown::make('Descargo de bienes')
                ->icon('bs.download')
                ->list([
                    \Orchid\Screen\Actions\ModalToggle::make('Devolver a Disponible')
                        ->icon('bs.download')
                        ->modal('batchDischargeNormalModal')
                        ->method('batchDischargeNormal')
                        ->id('batch-discharge-normal-btn')
                        ->title('Descarga a Disponible'),

                    \Orchid\Screen\Actions\ModalToggle::make('Registrar Mal Estado')
                        ->icon('bs.exclamation-triangle')
                        ->modal('batchDischargeBadConditionModal')
                        ->method('batchDischargeBadCondition')
                        ->id('batch-discharge-bad-condition-btn')
                        ->title('Descarga por Mal Estado'),
                ]),

            \Orchid\Screen\Actions\DropDown::make('Acciones')
                ->icon('bs.caret-down')
                ->list([
                    \Orchid\Screen\Actions\Link::make('Crear Nuevo Bien')
                        ->icon('bs.plus-circle')
                        ->route('platform.asset.create'),

                    \Orchid\Screen\Actions\Button::make('Exportar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportPdf')
                        ->rawClick()
                        ->set('formtarget', '_blank'),

                    \Orchid\Screen\Actions\Button::make('Descargar Plantilla')
                        ->method('downloadTemplate')
                        ->icon('bs.download')
                        ->rawClick(),

                    \Orchid\Screen\Actions\ModalToggle::make('Importar Excel')
                        ->icon('bs.upload')
                        ->modal('importExcelModal')
                        ->method('import'),
                ]),
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
                        ->format('d-m-Y')
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

            Layout::modal('importExcelModal', Layout::rows([
                Input::make('importFile')
                    ->type('file')
                    ->required()
                    ->title('Archivo Excel')
                    ->help('Seleccione el archivo .xlsx o .csv con el inventario de bienes.'),
            ]))
                ->title('Importar Bienes desde Excel')
                ->applyButton('Empezar Importación'),

            Layout::modal('viewAssetModal', [
                Layout::rows([
                    Input::make('view_asset.id')->type('hidden'),
                ]),
                Layout::legend('view_asset', [
                    Sight::make('sicoin', 'SICOIN'),
                    Sight::make('description', 'Descripción')->render(fn($asset) => "<div style='white-space: pre-wrap;'>{$asset->description}</div>"),
                    Sight::make('inventory_book', 'Libro de Inventario'),
                    Sight::make('folio_number', 'Número de Folio'),
                    Sight::make('value', 'Valor')->render(fn($asset) => 'Q ' . number_format($asset->value, 2)),
                    Sight::make('state', 'Estado')->render(function($asset) {
                        $text = $asset->state;
                        if ($asset->state === 'ASIGNADO' && $asset->latestAssignment?->responsabilityCard?->civilServant) {
                            $text .= ' - ' . $asset->latestAssignment->responsabilityCard->civilServant->name;
                        }
                        return $text;
                    }),
                    Sight::make('category', 'Categoría'),
                    Sight::make('date', 'Fecha de Alta')->render(fn($asset) => $asset->date ? \Carbon\Carbon::parse($asset->date)->format('d-m-Y') : ''),
                    Sight::make('observations', 'Observaciones')->render(fn($asset) => "<div style='white-space: pre-wrap;'>{$asset->observations}</div>"),
                ])
            ])->async('asyncViewAsset')
              ->applyButton('Editar Bien')
              ->closeButton('Cerrar')
              ->size(Modal::SIZE_XL),

            Layout::modal('assetHistoryModal', [
                \App\Orchid\Layouts\Asset\AssetHistoryLayout::class
            ])->async('asyncGetAssetHistory')
              ->withoutApplyButton()
              ->size(Modal::SIZE_LG),

            Layout::modal('batchDischargeNormalModal', [
                Layout::view('orchid.batch-discharge-normal'),
            ])->title('Descargar Bienes - Estado DISPONIBLE')
              ->withoutApplyButton(),

            Layout::modal('batchDischargeBadConditionModal', [
                Layout::view('orchid.batch-discharge-bad-condition'),
            ])->title('Descargar Bienes en Mal Estado')
              ->withoutApplyButton(),

            Layout::view('orchid.asset-batch-selector-script'),
            \App\Orchid\Layouts\Asset\AssetListLayout::class
        ];
    }

    public function asyncViewAsset(Asset $asset): array
    {
        $asset->load('latestAssignment.responsabilityCard.civilServant');
        return [
            'view_asset' => $asset,
        ];
    }

    public function asyncGetAssetHistory(Asset $asset): array
    {
        return [
            'view_asset' => $asset,
            'assetHistory' => $asset->assignments()
                ->with('responsabilityCard.civilServant')
                ->orderBy('date', 'desc')
                ->get(),
        ];
    }

    public function redirectToEdit(Request $request)
    {
        return redirect()->route('platform.asset.edit', $request->input('view_asset.id'));
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

    public function downloadTemplate()
    {
        return Excel::download(new AssetTemplateExport, 'plantilla_bienes.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new AssetImport();
        Excel::import($import, $request->file('importFile'));

        Alert::info(sprintf(
            'Importación de bienes finalizada. Nuevos registros: %d. Registros actualizados: %d.',
            $import->imported,
            $import->updated
        ));

        return redirect()->route('platform.asset.list');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['search', 'state', 'category', 'date']);

        $assets = Asset::when($filters['search'] ?? null, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('sicoin', 'like', "%$search%")
                          ->orWhere('description', 'like', "%$search%");
                    });
                })
                ->when($filters['state'] ?? null, fn($q, $state) => $q->where('state', $state))
                ->when($filters['category'] ?? null, fn($q, $category) => $q->where('category', $category))
                ->when($filters['date'] ?? null, fn($q, $date) => $q->whereDate('date', $date))
                ->filters()
                ->defaultSort('id', 'desc')
                ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.asset_report', compact('assets', 'filters'));
        
        return $pdf->download('reporte_bienes.pdf');
    }

    /**
     * Procesa la descarga masiva de activos agrupados por funcionario
     * (o por encargado si son activos en mal estado)
     */
    public function batchDischargeNormal(Request $request)
    {
        Log::info('batchDischargeNormal called', ['user_id' => $request->user()?->id ?? null]);

        Log::info('batchDischargeNormal raw input', [
            'selected_asset_ids_normal' => $request->input('selected_asset_ids_normal'),
            'observations_normal' => $request->input('observations_normal'),
        ]);

        $request->validate([
            'selected_asset_ids_normal' => 'required|array|min:1',
            'selected_asset_ids_normal.*' => 'required|integer|exists:assets,id',
            'observations_normal' => 'required|array',
            'observations_normal.*' => 'required|string|max:500',
        ], [
            'selected_asset_ids_normal.min' => 'Debe seleccionar al menos un bien.',
        ]);

        $assetIds = $request->input('selected_asset_ids_normal');
        $observations = $request->input('observations_normal', []);

        Log::info('batchDischargeNormal validation passed', [
            'user_id' => $request->user()?->id ?? null,
            'asset_count' => count($assetIds),
        ]);

        $assets = Asset::with('latestAssignment.responsabilityCard.civilServant')
            ->whereIn('id', $assetIds)
            ->get();

        if ($assets->isEmpty()) {
            Alert::warning('No se encontraron bienes para procesar.');
            return redirect()->route('platform.asset.list');
        }

        $invalidStates = $assets->where('state', '!=', 'ASIGNADO');
        if ($invalidStates->isNotEmpty()) {
            throw ValidationException::withMessages([
                'selected_asset_ids_normal' => 'Solo puede descargar bienes en estado ASIGNADO con esta acción.',
            ]);
        }

        $unknownAssets = $assets->filter(fn($asset) => !$asset->latestAssignment || !$asset->latestAssignment->responsabilityCard);
        if ($unknownAssets->isNotEmpty()) {
            throw ValidationException::withMessages([
                'selected_asset_ids_normal' => 'Algunos bienes no tienen una asignación completa o responsable asociado.',
            ]);
        }

        try {
            $result = DB::transaction(function () use ($assets, $observations) {
                return $this->processBatchDischargeNormal($assets, $observations);
            });

            Log::info('batchDischargeNormal completed successfully', ['user_id' => $request->user()?->id ?? null]);

            $parts = [];
            if (!empty($result['descargoCards'])) {
                foreach ($result['descargoCards'] as $dc) {
                    $parts[] = "Descargo No. {$dc['code']} para {$dc['servant']}";
                }
            }
            $message = 'Descarga a disponible procesada correctamente. ' . implode('. ', $parts) . '.';
            Alert::success($message);
        } catch (\Exception $e) {
            Log::error('Error in batchDischargeNormal', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Alert::error('Error al procesar descarga a disponible: ' . $e->getMessage());
        }

        return redirect()->route('platform.asset.list');
    }

    public function batchDischargeBadCondition(Request $request)
    {
        Log::info('batchDischargeBadCondition called', ['user_id' => $request->user()?->id ?? null]);

        Log::info('batchDischargeBadCondition raw input', [
            'selected_asset_ids_bad_condition' => $request->input('selected_asset_ids_bad_condition'),
        ]);

        $request->validate([
            'selected_asset_ids_bad_condition' => 'required|array|min:1',
            'selected_asset_ids_bad_condition.*' => 'required|integer|exists:assets,id',
            'observations_bad_condition' => 'required|array',
            'observations_bad_condition.*' => 'required|string|max:500',
        ], [
            'selected_asset_ids_bad_condition.min' => 'Debe seleccionar al menos un bien.',
        ]);

        $assetIds = $request->input('selected_asset_ids_bad_condition');
        $observations = $request->input('observations_bad_condition', []);
        $currentUser = $request->user();

        $encargado = CivilServant::where('name', $currentUser->name)->first();
        if (!$encargado) {
            throw ValidationException::withMessages([
                'selected_asset_ids_bad_condition' => 'No se encontró el funcionario encargado de activos fijos.',
            ]);
        }

        $assets = Asset::with('latestAssignment.responsabilityCard.civilServant')
            ->whereIn('id', $assetIds)
            ->get();
        if ($assets->isEmpty()) {
            Alert::warning('No se encontraron bienes para procesar.');
            return redirect()->route('platform.asset.list');
        }

        $invalidStates = $assets->where('state', '!=', 'ASIGNADO');
        if ($invalidStates->isNotEmpty()) {
            throw ValidationException::withMessages([
                'selected_asset_ids_bad_condition' => 'Solo puede descargar bienes en estado ASIGNADO con esta acción.',
            ]);
        }

        try {
            $result = DB::transaction(function () use ($assets, $observations, $encargado) {
                return $this->processBatchBadCondition($assets, $observations, $encargado);
            });

            Log::info('batchDischargeBadCondition completed successfully', ['user_id' => $request->user()?->id ?? null]);

            $parts = [];
            if (!empty($result['descargoCards'])) {
                foreach ($result['descargoCards'] as $dc) {
                    $parts[] = "Descargo No. {$dc['code']} para {$dc['servant']}";
                }
            }
            if (!empty($result['malEstadoCards'])) {
                $codes = implode(', ', $result['malEstadoCodes']);
                $parts[] = "Tarjeta(s) de Mal Estado No. {$codes} para {$result['encargado']}";
            }
            $message = 'Descarga por mal estado procesada correctamente. ' . implode('. ', $parts) . '.';
            Alert::success($message);
        } catch (\Exception $e) {
            Log::error('Error in batchDischargeBadCondition', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Alert::error('Error al procesar descarga por mal estado: ' . $e->getMessage());
        }

        return redirect()->route('platform.asset.list');
    }

    /**
     * Procesa descarga de activos ASIGNADO agrupados por funcionario
     */
    private function processBatchDischargeNormal($assignedAssets, $observations): array
    {
        $descargoCards = [];

        $groupedByServant = $assignedAssets->groupBy(function ($asset) {
            return $asset->latestAssignment?->responsabilityCard?->civil_servant_id ?? 'unknown';
        });

        foreach ($groupedByServant as $servantId => $groupAssets) {
            if ($servantId === 'unknown') {
                continue;
            }

            $servant = CivilServant::find($servantId);
            if (!$servant) {
                continue;
            }

            $descargoCode = $this->generateNextAssignmentCodeForServant($servantId, 'descargo');

            $descargoCard = ResponsabilityCard::create([
                'civil_servant_id' => $servantId,
                'assign_name' => $servant->name,
                'role' => $servant->position?->name ?? 'Sin Puesto',
                'assignment_code' => $descargoCode,
                'type' => 'descargo',
                'assign_date' => now(),
                'update_date' => now(),
            ]);

            foreach ($groupAssets as $asset) {
                Assignment::create([
                    'responsability_card_id' => $descargoCard->id,
                    'asset_id' => $asset->id,
                    'observation' => $observations[$asset->id] ?? '',
                    'date' => now(),
                ]);

                $asset->update(['state' => 'DISPONIBLE']);
            }

            $descargoCards[] = [
                'code' => $descargoCode,
                'servant' => $servant->name,
            ];
        }

        return ['descargoCards' => $descargoCards];
    }

    /**
     * Procesa descarga de activos en mal estado:
     * 1. Crea tarjeta de descargo para cada funcionario original
     * 2. Cambia estado a EN MAL ESTADO
     * 3. Crea tarjetas de mal estado para el encargado de activos fijos
     *
     * @return array Información de las tarjetas generadas
     */
    private function processBatchBadCondition($badConditionAssets, $observations, $encargado): array
    {
        if (!$encargado) {
            throw new \Exception('Encargado de activos fijos no definido.');
        }

        $assetIds = $badConditionAssets->pluck('id')->toArray();
        $descargoCards = [];
        $malEstadoCodes = [];

        // 1. Agrupar bienes por funcionario actual y crear tarjeta de descargo por cada uno
        $groupedByServant = $badConditionAssets->groupBy(function ($asset) {
            return $asset->latestAssignment?->responsabilityCard?->civil_servant_id ?? 'unknown';
        });

        foreach ($groupedByServant as $servantId => $groupAssets) {
            if ($servantId === 'unknown') continue;

            $servant = CivilServant::find($servantId);
            if (!$servant) continue;

            $descargoCode = $this->generateNextAssignmentCodeForServant($servantId, 'descargo');

            $descargoCard = ResponsabilityCard::create([
                'civil_servant_id' => $servantId,
                'assign_name' => $servant->name,
                'role' => $servant->position?->name ?? 'Sin Puesto',
                'assignment_code' => $descargoCode,
                'type' => 'descargo',
                'assign_date' => now(),
                'update_date' => now(),
            ]);

            foreach ($groupAssets as $asset) {
                Assignment::create([
                    'responsability_card_id' => $descargoCard->id,
                    'asset_id' => $asset->id,
                    'observation' => $observations[$asset->id] ?? '',
                    'date' => now(),
                ]);
            }

            $descargoCards[] = [
                'code' => $descargoCode,
                'servant' => $servant->name,
            ];
        }

        // 2. Cambiar estado a EN MAL ESTADO
        Asset::whereIn('id', $assetIds)->update(['state' => 'EN MAL ESTADO']);

        // 3. Crear tarjetas de mal estado para el encargado (empaquetado inteligente)
        $assets = Asset::whereIn('id', $assetIds)->get()->keyBy('id');
        $chunks = [];
        $currentChunk = [];
        $currentLines = 0;
        $maxLines = 18;

        foreach ($assetIds as $id) {
            if (!isset($assets[$id])) continue;
            $asset = $assets[$id];
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

        $currentCode = $this->generateNextAssignmentCodeForServant($encargado->id, 'mal_estado');

        foreach ($chunks as $index => $chunkAssetIds) {
            if ($index > 0) {
                $currentCode = (string) (((int) $currentCode) + 1);
            }

            $malEstadoCard = ResponsabilityCard::create([
                'civil_servant_id' => $encargado->id,
                'assign_name' => $encargado->name,
                'role' => $encargado->position?->name ?? 'Sin Puesto',
                'assignment_code' => $currentCode,
                'type' => 'mal_estado',
                'assign_date' => now(),
                'update_date' => now(),
            ]);

            foreach ($chunkAssetIds as $aId) {
                Assignment::create([
                    'responsability_card_id' => $malEstadoCard->id,
                    'asset_id' => $aId,
                    'date' => now(),
                    'observation' => $observations[$aId] ?? '',
                ]);
            }

            $malEstadoCodes[] = $currentCode;
        }

        return [
            'descargoCards' => $descargoCards,
            'malEstadoCodes' => $malEstadoCodes,
            'malEstadoCards' => $malEstadoCodes,
            'encargado' => $encargado->name,
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
}
