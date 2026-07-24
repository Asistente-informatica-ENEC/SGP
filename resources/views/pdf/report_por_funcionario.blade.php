<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienes por Funcionario</title>
    <style>
        @page { margin: 100px 30px; }
        header { position: fixed; top: -70px; left: 0px; right: 0px; height: 60px; text-align: center; border-bottom: 1px solid #eee; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 30px; text-align: center; font-size: 10px; color: #777; }
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; font-size: 9px; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; text-align: center; }
        .filters { margin-bottom: 15px; padding: 10px; background: #fcfcfc; border: 1px solid #efefef; }
        .signature-section { margin-top: 60px; text-align: center; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #000; width: 280px; margin: 0 auto; padding-top: 8px; font-weight: bold; }
        .text-right { text-align: right; }
        .func-group { background-color: #f0f4f8; font-weight: bold; }
        .total-row { background-color: #e9ecef; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h2 style="margin: 0; color: #2c3e50;">Bienes por Funcionario</h2>
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

        @php $grandTotal = 0; @endphp
        @forelse($groups as $servantName => $servantAssets)
            @php
                $servantTotal = $servantAssets->sum('value');
                $grandTotal += $servantTotal;
            @endphp
            <table>
                <thead>
                    <tr class="func-group">
                        <th colspan="4" style="text-align: left; font-size: 11px;">
                            {{ $servantName }} — Total: Q {{ number_format($servantTotal, 2) }} ({{ $servantAssets->count() }} bienes)
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 10%;">SICOIN</th>
                        <th style="width: 35%;">Descripción</th>
                        <th style="width: 10%;">Categoría</th>
                        <th style="width: 10%;" class="text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servantAssets as $asset)
                        <tr>
                            <td>{{ $asset->sicoin }}</td>
                            <td>{{ $asset->description }}</td>
                            <td>{{ $asset->category }}</td>
                            <td class="text-right">Q {{ number_format($asset->value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p class="text-center">No hay bienes asignados a funcionarios.</p>
        @endforelse

        <table>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>GRAN TOTAL</strong></td>
                    <td class="text-right">Q {{ number_format($grandTotal, 2) }}</td>
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
