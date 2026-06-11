<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pre-Factura - {{ $company->rfc }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        /* Marca de agua diagonal */
        .watermark {
            position: fixed; top: 35%; left: 10%; font-size: 70px; 
            color: rgba(255, 0, 0, 0.15); transform: rotate(-45deg); 
            z-index: -1; text-align: center; width: 100%; letter-spacing: 5px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; vertical-align: top; }
        th { background-color: #f8fafc; font-weight: bold; }
        .no-border td { border: none; padding: 4px 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header-table { margin-bottom: 30px; }
        .header-table td { border: none; padding: 0; }
        .totals-table { width: 40%; float: right; }
    </style>
</head>
<body>

    <div class="watermark">
        BORRADOR<br>SIN VALIDEZ FISCAL
    </div>

    <table class="header-table">
        <tr>
            <td width="60%">
                <h2 style="margin: 0; color: #1e293b;">{{ $company->name }}</h2>
                <p style="margin: 5px 0 0 0;"><strong>RFC:</strong> {{ $company->rfc }}</p>
                <p style="margin: 5px 0 0 0;"><strong>Régimen:</strong> {{ $company->fiscal_regime }}</p>
                <p style="margin: 5px 0 0 0;"><strong>C.P. Emisión:</strong> {{ $company->zip_code }}</p>
            </td>
            <td width="40%" class="text-right">
                <h1 style="margin: 0; color: #64748b;">PRE-FACTURA</h1>
                <p style="margin: 5px 0 0 0;"><strong>Folio:</strong> F-{{ $facturamaData['Folio'] }}</p>
                <p style="margin: 5px 0 0 0;"><strong>Fecha:</strong> {{ date('Y-m-d H:i') }}</p>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td width="50%">
                <strong>RECEPTOR:</strong><br>
                {{ $client->name }}<br>
                RFC: {{ $client->rfc }}<br>
                C.P.: {{ $client->zip_code }}<br>
                Régimen: {{ $client->fiscal_regime }}
            </td>
            <td width="50%">
                <strong>DATOS DEL CFDI:</strong><br>
                Uso de CFDI: {{ $facturamaData['Receiver']['CfdiUse'] }}<br>
                Forma de Pago: {{ $facturamaData['PaymentForm'] }}<br>
                Método de Pago: {{ $facturamaData['PaymentMethod'] }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Clave SAT</th>
                <th>Cant</th>
                <th>Unidad</th>
                <th>Descripción</th>
                <th class="text-right">Precio U.</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facturamaData['Items'] as $item)
            <tr>
                <td>{{ $item['ProductCode'] }}</td>
                <td class="text-center">{{ $item['Quantity'] }}</td>
                <td>{{ $item['UnitCode'] }}</td>
                <td>{{ $item['Description'] }}</td>
                <td class="text-right">${{ number_format($item['UnitPrice'], 2) }}</td>
                <td class="text-right">${{ number_format($item['Subtotal'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table no-border">
        <tr>
            <td class="text-right"><strong>Subtotal:</strong></td>
            <td class="text-right">${{ number_format($totals['subtotal'], 2) }}</td>
        </tr>
        @if($totals['total_traslados'] > 0)
        <tr>
            <td class="text-right"><strong>Impuestos Trasladados:</strong></td>
            <td class="text-right">${{ number_format($totals['total_traslados'], 2) }}</td>
        </tr>
        @endif
        @if($totals['total_retenciones'] > 0)
        <tr>
            <td class="text-right"><strong>Impuestos Retenidos:</strong></td>
            <td class="text-right">-${{ number_format($totals['total_retenciones'], 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-right" style="font-size: 14px; border-top: 1px solid #333; padding-top: 5px;"><strong>TOTAL:</strong></td>
            <td class="text-right" style="font-size: 14px; border-top: 1px solid #333; padding-top: 5px;"><strong>${{ number_format($totals['total'], 2) }}</strong></td>
        </tr>
    </table>

    <div style="clear: both; margin-top: 50px; text-align: center; color: #94a3b8;">
        <p>Este documento es una representación impresa de un BORRADOR y no tiene validez fiscal.</p>
    </div>

</body>
</html>