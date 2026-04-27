<?php

namespace App\Orchid\Screens\Asset;

use App\Models\Asset;
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
            \Orchid\Screen\Actions\DropDown::make('Acciones')
                ->icon('bs.caret-down')
                ->list([
                    \Orchid\Screen\Actions\Link::make('Crear Nuevo Bien')
                        ->icon('bs.plus-circle')
                        ->route('platform.asset.create'),

                    \Orchid\Screen\Actions\Button::make('Exportar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportPdf')
                        ->rawClick(),

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
}
