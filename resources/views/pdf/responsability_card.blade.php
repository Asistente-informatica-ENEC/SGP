<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tarjeta de Responsabilidad</title>
    <style>
        /* Adjusting margins to allow fixed header/footer (padding on top/bottom) */
        /* Left: 1.1cm, Right: 0.2cm */
        @page {
            margin: 220px 0.2cm 120px 1.1cm;
        }
        header {
            position: fixed;
            top: -210px;
            left: 0;
            right: 0;
            height: 200px;
        }
        footer {
            position: fixed;
            bottom: -112px;
            left: 0;
            right: 0;
            height: 112px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: top;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .header-table {
            border: none;
            margin-bottom: 5px;
        }
        .header-table td {
            border: none;
            padding: 2px;
            vertical-align: middle;
        }
        .info-table {
            border: none;
            margin-bottom: 5px;
        }
        .info-table td {
            border: none;
            padding: 2px 0;
            text-align: left;
            vertical-align: bottom;
        }
        .line-bottom {
            border-bottom: 1px solid #000 !important;
            text-align: center !important;
        }
        .title-header {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
        }
        .card-number {
            color: red;
            font-weight: bold;
            font-size: 16px;
            margin-right: 60px;
        }
        .small-text {
            font-size: 8px;
            text-align: justify;
            margin-top: 5px;
        }
        .signature-table {
            border: none;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            padding: 0;
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 25%; text-align: left;">
                    @if(file_exists(public_path('images/logost.png')))
                        <img src="{{ public_path('images/logost.png') }}" style="height: 100px;">
                    @endif
                </td>
                <td style="width: 50%;" class="title-header">
                    MINISTERIO DE SALUD PÚBLICA Y ASISTENCIA SOCIAL<br>
                    ESCUELA NACIONAL DE ENFERMERÍA DE COBÁN<br>
                    E INSTITUTO DE ADIESTRAMIENTO PARA PERSONAL DE SALUD DE LAS VERAPACES<br>
                    ACTIVOS FIJOS<br>
                    TARJETA DE RESPONSABILIDAD
                </td>
                <td style="width: 25%; text-align: right;">
                    @if(file_exists(public_path('images/logoc.png')))
                        <img src="{{ public_path('images/logoc.png') }}" style="height: 100px;">
                    @endif
                    <div class="card-number" style="margin-top: 10px;">No. {{ str_pad($card->assignment_code, 3, '0', STR_PAD_LEFT) }}</div>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td style="width: 15%;">NOMBRE COMPLETO</td>
                <td class="line-bottom" style="width: 45%;">{{ optional($card->civilServant)->name }}</td>
                <td style="width: 5%; text-align: right; padding-right: 5px;">NIT</td>
                <td class="line-bottom" style="width: 35%;">{{ optional($card->civilServant)->nit }}</td>
            </tr>
            <tr>
                <td>UNIDAD ADMINISTRATIVA</td>
                <td class="line-bottom">{{ optional($card->civilServant)->unit }}</td>
                <td style="text-align: right; padding-right: 5px;">CARGO</td>
                <td class="line-bottom">{{ optional(optional($card->civilServant)->position)->name }}</td>
            </tr>
        </table>
    </header>

    <main>
        <table class="main-table">
        <thead>
            <tr>
                <th style="width: 8%;">FECHA DE<br>OPERACIÓN</th>
                <th style="width: 12%;">No. DE<br>INVENTARIO DE<br>SICOIN</th>
                <th style="width: 7%;">CANTIDAD</th>
                <th style="width: 35%;">DESCRIPCIÓN</th>
                <th style="width: 9%;">DEBE</th>
                <th style="width: 9%;">HABER</th>
                <th style="width: 9%;">SALDO</th>
                <th style="width: 11%;">OBSERVACIÓN</th>
            </tr>
        </thead>
        <tbody>
            <!-- Fila VIENEN -->
            <tr>
                <td colspan="3" class="text-right" style="font-weight: bold; border-right: none;">V I E N E N ................................</td>
                <td class="text-left" style="font-weight: bold; border-left: none;">{{ $vienen }}</td>
                <td class="text-right" style="font-weight: bold;">Q. {{ number_format($vienenAmount, 2, '.', ',') }}</td>
                <td></td>
                <td class="text-right" style="font-weight: bold;">Q. {{ number_format($vienenAmount, 2, '.', ',') }}</td>
                <td></td>
            </tr>

            @foreach($card->assignments as $assignment)
            @php 
                $asset = $assignment->asset;
                $isDescargo = $card->type === 'descargo';
            @endphp
            @if($asset)
            <tr>
                <td>{{ optional($assignment->date)->format('d/m/Y') }}</td>
                <td>{{ $asset->sicoin }}</td>
                <td>1</td>
                <td class="text-left" style="white-space: pre-wrap;">{{ $asset->description }}</td>
                <td>{{ !$isDescargo ? number_format((float)$asset->value, 2, '.', '') : '' }}</td>
                <td>{{ $isDescargo ? number_format((float)$asset->value, 2, '.', '') : '' }}</td>
                <td>{{ number_format((float)$asset->value, 2, '.', '') }}</td>
                <td>{{ $assignment->observation }}</td>
            </tr>
            @endif
            @endforeach

            <!-- Fila VAN -->
            <tr>
                <td colspan="3" class="text-right" style="font-weight: bold; border-right: none;">V A N ................................</td>
                <td class="text-left" style="font-weight: bold; border-left: none;">{{ $van }}</td>
                <td class="text-right" style="font-weight: bold;">Q. {{ number_format($vanAmount, 2, '.', ',') }}</td>
                <td></td>
                <td class="text-right" style="font-weight: bold;">Q. {{ number_format($vanAmount, 2, '.', ',') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    </main>

    <footer>
        <div class="small-text">
            Autorizado por la Contraloría General de Cuentas según Resolución número F.O.-MC 205-2025 002500 Gestión 1065478 del 12/06/2025, Libro No. 65403 del 24/08/2016 folio 81 correlativo 55-AV-2025 del 20/08/2025. Forma "Activos Fijos Tarjetas de Responsabilidad" Electrónica Original. Original: Para la Entidad, cantidad 300 unidades del 301 al 600 sin serie, Envío Fiscal Especie: 4-A1-CCC Serie "A" No. 15190 del 20/08/2025 No. De Cuentadante: 2022-1500-1601-18-002
        </div>

        <table class="signature-table">
            <tr>
                <td style="width: 40%; text-align: center;">
                    <div style="margin-bottom: 2px;">f. ________________________________________</div>
                    <div>{{ auth()->user()->name }}</div>
                    <div>Encargado(a) de la Unidad de Activos</div>
                </td>
                <td style="width: 20%;"></td>
                <td style="width: 40%; text-align: center;">
                    <div style="margin-bottom: 2px;">f. ________________________________________</div>
                    <div>{{ optional($card->civilServant)->name }}</div>
                    <div>{{ optional(optional($card->civilServant)->position)->name }}</div>
                </td>
            </tr>
        </table>
    </footer>
</body>
</html>
