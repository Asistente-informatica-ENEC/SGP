<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Movimientos</title>
    <style>
        @page { margin: 100px 30px; }
        header { position: fixed; top: -70px; left: 0px; right: 0px; height: 60px; text-align: center; border-bottom: 1px solid #eee; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 30px; text-align: center; font-size: 10px; color: #777; }
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .filters { margin-bottom: 15px; padding: 10px; background: #fcfcfc; border: 1px solid #efefef; }
        .signature-section { margin-top: 60px; text-align: center; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #000; width: 280px; margin: 0 auto; padding-top: 8px; font-weight: bold; }
        .text-center { text-align: center; }
        .badge { padding: 3px 6px; border-radius: 3px; color: white; font-weight: bold; font-size: 9px; }
        .bg-success { background-color: #28a745; }
        .bg-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <header>
        <h2 style="margin: 0; color: #2c3e50;">Auditoría de Movimientos Mensuales</h2>
        <p style="margin: 5px 0 0 0; font-size: 11px;">Generado el: {{ now()->format('d-m-Y H:i') }}</p>
    </header>

    <footer>
        Sistema de Control Patrimonial - {{ date('Y') }}
    </footer>

    <main>
        @if(!empty(array_filter($filters)))
            <div class="filters">
                <strong>Filtros aplicados:</strong>
                @foreach($filters as $key => $value)
                    <br>• {{ $key }}: {{ $value }}
                @endforeach
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 10%;" class="text-center">Movimiento</th>
                    <th style="width: 15%;">Tarjeta No.</th>
                    <th style="width: 15%;">SICOIN</th>
                    <th style="width: 25%;">Funcionario</th>
                    <th style="width: 20%;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $assignment)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($assignment->date)->format('d-m-Y H:i') }}</td>
                        <td class="text-center">
                            @if(($assignment->responsabilityCard->type ?? '') === 'descargo')
                                <span class="badge bg-danger">Descargo</span>
                            @else
                                <span class="badge bg-success">Asignación</span>
                            @endif
                        </td>
                        <td>No. {{ $assignment->responsabilityCard->assignment_code ?? 'N/A' }}</td>
                        <td>{{ $assignment->asset->sicoin ?? 'N/A' }}</td>
                        <td>{{ $assignment->responsabilityCard->civilServant->name ?? 'N/A' }}</td>
                        <td>{{ $assignment->observation }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($assignments->count() === 0)
            <p style="text-align: center; margin-top: 50px;">No se registraron movimientos en el rango de fechas seleccionado.</p>
        @endif

        <div class="signature-section">
            <div style="height: 80px;"></div>
            <div class="signature-line">
                Auditoría / Control Interno
            </div>
        </div>
    </main>

    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
            $size = 9;
            $pageText = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $y = $pdf->get_height() - 35;
            $x = ($pdf->get_width() - $fontMetrics->get_text_width($pageText, $font, $size)) / 2;
            $pdf->page_text($x, $y, $pageText, $font, $size);
        }
    </script>
</body>
</html>
