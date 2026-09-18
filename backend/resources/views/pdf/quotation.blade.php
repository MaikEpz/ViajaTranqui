<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Póliza ViajaTranqui #VTQ-{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 28px 32px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #111111;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        
        /* Cabecera Principal */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-left {
            vertical-align: middle;
            text-align: left;
        }
        .header-right {
            vertical-align: middle;
            text-align: right;
        }
        .logo-mark {
            vertical-align: middle;
            margin-right: 8px;
            border-radius: 6px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 800;
            color: #111111;
            letter-spacing: -0.5px;
            display: inline-block;
            vertical-align: middle;
        }
        .brand-subtitle {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #717171;
            margin-top: 4px;
        }
        
        .folio-number {
            font-size: 13px;
            font-weight: 800;
            font-family: monospace;
            color: #111111;
        }
        .emission-date {
            font-size: 10px;
            color: #717171;
            margin-top: 2px;
        }
        
        /* Estado / Status Pill */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        .status-contracted {
            background-color: #111111;
            color: #ffffff;
            border: 1px solid #111111;
        }
        .status-quoted {
            background-color: #f4f4f5;
            color: #111111;
            border: 1px solid #d4d4d8;
        }
        
        .divider-solid {
            border-top: 1.5px solid #111111;
            margin: 12px 0 18px 0;
        }

        /* Bloques de Información (2 Columnas) */
        .two-col-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-left: -12px;
            margin-right: -12px;
            margin-bottom: 16px;
        }
        .card-box {
            background-color: #fafafa;
            border: 1px solid #e4e4e7;
            border-radius: 8px;
            padding: 12px 14px;
            vertical-align: top;
            width: 50%;
        }
        .card-header-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #717171;
            padding-bottom: 6px;
            border-bottom: 1px solid #e4e4e7;
            margin-bottom: 8px;
        }
        
        .field-row {
            margin-bottom: 6px;
        }
        .field-row:last-child {
            margin-bottom: 0;
        }
        .field-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #717171;
            display: block;
        }
        .field-value {
            font-size: 11px;
            font-weight: 600;
            color: #111111;
        }
        .flag-icon {
            vertical-align: middle;
            margin-right: 4px;
            border-radius: 2px;
        }

        /* Tabla de Liquidación Actuarial */
        .section-header {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #111111;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .pricing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .pricing-table th {
            background-color: #111111;
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 10px;
            text-align: left;
            border: 1px solid #111111;
        }
        .pricing-table td {
            padding: 8px 10px;
            border: 1px solid #e4e4e7;
            font-size: 10.5px;
        }
        .pricing-table .text-right {
            text-align: right;
        }
        .pricing-table .concept-title {
            font-weight: 600;
            color: #111111;
        }
        .pricing-table .concept-sub {
            font-size: 9px;
            color: #717171;
        }
        .total-row {
            background-color: #f4f4f5;
        }
        .total-row td {
            border-top: 2px solid #111111;
            font-weight: 800;
            font-size: 12px;
            color: #111111;
            padding: 10px;
        }
        .total-amount-val {
            font-size: 15px;
            font-weight: 800;
            color: #111111;
        }

        /* Cláusula de Validez Consular & Garantías */
        .notice-box {
            border: 1px solid #111111;
            border-radius: 6px;
            padding: 10px 14px;
            background-color: #ffffff;
            margin-bottom: 16px;
        }
        .notice-title {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111111;
            margin-bottom: 4px;
        }
        .notice-desc {
            font-size: 9.5px;
            color: #333333;
            line-height: 1.4;
        }

        /* Footer Oficial */
        .page-footer {
            border-top: 1px solid #e4e4e7;
            padding-top: 10px;
            text-align: center;
            font-size: 8.5px;
            color: #717171;
            margin-top: 20px;
        }
        .page-footer-bold {
            font-weight: 700;
            color: #111111;
        }
    </style>
</head>
<body>

    <!-- CABECERA PRINCIPAL MINIMALISTA -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" width="34" height="34" class="logo-mark" alt="Logo">
                @endif
                <div class="brand-title">ViajaTranqui</div>
                <div class="brand-subtitle">Certificado Oficial de Seguro de Asistencia en Viaje</div>
            </td>
            <td class="header-right">
                <div class="folio-number">VTQ-{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="emission-date">Emisión: {{ $quotation->created_at->format('d/m/Y H:i') }}</div>
                <div>
                    <span class="status-badge {{ $quotation->status === 'Contratado' ? 'status-contracted' : 'status-quoted' }}">
                        {{ $quotation->status === 'Contratado' ? 'Póliza Contratada' : 'Cotización Informativa' }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider-solid"></div>

    <!-- DOS COLUMNAS: DATOS DEL ASEGURADO E ITINERARIO -->
    <table class="two-col-table">
        <tr>
            <!-- Columna 1: Asegurado -->
            <td class="card-box">
                <div class="card-header-title">1. Datos del Asegurado</div>
                
                <div class="field-row">
                    <span class="field-label">Nombre Completo</span>
                    <span class="field-value">{{ $quotation->first_name }} {{ $quotation->last_name }}</span>
                </div>

                <div class="field-row">
                    <span class="field-label">Documento de Identificación / DNI / Pasaporte</span>
                    <span class="field-value">{{ $quotation->identification_number }}</span>
                </div>

                <div class="field-row">
                    <span class="field-label">Fecha de Nacimiento</span>
                    <span class="field-value">{{ \Carbon\Carbon::parse($quotation->birth_date)->format('d/m/Y') }}</span>
                </div>

                <div class="field-row">
                    <span class="field-label">Correo Electrónico</span>
                    <span class="field-value">{{ $quotation->email }}</span>
                </div>
            </td>

            <!-- Columna 2: Itinerario -->
            <td class="card-box">
                <div class="card-header-title">2. Itinerario y Vigencia</div>

                <div class="field-row">
                    <span class="field-label">País de Destino</span>
                    <span class="field-value">
                        @if(!empty($flagBase64))
                            <img src="{{ $flagBase64 }}" width="16" height="11" class="flag-icon" alt="Bandera">
                        @endif
                        {{ $quotation->destination_country }} ({{ $quotation->destination_country_code }})
                    </span>
                </div>

                <div class="field-row">
                    <span class="field-label">Región Continental</span>
                    <span class="field-value">{{ $quotation->destination_region }}</span>
                </div>

                <div class="field-row">
                    <span class="field-label">Periodo de Cobertura</span>
                    <span class="field-value">
                        {{ \Carbon\Carbon::parse($quotation->start_date)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($quotation->end_date)->format('d/m/Y') }}
                    </span>
                </div>

                <div class="field-row">
                    <span class="field-label">Duración Efectiva</span>
                    <span class="field-value">{{ $quotation->days_count }} días continuos</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- SECCIÓN 3: DESGLOSE ECONÓMICO ACTUARIAL -->
    <div class="section-header">3. Liquidación de Prima y Desglose Actuarial</div>
    <table class="pricing-table">
        <thead>
            <tr>
                <th style="width: 48%;">Concepto</th>
                <th style="width: 26%;" class="text-right">Detalle de Cálculo</th>
                <th style="width: 26%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="concept-title">Tarifa Base Internacional</div>
                    <div class="concept-sub">Cobertura diaria computada por estadía efectiva</div>
                </td>
                <td class="text-right">
                    {{ $quotation->days_count }} días × USD ${{ number_format($quotation->base_rate_per_day, 2) }}/día
                </td>
                <td class="text-right concept-title">
                    USD ${{ number_format($quotation->base_amount, 2) }}
                </td>
            </tr>
            <tr>
                <td>
                    <div class="concept-title">Recargo Geográfico ({{ $quotation->destination_region }})</div>
                    <div class="concept-sub">Matriz de riesgo actuarial continental</div>
                </td>
                <td class="text-right">
                    +{{ number_format($quotation->surcharge_percentage, 0) }}% sobre tarifa base
                </td>
                <td class="text-right concept-title">
                    +USD ${{ number_format($quotation->surcharge_amount, 2) }}
                </td>
            </tr>
            <tr class="total-row">
                <td colspan="2" class="text-right">PRIMA TOTAL LIQUIDADA:</td>
                <td class="text-right total-amount-val">
                    USD ${{ number_format($quotation->total_amount, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SECCIÓN 4: VALIDEZ CONSULAR Y CERTIFICACIÓN -->
    <div class="notice-box">
        <div class="notice-title">Validez Migratoria y Garantías Consulares</div>
        <div class="notice-desc">
            Este certificado acredita la contratación de asistencia médica de emergencia internacional hasta <strong>$50,000 USD</strong>, incluyendo gastos hospitalarios, medicamentos, repatriación sanitaria y funeraria 24/7 sin deducibles ni franquicias.
            @if($quotation->destination_region === 'Europa')
                Documento conforme a los requisitos del Reglamento (CE) N° 810/2009 del Parlamento Europeo y del Consejo (Código Comunitario sobre Visados Schengen).
            @endif
        </div>
    </div>

    @if($quotation->status === 'Contratado')
        <div style="font-size: 9px; color: #15803d; font-weight: 700; margin-bottom: 8px;">
            ✓ Póliza confirmada y en vigencia. Confirmación registrada el {{ \Carbon\Carbon::parse($quotation->contracted_at ?? $quotation->updated_at)->format('d/m/Y H:i') }}.
        </div>
    @else
        <div style="font-size: 9px; color: #717171; margin-bottom: 8px;">
            * Documento informativo de cotización previo a la contratación final. Tarifa garantizada por 15 días continuos.
        </div>
    @endif

    <!-- FOOTER OFICIAL -->
    <div class="page-footer">
        <span class="page-footer-bold">ViajaTranqui</span> • Cobertura Médica y Asistencia Internacional 24/7 • Documento expedido electrónicamente con validez legal • {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>
