<?php

namespace App\Orchid\Screens\Report;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\ResponsabilityCard;

class ReportListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
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
        return 'Centro de mando para descargar reportería gerencial y de auditoría.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::block(Layout::rows([
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportFinancialPdf')
                        ->rawClick()
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportFinancialExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('1. Consolidado Financiero')
               ->description('Valor monetario de bienes sumados por categoría.'),

            Layout::block(Layout::rows([
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportDepartmentPdf')
                        ->rawClick()
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportDepartmentExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('2. Inventario por Unidad')
               ->description('Bienes asignados agrupados por departamento.'),

            Layout::block(Layout::rows([
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportDischargedPdf')
                        ->rawClick()
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportDischargedExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('3. Reporte de Bajas y Mermas')
               ->description('Únicamente bienes en estado: De Baja, Sustraído, En Mal Estado.'),

            Layout::block(Layout::rows([
                Group::make([
                    DateTimer::make('start_date')
                        ->title('Fecha Inicio')
                        ->format('Y-m-d')
                        ->required(),
                    DateTimer::make('end_date')
                        ->title('Fecha Fin')
                        ->format('Y-m-d')
                        ->required(),
                ]),
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportMovementsPdf')
                        ->rawClick()
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportMovementsExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('4. Movimientos Mensuales')
               ->description('Auditoría de asignaciones y descargos en un rango de fechas.'),

            Layout::block(Layout::rows([
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportCardValuesPdf')
                        ->rawClick()
                        ->set('formtarget', '_blank')
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportCardValuesExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('5. Valores por Tarjeta de Responsabilidad')
               ->description('Monto total de bienes agrupados por tarjeta de responsabilidad.'),

            Layout::block(Layout::rows([
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportAssetsSummaryPdf')
                        ->rawClick()
                        ->set('formtarget', '_blank')
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportAssetsSummaryExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('6. Resumen de Bienes')
               ->description('Totales de bienes agrupados por estado y categoría.'),

            Layout::block(Layout::rows([
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportInventarioGeneralPdf')
                        ->rawClick()
                        ->set('formtarget', '_blank')
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportInventarioGeneralExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('7. Inventario General de Bienes')
               ->description('Listado completo de todos los bienes con funcionario asignado y No. de tarjeta.'),

            Layout::block(Layout::rows([
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportPorFuncionarioPdf')
                        ->rawClick()
                        ->set('formtarget', '_blank')
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportPorFuncionarioExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('8. Bienes por Funcionario')
               ->description('Bienes asignados agrupados por funcionario responsable.'),

            Layout::block(Layout::rows([
                Group::make([
                    Button::make('Descargar PDF')
                        ->icon('bs.file-pdf')
                        ->method('exportDisponiblesPdf')
                        ->rawClick()
                        ->set('formtarget', '_blank')
                        ->type(Color::DANGER),
                    Button::make('Descargar Excel')
                        ->icon('bs.file-excel')
                        ->method('exportDisponiblesExcel')
                        ->rawClick()
                        ->type(Color::SUCCESS),
                ])->alignEnd(),
            ]))->title('9. Bienes Disponibles (No Asignados)')
               ->description('Solo bienes en estado DISPONIBLE, sin responsable asignado.'),
        ];
    }

    public function exportFinancialPdf(Request $request)
    {
        $assets = Asset::all();
        $categories = $assets->groupBy('category')->map(function($group) {
            return [
                'count' => $group->count(),
                'total_value' => $group->sum('value')
            ];
        });

        $filters = ['Tipo de Reporte' => 'Consolidado Financiero por Categorías'];

        $pdf = Pdf::loadView('pdf.report_financial', compact('categories', 'assets', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'reporte_financiero.pdf');
    }

    public function exportFinancialExcel(Request $request)
    {
        $assets = Asset::all();
        $categories = $assets->groupBy('category')->map(function($group, $key) {
            return [
                'Categoría' => $key,
                'Cantidad' => $group->count(),
                'Valor Total' => $group->sum('value')
            ];
        })->values()->toArray();

        return Excel::download(new \App\Exports\GenericReportExport(
            ['Categoría', 'Cantidad', 'Valor Total (Q)'], 
            $categories
        ), 'reporte_financiero.xlsx');
    }

    public function exportDepartmentPdf(Request $request)
    {
        $assets = Asset::where('state', 'ASIGNADO')
            ->with('latestAssignment.responsabilityCard.civilServant')
            ->get();

        $departments = [];
        foreach ($assets as $asset) {
            // Check if latest assignment is an assignment (not discharge) 
            // In our system, ASIGNADO state implies the latest movement was an assignment.
            $unit = $asset->latestAssignment?->responsabilityCard?->civilServant?->unit ?? 'Sin Unidad Asignada';
            
            if (!isset($departments[$unit])) {
                $departments[$unit] = collect();
            }
            $departments[$unit]->push($asset);
        }

        $filters = ['Tipo de Reporte' => 'Inventario Físico Agrupado por Departamento'];

        $pdf = Pdf::loadView('pdf.report_department', compact('departments', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'reporte_departamentos.pdf');
    }

    public function exportDepartmentExcel(Request $request)
    {
        $assets = Asset::where('state', 'ASIGNADO')
            ->with('latestAssignment.responsabilityCard.civilServant')
            ->get();

        $data = [];
        foreach ($assets as $asset) {
            $unit = $asset->latestAssignment?->responsabilityCard?->civilServant?->unit ?? 'Sin Unidad Asignada';
            $funcName = $asset->latestAssignment?->responsabilityCard?->civilServant?->name ?? 'Desconocido';

            $data[] = [
                'Departamento' => $unit,
                'Funcionario' => $funcName,
                'SICOIN' => $asset->sicoin,
                'Descripción' => $asset->description,
                'Categoría' => $asset->category,
                'Valor (Q)' => $asset->value,
            ];
        }

        return Excel::download(new \App\Exports\GenericReportExport(
            ['Departamento', 'Funcionario', 'SICOIN', 'Descripción', 'Categoría', 'Valor (Q)'], 
            $data
        ), 'reporte_departamentos.xlsx');
    }

    public function exportDischargedPdf(Request $request)
    {
        $filters = [
            'Tipo de Reporte' => 'Bienes Dados de Baja o Sustraídos',
            'Estados Incluidos' => 'DE BAJA, SUSTRAÍDO, EN MAL ESTADO'
        ];
        $assets = Asset::whereIn('state', ['DE BAJA', 'SUSTRAÍDO', 'EN MAL ESTADO'])->get();
        $pdf = Pdf::loadView('pdf.asset_report', compact('assets', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'reporte_bajas_mermas.pdf');
    }

    public function exportDischargedExcel(Request $request)
    {
        $assets = Asset::whereIn('state', ['DE BAJA', 'SUSTRAÍDO', 'EN MAL ESTADO'])->get()->map(function($asset) {
            return [
                'SICOIN' => $asset->sicoin,
                'Descripción' => $asset->description,
                'Libro' => $asset->inventory_book,
                'Folio' => $asset->folio_number,
                'Valor' => $asset->value,
                'Estado' => $asset->state,
                'Categoría' => $asset->category,
                'Fecha' => $asset->date
            ];
        })->toArray();

        return Excel::download(new \App\Exports\GenericReportExport(
            ['SICOIN', 'Descripción', 'Libro', 'Folio', 'Valor (Q)', 'Estado', 'Categoría', 'Fecha'], 
            $assets
        ), 'reporte_bajas_mermas.xlsx');
    }

    public function exportMovementsPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $filters = [
            'Tipo de Reporte' => 'Auditoría de Movimientos Mensuales',
            'Desde' => \Carbon\Carbon::parse($request->input('start_date'))->format('d-m-Y'),
            'Hasta' => \Carbon\Carbon::parse($request->input('end_date'))->format('d-m-Y')
        ];

        $assignments = Assignment::with(['asset', 'responsabilityCard.civilServant'])
            ->whereDate('date', '>=', $request->input('start_date'))
            ->whereDate('date', '<=', $request->input('end_date'))
            ->orderBy('date', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdf.report_movements', compact('assignments', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'reporte_movimientos.pdf');
    }

    public function exportMovementsExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $assignments = Assignment::with(['asset', 'responsabilityCard.civilServant'])
            ->whereDate('date', '>=', $request->input('start_date'))
            ->whereDate('date', '<=', $request->input('end_date'))
            ->orderBy('date', 'asc')
            ->get()->map(function($assignment) {
                return [
                    'Fecha' => \Carbon\Carbon::parse($assignment->date)->format('d-m-Y H:i'),
                    'Movimiento' => ($assignment->responsabilityCard->type ?? '') === 'descargo' ? 'Descargo' : 'Asignación',
                    'Tarjeta No.' => 'No. ' . ($assignment->responsabilityCard->assignment_code ?? 'N/A'),
                    'SICOIN' => $assignment->asset->sicoin ?? 'N/A',
                    'Funcionario' => $assignment->responsabilityCard->civilServant->name ?? 'N/A',
                    'Observaciones' => $assignment->observation
                ];
            })->toArray();

        return Excel::download(new \App\Exports\GenericReportExport(
            ['Fecha', 'Movimiento', 'Tarjeta No.', 'SICOIN', 'Funcionario', 'Observaciones'], 
            $assignments
        ), 'reporte_movimientos.xlsx');
    }

    public function exportCardValuesPdf(Request $request)
    {
        $cards = ResponsabilityCard::with(['assignments.asset', 'civilServant'])->get();

        $filters = ['Tipo de Reporte' => 'Valores por Tarjeta de Responsabilidad'];

        $pdf = Pdf::loadView('pdf.report_card_values', compact('cards', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'reporte_valores_tarjetas.pdf');
    }

    public function exportCardValuesExcel(Request $request)
    {
        $cards = ResponsabilityCard::with(['assignments.asset'])->get()->map(function ($card) {
            $cardTotal = $card->assignments->sum(fn($a) => $a->asset?->value ?? 0);
            $cardCount = $card->assignments->count();
            $typeLabel = match ($card->type) {
                'asignacion' => 'Asignación',
                'descargo'   => 'Descargo',
                'mal_estado' => 'Mal Estado',
                default      => $card->type,
            };
            return [
                'No. Tarjeta' => 'No. ' . $card->formatted_code,
                'Funcionario' => $card->assign_name,
                'Tipo'        => $typeLabel,
                'Bienes'      => $cardCount,
                'Valor Total (Q)' => number_format($cardTotal, 2),
            ];
        })->toArray();

        return Excel::download(new \App\Exports\GenericReportExport(
            ['No. Tarjeta', 'Funcionario', 'Tipo', 'Bienes', 'Valor Total (Q)'],
            $cards
        ), 'reporte_valores_tarjetas.xlsx');
    }

    public function exportAssetsSummaryPdf(Request $request)
    {
        $totalAssets = Asset::count();
        $totalValue = Asset::sum('value');
        $byState = Asset::selectRaw('state, COUNT(*) as count, SUM(value) as total_value')
            ->groupBy('state')->get();
        $byCategory = Asset::selectRaw('category, COUNT(*) as count, SUM(value) as total_value')
            ->groupBy('category')->get();

        $filters = ['Tipo de Reporte' => 'Resumen de Bienes'];

        $pdf = Pdf::loadView('pdf.report_assets_summary', compact('totalAssets', 'totalValue', 'byState', 'byCategory', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'reporte_resumen_bienes.pdf');
    }

    public function exportAssetsSummaryExcel(Request $request)
    {
        $totalAssets = Asset::count();
        $totalValue = Asset::sum('value');

        $byState = Asset::selectRaw('state as estado, COUNT(*) as cantidad, SUM(value) as valor_total')
            ->groupBy('estado')->get()->toArray();

        $byCategory = Asset::selectRaw('category as categoria, COUNT(*) as cantidad, SUM(value) as valor_total')
            ->groupBy('categoria')->get()->toArray();

        // Combine into a single data set with a header row for each section
        $data = [
            ['RESUMEN GENERAL', '', ''],
            ['Total de Bienes', $totalAssets, ''],
            ['Valor Total Estimado (Q)', number_format($totalValue, 2), ''],
            ['', '', ''],
            ['POR ESTADO', '', ''],
            ['Estado', 'Cantidad', 'Valor Total (Q)'],
        ];
        foreach ($byState as $row) {
            $data[] = [$row['estado'], $row['cantidad'], number_format($row['valor_total'], 2)];
        }
        $data[] = ['', '', ''];
        $data[] = ['POR CATEGORÍA', '', ''];
        $data[] = ['Categoría', 'Cantidad', 'Valor Total (Q)'];
        foreach ($byCategory as $row) {
            $data[] = [$row['categoria'], $row['cantidad'], number_format($row['valor_total'], 2)];
        }

        return Excel::download(new \App\Exports\GenericReportExport(
            ['Detalle', 'Cantidad', 'Valor (Q)'],
            $data
        ), 'reporte_resumen_bienes.xlsx');
    }

    public function exportInventarioGeneralPdf(Request $request)
    {
        $assets = Asset::with('latestAssignment.responsabilityCard.civilServant')->get();
        $totalAssets = $assets->count();
        $totalValue = $assets->sum('value');
        $filters = ['Tipo de Reporte' => 'Inventario General de Bienes'];

        $pdf = Pdf::loadView('pdf.report_inventario_general', compact('assets', 'totalAssets', 'totalValue', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'inventario_general.pdf');
    }

    public function exportInventarioGeneralExcel(Request $request)
    {
        $assets = Asset::with('latestAssignment.responsabilityCard.civilServant')->get()->map(function ($asset) {
            $card = $asset->latestAssignment?->responsabilityCard;
            return [
                'SICOIN'               => $asset->sicoin,
                'Descripción'          => $asset->description,
                'Categoría'            => $asset->category,
                'Valor (Q)'            => number_format($asset->value, 2),
                'Estado'               => $asset->state,
                'Funcionario'          => $card?->civilServant->name ?? ($asset->state === 'ASIGNADO' ? '—' : ''),
                'No. Tarjeta'          => $card ? 'No. ' . $card->formatted_code : '',
            ];
        })->toArray();

        return Excel::download(new \App\Exports\GenericReportExport(
            ['SICOIN', 'Descripción', 'Categoría', 'Valor (Q)', 'Estado', 'Funcionario', 'No. Tarjeta'],
            $assets
        ), 'inventario_general.xlsx');
    }

    public function exportPorFuncionarioPdf(Request $request)
    {
        $assignedAssets = Asset::where('state', 'ASIGNADO')
            ->with('latestAssignment.responsabilityCard.civilServant')
            ->get();

        $groups = $assignedAssets->groupBy(function ($asset) {
            return $asset->latestAssignment?->responsabilityCard?->civilServant->name ?? 'Sin Asignar';
        })->sortKeys();

        $filters = ['Tipo de Reporte' => 'Bienes por Funcionario'];

        $pdf = Pdf::loadView('pdf.report_por_funcionario', compact('groups', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'bienes_por_funcionario.pdf');
    }

    public function exportPorFuncionarioExcel(Request $request)
    {
        $assignedAssets = Asset::where('state', 'ASIGNADO')
            ->with('latestAssignment.responsabilityCard.civilServant')
            ->get();

        $groups = $assignedAssets->groupBy(function ($asset) {
            return $asset->latestAssignment?->responsabilityCard?->civilServant->name ?? 'Sin Asignar';
        })->sortKeys();

        $data = [];
        foreach ($groups as $servantName => $servantAssets) {
            $data[] = ['FUNCIONARIO: ' . $servantName, '', '', ''];
            $data[] = ['SICOIN', 'Descripción', 'Categoría', 'Valor (Q)'];
            foreach ($servantAssets as $asset) {
                $data[] = [$asset->sicoin, $asset->description, $asset->category, number_format($asset->value, 2)];
            }
            $data[] = ['', '', 'Total', number_format($servantAssets->sum('value'), 2)];
            $data[] = [];
        }

        $grandTotal = $assignedAssets->sum('value');
        $data[] = ['GRAN TOTAL', '', '', number_format($grandTotal, 2)];

        return Excel::download(new \App\Exports\GenericReportExport(
            ['SICOIN', 'Descripción', 'Categoría', 'Valor (Q)'],
            $data
        ), 'bienes_por_funcionario.xlsx');
    }

    public function exportDisponiblesPdf(Request $request)
    {
        $assets = Asset::where('state', 'DISPONIBLE')->get();
        $totalAssets = $assets->count();
        $totalValue = $assets->sum('value');
        $filters = ['Tipo de Reporte' => 'Bienes Disponibles (No Asignados)'];

        $pdf = Pdf::loadView('pdf.report_disponibles', compact('assets', 'totalAssets', 'totalValue', 'filters'));
        return response()->streamDownload(fn () => print($pdf->stream()), 'bienes_disponibles.pdf');
    }

    public function exportDisponiblesExcel(Request $request)
    {
        $assets = Asset::where('state', 'DISPONIBLE')->get()->map(function ($asset) {
            return [
                'SICOIN'      => $asset->sicoin,
                'Descripción' => $asset->description,
                'Categoría'   => $asset->category,
                'Valor (Q)'   => number_format($asset->value, 2),
                'Libro'       => $asset->inventory_book,
                'Folio'       => $asset->folio_number,
            ];
        })->toArray();

        return Excel::download(new \App\Exports\GenericReportExport(
            ['SICOIN', 'Descripción', 'Categoría', 'Valor (Q)', 'Libro', 'Folio'],
            $assets
        ), 'bienes_disponibles.xlsx');
    }
}
