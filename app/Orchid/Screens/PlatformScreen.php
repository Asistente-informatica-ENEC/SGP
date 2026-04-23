<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;

class PlatformScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // 1. Chart Data: Tarjetas de Responsabilidad (Últimos 7 días)
        $cardsByDay = \App\Models\ResponsabilityCard::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date');

        $chartLabels = [];
        $chartValues = [];

        // Asegurar que haya 7 días incluso si no hay registros
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartValues[] = $cardsByDay->get($date, 0);
        }

        $cardsChart = [
            [
                'name' => 'Tarjetas Emitidas',
                'values' => $chartValues,
                'labels' => $chartLabels,
            ]
        ];

        // 2. Pie Data: Estado de los Bienes
        $assignedAssets = \App\Models\Asset::where('state', 'ASIGNADO')->count();
        $availableAssets = \App\Models\Asset::where('state', 'DISPONIBLE')->count();
        // Incluimos otros estados si existen (ej. DE BAJA), si no, sólo los 2 principales
        $otherAssets = \App\Models\Asset::whereNotIn('state', ['ASIGNADO', 'DISPONIBLE'])->count();

        $assetStateChart = [
            [
                'name' => 'Estados',
                'values' => [$assignedAssets, $availableAssets, $otherAssets],
                'labels' => ['Asignados', 'Disponibles', 'Otros (Baja/Mantenimiento)'],
            ]
        ];

        return [
            'metrics' => [
                'Total Tarjetas' => 'metrics.cards',
                'Total Funcionarios' => 'metrics.servants',
                'Bienes Asignados' => 'metrics.assigned',
                'Bienes Disponibles' => 'metrics.available',
            ],
            'metrics.cards' => \App\Models\ResponsabilityCard::count(),
            'metrics.servants' => \App\Models\CivilServant::count(),
            'metrics.assigned' => $assignedAssets,
            'metrics.available' => $availableAssets,

            'cardsChart' => $cardsChart,
            'assetStateChart' => $assetStateChart,

            'latestCards' => \App\Models\ResponsabilityCard::with('civilServant')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Panel de Control';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Resumen estadístico y últimos movimientos del sistema de inventarios.';
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

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Tarjetas Emitidas' => 'metrics.cards',
                'Funcionarios' => 'metrics.servants',
                'Bienes Asignados' => 'metrics.assigned',
                'Bienes Disponibles' => 'metrics.available',
            ]),

            Layout::columns([
                \App\Orchid\Layouts\Dashboard\CardsChartLayout::class,
                \App\Orchid\Layouts\Dashboard\AssetStatePieLayout::class,
            ]),

            \App\Orchid\Layouts\Dashboard\LatestCardsLayout::class,
        ];
    }

    // Métodos para permitir la descarga directa desde el Dashboard
    public function exportPdf(Request $request)
    {
        $card = \App\Models\ResponsabilityCard::findOrFail($request->input('card'));
        $assignments = $card->assignments()->with('asset')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.responsability_card', [
            'card' => $card,
            'assignments' => $assignments,
        ])->setPaper('letter', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'tarjeta_responsabilidad_' . $card->assignment_code . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $card = \App\Models\ResponsabilityCard::findOrFail($request->input('card'));
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ResponsabilityCardExport($card), 'tarjeta_responsabilidad_' . $card->assignment_code . '.xlsx');
    }
}
