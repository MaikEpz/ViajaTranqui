<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #VTQ-{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 24px;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-title {
            font-size: 22px;
            font-weight: bold;
            color: #0369a1;
            margin: 0;
        }
        .logo-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }
        .quote-meta {
            text-align: right;
        }
        .quote-number {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .badge-quoted {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .badge-contracted {
            background-color: #dcfce7;
            color: #15803d;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-top: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .info-table td {
            padding: 5px 6px;
            vertical-align: top;
        }
        .label {
            color: #64748b;
            font-weight: 600;
            width: 35%;
        }
        .value {
            color: #0f172a;
            font-weight: 500;
        }
        .pricing-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .pricing-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
        }
        .pricing-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 13px;
        }
        .total-amount {
            color: #0284c7;
            font-size: 15px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            color: #94a3b8;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="logo-title">✈️ ViajaTranqui</div>
                    <div class="logo-subtitle">Cotización y Cobertura de Seguro de Viaje Internacional</div>
                </td>
                <td class="quote-meta">
                    <div class="quote-number">Cotización #VTQ-{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}</div>
                    <div style="margin-top: 4px; color: #64748b;">Fecha de emisión: {{ $quotation->created_at->format('d/m/Y H:i') }}</div>
                    <div style="margin-top: 6px;">
                        <span class="badge {{ $quotation->status === 'Contratado' ? 'badge-contracted' : 'badge-quoted' }}">
                            {{ $quotation->status }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Asegurado -->
    <div class="section-title">1. Datos del Asegurado</div>
    <table class="info-table">
        <tr>
            <td class="label">Nombres y Apellidos:</td>
            <td class="value">{{ $quotation->first_name }} {{ $quotation->last_name }}</td>
            <td class="label">N° de Identificación / DNI:</td>
            <td class="value">{{ $quotation->identification_number }}</td>
        </tr>
        <tr>
            <td class="label">Correo Electrónico:</td>
            <td class="value">{{ $quotation->email }}</td>
            <td class="label">Fecha de Nacimiento:</td>
            <td class="value">{{ \Carbon\Carbon::parse($quotation->birth_date)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <!-- Viaje -->
    <div class="section-title">2. Datos del Viaje y Destino</div>
    <table class="info-table">
        <tr>
            <td class="label">País de Destino:</td>
            <td class="value">{{ $quotation->destination_country }} ({{ $quotation->destination_country_code }})</td>
            <td class="label">Región Continental:</td>
            <td class="value">{{ $quotation->destination_region }}</td>
        </tr>
        <tr>
            <td class="label">Fecha de Salida:</td>
            <td class="value">{{ \Carbon\Carbon::parse($quotation->start_date)->format('d/m/Y') }}</td>
            <td class="label">Fecha de Retorno:</td>
            <td class="value">{{ \Carbon\Carbon::parse($quotation->end_date)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Total de Días Cubiertos:</td>
            <td class="value" colspan="3"><strong>{{ $quotation->days_count }} días</strong></td>
        </tr>
    </table>

    <!-- Desglose Económico -->
    <div class="section-title">3. Desglose de Cotización</div>
    <table class="pricing-table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="text-right">Detalle</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tarifa Base de Seguro</td>
                <td class="text-right">{{ $quotation->days_count }} días × USD ${{ number_format($quotation->base_rate_per_day, 2) }}</td>
                <td class="text-right">USD ${{ number_format($quotation->base_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Recargo por Región ({{ $quotation->destination_region }})</td>
                <td class="text-right">{{ number_format($quotation->surcharge_percentage, 0) }}% sobre tarifa base</td>
                <td class="text-right">USD ${{ number_format($quotation->surcharge_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" class="text-right">VALOR TOTAL:</td>
                <td class="text-right total-amount">USD ${{ number_format($quotation->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($quotation->status === 'Contratado')
        <div style="padding: 10px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; margin-top: 10px;">
            <strong style="color: #166534;">✓ Póliza Contratada</strong><br>
            <span style="color: #15803d; font-size: 11px;">
                Esta póliza se encuentra oficialmente confirmada y en vigencia a partir del {{ \Carbon\Carbon::parse($quotation->contracted_at ?? $quotation->updated_at)->format('d/m/Y H:i') }}.
            </span>
        </div>
    @else
        <div style="padding: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; margin-top: 10px;">
            <strong style="color: #475569;">ℹ Cotización Informativa</strong><br>
            <span style="color: #64748b; font-size: 11px;">
                Esta cotización es válida por 15 días a partir de su emisión. Puede confirmar la contratación en línea a través de nuestro portal web.
            </span>
        </div>
    @endif

    <div class="footer">
        ViajaTranqui Seguros S.A. | Cobertura médica internacional 24/7 | Documento generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>
