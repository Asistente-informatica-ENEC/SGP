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
}
