<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte por Departamento</title>
    <style>
        @page { margin: 100px 30px; }
        header { position: fixed; top: -70px; left: 0px; right: 0px; height: 60px; text-align: center; border-bottom: 1px solid #eee; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 30px; text-align: center; font-size: 10px; color: #777; }
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 25px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .filters { margin-bottom: 15px; padding: 10px; background: #fcfcfc; border: 1px solid #efefef; }
        .signature-section { margin-top: 60px; text-align: center; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #000; width: 280px; margin: 0 auto; padding-top: 8px; font-weight: bold; }
        .dept-title { font-size: 14px; font-weight: bold; margin-bottom: 5px; color: #2c3e50; border-bottom: 2px solid #2c3e50; display: inline-block; padding-bottom: 2px;}
    </style>
</head>
<body>
    <header>
        <h2 style="margin: 0; color: #2c3e50;">Inventario Físico por Departamento</h2>
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

        @foreach($departments as $unit => $assets)
            @if($assets->count() > 0)
                <div class="dept-title">{{ $unit }} ({{ $assets->count() }} Bienes)</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 12%;">SICOIN</th>
                            <th style="width: 48%;">Descripción</th>
                            <th style="width: 15%;">Categoría</th>
                            <th style="width: 15%;">Valor (Q)</th>
                            <th style="width: 10%;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $deptTotal = 0; @endphp
                        @foreach($assets as $asset)
                            @php $deptTotal += $asset->value; @endphp
                            <tr>
                                <td>{{ $asset->sicoin }}</td>
                                <td>{{ $asset->description }}</td>
                                <td>{{ $asset->category }}</td>
                                <td>Q {{ number_format($asset->value, 2) }}</td>
                                <td>{{ $asset->state }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: bold; background-color: #f8f9fa;">Subtotal Departamento:</td>
                            <td colspan="2" style="font-weight: bold; background-color: #f8f9fa;">Q {{ number_format($deptTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endforeach

        @if(count($departments) === 0 || collect($departments)->flatten()->count() === 0)
            <p style="text-align: center; margin-top: 50px;">No hay bienes asignados actualmente.</p>
        @endif

        <div class="signature-section">
            <div style="height: 80px;"></div>
            <div class="signature-line">
                Director/a Administrativo/a
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
