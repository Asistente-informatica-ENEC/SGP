<?php

namespace App\Orchid\Screens\Report;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\ResponsabilityCard;
use App\Models\CivilServant;

class ReportBuilderScreen extends Screen
{
    public function query(): iterable
    {
        return [];
    }

    public function name(): ?string
    {
        return 'Módulo de Reportes';
    }

    public function description(): ?string
    {
        return 'Construya reportes personalizados eligiendo origen, columnas, filtros y agrupación.';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        $states = $this->getStates();
        $categories = $this->getCategories();
        $civilServants = $this->getCivilServants();
        $allColumns = $this->getAllColumns();

        return [
            Layout::block(Layout::rows([
                Select::make('data_source')
                    ->options([
                        'assets' => 'Bienes',
                        'cards' => 'Tarjetas de Responsabilidad',
                        'civil_servants' => 'Funcionarios',
                        'movements' => 'Movimientos',
                    ])
                    ->title('Origen de Datos')
                    ->help('Seleccione la fuente principal del reporte.'),

                Select::make('columns')
                    ->options($allColumns)
                    ->multiple()
                    ->title('Columnas a Incluir')
                    ->help('Seleccione las columnas que desea. Organizadas por origen.'),

                Group::make([
                    Select::make('state_filter')
                        ->options($states)
                        ->title('Filtrar por Estado'),
                    Select::make('category_filter')
                        ->options($categories)
                        ->title('Filtrar por Categoría'),
                ]),

                Group::make([
                    DateTimer::make('date_from')
                        ->title('Fecha Desde')
                        ->format('Y-m-d'),
                    DateTimer::make('date_to')
                        ->title('Fecha Hasta')
                        ->format('Y-m-d'),
                ]),

                Select::make('civil_servant_filter')
                    ->options($civilServants)
                    ->title('Filtrar por Funcionario'),

                Select::make('group_by')
                    ->options([
                        '_all' => 'Sin agrupar',
                        'state' => 'Estado',
                        'category' => 'Categoría',
                        'civil_servant' => 'Funcionario',
                        'card' => 'Tarjeta de Responsabilidad',
                    ])
                    ->title('Agrupación')
                    ->help('Agrupa los datos por el campo seleccionado.'),

                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportBuilderPdf')
                        ->rawClick()
                        ->set('formtarget', '_blank')
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportBuilderExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('Constructor de Reportes')
               ->description('Seleccione origen, columnas, filtros y agrupación para generar su reporte en PDF o Excel.'),
        ];
    }

    public function exportBuilderPdf(Request $request)
    {
        $source = $request->input('data_source') ?? 'assets';
        $selectedColumns = $request->input('columns') ?? [];
        $filters = $this->extractFilters($request);
        $groupBy = $request->input('group_by') ?? '';
        if ($groupBy === '_all') { $groupBy = ''; }

        if (empty($selectedColumns)) {
            return back()->with('error', 'Debe seleccionar al menos una columna.');
        }

        $extractors = $this->getExtractors();
        $columnLabels = $this->getColumnLabels();
        $rows = $this->querySource($source, $filters);

        $displayRows = $this->buildDisplayRows($rows, $selectedColumns, $extractors, $columnLabels, $groupBy);

        $sourceLabels = [
            'assets' => 'Bienes',
            'cards' => 'Tarjetas de Responsabilidad',
            'civil_servants' => 'Funcionarios',
            'movements' => 'Movimientos',
        ];

        $pdf = Pdf::loadView('pdf.dynamic_report', [
            'sourceLabel' => $sourceLabels[$source] ?? $source,
            'columns' => $selectedColumns,
            'columnLabels' => $columnLabels,
            'displayRows' => $displayRows,
            'filters' => $this->buildFilterLabels($filters),
            'groupBy' => $groupBy,
        ]);

        return response()->streamDownload(fn () => print($pdf->stream()), 'reporte_personalizado.pdf');
    }

    public function exportBuilderExcel(Request $request)
    {
        $source = $request->input('data_source') ?? 'assets';
        $selectedColumns = $request->input('columns') ?? [];
        $filters = $this->extractFilters($request);
        $groupBy = $request->input('group_by') ?? '';
        if ($groupBy === '_all') { $groupBy = ''; }

        if (empty($selectedColumns)) {
            return back()->with('error', 'Debe seleccionar al menos una columna.');
        }

        $extractors = $this->getExtractors();
        $columnLabels = $this->getColumnLabels();
        $rows = $this->querySource($source, $filters);

        $displayRows = $this->buildDisplayRows($rows, $selectedColumns, $extractors, $columnLabels, $groupBy);

        $headers = array_map(fn($col) => $columnLabels[$col] ?? $col, $selectedColumns);
        $data = [];

        foreach ($displayRows as $item) {
            if ($item['isGroupHeader']) {
                $data[] = array_fill(0, count($selectedColumns), $item['label']);
            } else {
                $data[] = $item['values'];
            }
        }

        return Excel::download(new \App\Exports\GenericReportExport($headers, $data), 'reporte_personalizado.xlsx');
    }

    private function getStates(): array
    {
        return ['_all' => 'Todos'] + [
            'DISPONIBLE' => 'Disponible',
            'ASIGNADO' => 'Asignado',
            'EN MAL ESTADO' => 'En Mal Estado',
            'DE BAJA' => 'De Baja',
            'SUSTRAÍDO' => 'Sustraído',
        ];
    }

    private function getCategories(): array
    {
        $cats = ['_all' => 'Todas'];
        try {
            $cats += Asset::select('category')->distinct()->whereNotNull('category')->orderBy('category')->pluck('category', 'category')->toArray();
        } catch (\Exception $e) {}
        return $cats;
    }

    private function getCivilServants(): array
    {
        $cs = ['_all' => 'Todos'];
        try {
            $cs += CivilServant::orderBy('name')->pluck('name', 'id')->toArray();
        } catch (\Exception $e) {}
        return $cs;
    }

    private function getAllColumns(): array
    {
        return [
            'Bienes' => [
                'asset.sicoin' => 'SICOIN',
                'asset.description' => 'Descripción',
                'asset.category' => 'Categoría',
                'asset.value' => 'Valor (Q)',
                'asset.state' => 'Estado',
                'asset.inventory_book' => 'Libro',
                'asset.folio_number' => 'Folio',
                'asset.date' => 'Fecha',
                'asset.civil_servant' => 'Funcionario Asignado',
                'asset.card_code' => 'No. Tarjeta',
                'asset.card_type' => 'Tipo de Tarjeta',
                'asset.unit' => 'Unidad',
                'asset.sede' => 'Sede',
            ],
            'Tarjetas de Responsabilidad' => [
                'card.code' => 'No. Tarjeta',
                'card.type' => 'Tipo',
                'card.assign_name' => 'Nombre Asignación',
                'card.role' => 'Cargo',
                'card.assign_date' => 'Fecha Asignación',
                'card.update_date' => 'Fecha Actualización',
                'card.civil_servant' => 'Funcionario',
                'card.unit' => 'Unidad',
                'card.sede' => 'Sede',
                'card.asset_count' => 'Cantidad de Bienes',
                'card.total_value' => 'Valor Total (Q)',
            ],
            'Funcionarios' => [
                'cs.name' => 'Nombre',
                'cs.sede' => 'Sede',
                'cs.nit' => 'NIT',
                'cs.unit' => 'Unidad',
                'cs.position' => 'Cargo',
                'cs.card_count' => 'Cantidad de Tarjetas',
                'cs.asset_count' => 'Cantidad de Bienes',
            ],
            'Movimientos' => [
                'mov.date' => 'Fecha',
                'mov.type' => 'Tipo de Movimiento',
                'mov.observation' => 'Observación',
                'mov.sicoin' => 'SICOIN',
                'mov.asset_description' => 'Descripción del Bien',
                'mov.asset_category' => 'Categoría',
                'mov.asset_value' => 'Valor (Q)',
                'mov.card_code' => 'No. Tarjeta',
                'mov.civil_servant' => 'Funcionario',
                'mov.unit' => 'Unidad',
            ],
        ];
    }

    private function getColumnLabels(): array
    {
        $labels = [];
        foreach ($this->getAllColumns() as $group => $cols) {
            foreach ($cols as $key => $label) {
                $labels[$key] = $label;
            }
        }
        return $labels;
    }

    private function getExtractors(): array
    {
        return [
            'asset.sicoin' => fn($row) => $row->sicoin ?? '',
            'asset.description' => fn($row) => $row->description ?? '',
            'asset.category' => fn($row) => $row->category ?? '',
            'asset.value' => fn($row) => 'Q ' . number_format((float)($row->value ?? 0), 2),
            'asset.state' => fn($row) => $row->state ?? '',
            'asset.inventory_book' => fn($row) => $row->inventory_book ?? '',
            'asset.folio_number' => fn($row) => $row->folio_number ?? '',
            'asset.date' => fn($row) => $row->date ? ($row->date instanceof \Carbon\Carbon ? $row->date->format('d-m-Y') : $row->date) : '',
            'asset.civil_servant' => fn($row) => $row->latestAssignment?->responsabilityCard?->civilServant->name ?? '',
            'asset.card_code' => fn($row) => $row->latestAssignment?->responsabilityCard ? 'No. ' . $row->latestAssignment->responsabilityCard->formatted_code : '',
            'asset.card_type' => fn($row) => ($row->latestAssignment?->responsabilityCard?->type ?? '') === 'mal_estado' ? 'Mal Estado' : (($row->latestAssignment?->responsabilityCard?->type ?? '') === 'descargo' ? 'Descargo' : 'Asignación'),
            'asset.unit' => fn($row) => $row->latestAssignment?->responsabilityCard?->civilServant->unit ?? '',
            'asset.sede' => fn($row) => $row->latestAssignment?->responsabilityCard?->civilServant->sede ?? '',

            'card.code' => fn($row) => 'No. ' . ($row->formatted_code ?? ''),
            'card.type' => fn($row) => match ($row->type ?? '') { 'asignacion' => 'Asignación', 'descargo' => 'Descargo', 'mal_estado' => 'Mal Estado', default => $row->type ?? '' },
            'card.assign_name' => fn($row) => $row->assign_name ?? '',
            'card.role' => fn($row) => $row->role ?? '',
            'card.assign_date' => fn($row) => $row->assign_date ? $row->assign_date->format('d-m-Y') : '',
            'card.update_date' => fn($row) => $row->update_date ? $row->update_date->format('d-m-Y') : '',
            'card.civil_servant' => fn($row) => $row->civilServant->name ?? '',
            'card.unit' => fn($row) => $row->civilServant->unit ?? '',
            'card.sede' => fn($row) => $row->civilServant->sede ?? '',
            'card.asset_count' => fn($row) => (string) $row->assignments->count(),
            'card.total_value' => fn($row) => 'Q ' . number_format($row->assignments->sum(fn($a) => $a->asset?->value ?? 0), 2),

            'cs.name' => fn($row) => $row->name ?? '',
            'cs.sede' => fn($row) => $row->sede ?? '',
            'cs.nit' => fn($row) => $row->nit ?? '',
            'cs.unit' => fn($row) => $row->unit ?? '',
            'cs.position' => fn($row) => $row->position->name ?? '',
            'cs.card_count' => fn($row) => (string) $row->responsabilityCards->count(),
            'cs.asset_count' => fn($row) => (string) $row->responsabilityCards->loadMissing('assignments')->sum(fn($c) => $c->assignments->count()),

            'mov.date' => fn($row) => $row->date ? $row->date->format('d-m-Y H:i') : '',
            'mov.type' => fn($row) => ($row->responsabilityCard->type ?? '') === 'descargo' ? 'Descargo' : 'Asignación',
            'mov.observation' => fn($row) => $row->observation ?? '',
            'mov.sicoin' => fn($row) => $row->asset->sicoin ?? '',
            'mov.asset_description' => fn($row) => $row->asset->description ?? '',
            'mov.asset_category' => fn($row) => $row->asset->category ?? '',
            'mov.asset_value' => fn($row) => 'Q ' . number_format((float)($row->asset->value ?? 0), 2),
            'mov.card_code' => fn($row) => $row->responsabilityCard ? 'No. ' . $row->responsabilityCard->formatted_code : '',
            'mov.civil_servant' => fn($row) => $row->responsabilityCard->civilServant->name ?? '',
            'mov.unit' => fn($row) => $row->responsabilityCard->civilServant->unit ?? '',
        ];
    }

    private function extractFilters(Request $request): array
    {
        $state = $request->input('state_filter') ?? '';
        $category = $request->input('category_filter') ?? '';
        $csId = $request->input('civil_servant_filter') ?? '';

        return [
            'state' => $state === '_all' ? '' : $state,
            'category' => $category === '_all' ? '' : $category,
            'date_from' => $request->input('date_from') ?? '',
            'date_to' => $request->input('date_to') ?? '',
            'civil_servant_id' => $csId === '_all' ? '' : $csId,
        ];
    }

    private function buildFilterLabels(array $filters): array
    {
        $labels = [];
        if (!empty($filters['state'])) {
            $labels['Estado'] = $filters['state'];
        }
        if (!empty($filters['category'])) {
            $labels['Categoría'] = $filters['category'];
        }
        if (!empty($filters['date_from'])) {
            $labels['Fecha Desde'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $labels['Fecha Hasta'] = $filters['date_to'];
        }
        if (!empty($filters['civil_servant_id'])) {
            $cs = CivilServant::find($filters['civil_servant_id']);
            $labels['Funcionario'] = $cs?->name ?? $filters['civil_servant_id'];
        }
        return $labels;
    }

    private function querySource(string $source, array $filters): \Illuminate\Support\Collection
    {
        return match ($source) {
            'assets' => $this->queryAssets($filters),
            'cards' => $this->queryCards($filters),
            'civil_servants' => $this->queryCivilServants($filters),
            'movements' => $this->queryMovements($filters),
            default => collect(),
        };
    }

    private function queryAssets(array $filters): \Illuminate\Support\Collection
    {
        $query = Asset::with('latestAssignment.responsabilityCard.civilServant');

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }
        if (!empty($filters['civil_servant_id'])) {
            $query->whereHas('latestAssignment.responsabilityCard', fn($q) => $q->where('civil_servant_id', $filters['civil_servant_id']));
        }

        return $query->get();
    }

    private function queryCards(array $filters): \Illuminate\Support\Collection
    {
        $query = ResponsabilityCard::with('civilServant', 'assignments.asset');

        if (!empty($filters['civil_servant_id'])) {
            $query->where('civil_servant_id', $filters['civil_servant_id']);
        }

        return $query->get();
    }

    private function queryCivilServants(array $filters): \Illuminate\Support\Collection
    {
        $query = CivilServant::with('position', 'responsabilityCards.assignments');

        if (!empty($filters['civil_servant_id'])) {
            $query->where('id', $filters['civil_servant_id']);
        }

        return $query->get();
    }

    private function queryMovements(array $filters): \Illuminate\Support\Collection
    {
        $query = Assignment::with('asset', 'responsabilityCard.civilServant');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }
        if (!empty($filters['civil_servant_id'])) {
            $query->whereHas('responsabilityCard', fn($q) => $q->where('civil_servant_id', $filters['civil_servant_id']));
        }

        return $query->orderBy('date', 'asc')->get();
    }

    private function getGroupValue($row, string $groupBy): string
    {
        return match ($groupBy) {
            'state' => $row->state ?? 'Sin Estado',
            'category' => $row->category ?? 'Sin Categoría',
            'civil_servant' => match (true) {
                $row instanceof \App\Models\CivilServant => $row->name ?? 'Sin Nombre',
                $row instanceof \App\Models\ResponsabilityCard => $row->civilServant->name ?? 'Sin Funcionario',
                $row instanceof \App\Models\Assignment => $row->responsabilityCard->civilServant->name ?? 'Sin Funcionario',
                default => $row->latestAssignment?->responsabilityCard?->civilServant->name ?? 'Sin Funcionario',
            },
            'card' => match (true) {
                $row instanceof \App\Models\ResponsabilityCard => 'No. ' . ($row->formatted_code ?? ''),
                $row instanceof \App\Models\Assignment => 'No. ' . ($row->responsabilityCard->formatted_code ?? ''),
                default => $row->latestAssignment?->responsabilityCard ? 'No. ' . $row->latestAssignment->responsabilityCard->formatted_code : 'Sin Tarjeta',
            },
            default => '',
        };
    }

    private function buildDisplayRows(\Illuminate\Support\Collection $rows, array $selectedColumns, array $extractors, array $columnLabels, string $groupBy): array
    {
        $displayRows = [];

        if (empty($groupBy)) {
            foreach ($rows as $row) {
                $values = [];
                foreach ($selectedColumns as $col) {
                    $values[] = isset($extractors[$col]) ? $extractors[$col]($row) : '';
                }
                $displayRows[] = ['isGroupHeader' => false, 'values' => $values];
            }
            return $displayRows;
        }

        $grouped = $rows->groupBy(fn($row) => $this->getGroupValue($row, $groupBy))->sortKeys();

        foreach ($grouped as $groupLabel => $groupRows) {
            $displayRows[] = ['isGroupHeader' => true, 'label' => $groupBy === 'state' ? 'Estado: ' : ($groupBy === 'category' ? 'Categoría: ' : ($groupBy === 'civil_servant' ? 'Funcionario: ' : 'Tarjeta: ')) . $groupLabel];

            foreach ($groupRows as $row) {
                $values = [];
                foreach ($selectedColumns as $col) {
                    $values[] = isset($extractors[$col]) ? $extractors[$col]($row) : '';
                }
                $displayRows[] = ['isGroupHeader' => false, 'values' => $values];
            }
        }

        return $displayRows;
    }
}
