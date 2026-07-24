<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumen de Bienes</title>
    <style>
        @page { margin: 100px 30px; }
        header { position: fixed; top: -70px; left: 0px; right: 0px; height: 60px; text-align: center; border-bottom: 1px solid #eee; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 30px; text-align: center; font-size: 10px; color: #777; }
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .filters { margin-bottom: 15px; padding: 10px; background: #fcfcfc; border: 1px solid #efefef; }
        .signature-section { margin-top: 60px; text-align: center; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #000; width: 280px; margin: 0 auto; padding-top: 8px; font-weight: bold; }
        .text-right { text-align: right; }
        .total-row { background-color: #e9ecef; font-weight: bold; }
        h3 { color: #2c3e50; margin-top: 25px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <header>
        <h2 style="margin: 0; color: #2c3e50;">Resumen General de Bienes</h2>
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

        <h3>Totales Generales</h3>
        <table>
            <tr><td><strong>Total de Bienes</strong></td><td>{{ $totalAssets }}</td></tr>
            <tr><td><strong>Valor Total Estimado</strong></td><td>Q {{ number_format($totalValue, 2) }}</td></tr>
        </table>

        <h3>Por Estado</h3>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @php $stateTotal = 0; @endphp
                @forelse($byState as $row)
                    @php $stateTotal += $row->total_value; @endphp
                    <tr>
                        <td>{{ $row->state }}</td>
                        <td class="text-right">{{ $row->count }}</td>
                        <td class="text-right">Q {{ number_format($row->total_value, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">No hay bienes registrados.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="text-right"><strong>TOTAL</strong></td>
                    <td class="text-right">{{ $byState->sum('count') }}</td>
                    <td class="text-right">Q {{ number_format($stateTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <h3>Por Categoría</h3>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @php $catTotal = 0; @endphp
                @forelse($byCategory as $row)
                    @php $catTotal += $row->total_value; @endphp
                    <tr>
                        <td>{{ $row->category }}</td>
                        <td class="text-right">{{ $row->count }}</td>
                        <td class="text-right">Q {{ number_format($row->total_value, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">No hay bienes registrados.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="text-right"><strong>TOTAL</strong></td>
                    <td class="text-right">{{ $byCategory->sum('count') }}</td>
                    <td class="text-right">Q {{ number_format($catTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="signature-section">
            <div style="height: 80px;"></div>
            <div class="signature-line">
                Encargado/a de Activos Fijos
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
