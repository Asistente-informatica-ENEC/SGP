<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Bienes</title>
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
        .text-justify { text-align: justify; }
    </style>
</head>
<body>
    <header>
        <h2 style="margin: 0; color: #2c3e50;">Reporte de Inventario de Bienes</h2>
        <p style="margin: 5px 0 0 0; font-size: 11px;">Generado el: {{ now()->format('d-m-Y H:i') }}</p>
    </header>

    <footer>
        Sistema de Control Patrimonial - {{ date('Y') }}
    </footer>

    <main>
        @if(!empty(array_filter($filters)))
            <div class="filters">
                <strong>Filtros aplicados:</strong>
                @if(!empty($filters['search'])) <br>• Búsqueda: "{{ $filters['search'] }}" @endif
                @if(!empty($filters['state'])) <br>• Estado: {{ $filters['state'] }} @endif
                @if(!empty($filters['category'])) <br>• Categoría: {{ $filters['category'] }} @endif
                @if(!empty($filters['date'])) <br>• Fecha: {{ \Carbon\Carbon::parse($filters['date'])->format('d-m-Y') }} @endif
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">SICOIN</th>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 10%;">Valor</th>
                    <th style="width: 12%;">Estado</th>
                    <th style="width: 18%;">Categoría</th>
                    <th style="width: 10%;">Fecha Alta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $asset)
                    <tr>
                        <td>{{ $asset->sicoin }}</td>
                        <td class="text-justify">{{ $asset->description }}</td>
                        <td>Q {{ number_format($asset->value, 2) }}</td>
                        <td>{{ $asset->state }}</td>
                        <td>{{ $asset->category }}</td>
                        <td>{{ $asset->date ? \Carbon\Carbon::parse($asset->date)->format('d-m-Y') : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-section">
            <div style="height: 80px;"></div>
            <div class="signature-line">
                Encargado/a de la unidad de activos fijos
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
