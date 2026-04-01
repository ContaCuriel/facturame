<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura</title>
    <style>
        @page { margin: 25px; }
        body { font-family: Arial, sans-serif; font-size: 9pt; color: #333; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { padding: 5px; vertical-align: top; }
        .company-details { width: 60%; }
        .invoice-details { width: 40%; text-align: right; }
        .logo { max-width: 150px; max-height: 80px; margin-bottom: 10px; }
        .section-title { background-color: #f2f2f2; font-weight: bold; padding: 5px; margin-top: 15px; font-size: 10pt; }
        .details p { margin: 5px 0; }
        .concepts-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        .concepts-table th, .concepts-table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .concepts-table th { background-color: #e9e9e9; }
        .totals-inner-table { width: 100%; border-collapse: collapse; }
        .totals-inner-table td { padding: 5px; }
        .totals-inner-table .label { font-weight: bold; text-align: right; }
        .seals { font-size: 7pt; }
        .seal-block { width: 100%; margin-top: 10px; word-wrap: break-word; word-break: break-all; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td class="company-details">
                    @if(isset($logo_base64))
                        <img src="{{ $logo_base64 }}" class="logo" alt="Logo">
                    @endif
                    <p>
                        <strong>{{ $emisor_nombre ?? 'N/A' }}</strong><br>
                        <strong>RFC:</strong> {{ $emisor_rfc ?? 'N/A' }}<br>
                        <strong>Régimen Fiscal:</strong> {{ $regimen_fiscal_emisor ?? 'N/A' }}<br>
                        <strong>Lugar de Expedición:</strong> {{ $lugar_expedicion ?? 'N/A' }}
                    </p>
                </td>
                <td class="invoice-details">
                    <h2>FACTURA</h2>
                    <p>
                        <strong>Folio Fiscal (UUID):</strong><br>{{ $folio_fiscal ?? 'N/A' }}<br><br>
                        <strong>Uso de CFDI:</strong> {{ $uso_cfdi ?? 'N/A' }}<br>
                        <strong>Forma de Pago:</strong> {{ $forma_pago ?? 'N/A' }}<br>
                        <strong>Método de Pago:</strong> {{ $metodo_pago ?? 'N/A' }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Datos del Receptor</div>
    <div class="details">
        <p>
            <strong>Nombre:</strong> {{ $receptor_nombre ?? 'N/A' }}<br>
            <strong>RFC:</strong> {{ $receptor_rfc ?? 'N/A' }}<br>
            <strong>Código Postal:</strong> {{ $receptor_cp ?? 'N/A' }}
        </p>
    </div>

    <div class="section-title">Conceptos</div>
    <table class="concepts-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Concepto</th>
                <th style="width:10%;">Cantidad</th>
                <th style="width:15%;">Precio U.</th>
                <th style="width:15%;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conceptos as $concepto)
                <tr>
                    <td>{{ $concepto['producto'] }}</td>
                    <td>{!! $concepto['concepto'] !!}</td>
                    <td>{{ $concepto['cantidad'] }}</td>
                    <td>{{ $concepto['precio_u'] }}</td>
                    <td>{{ $concepto['importe'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No se encontraron conceptos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 15px; page-break-inside: avoid;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                @if(isset($complemento_educativo) && !empty($complemento_educativo['nombre_alumno']))
                    <div class="section-title">Complemento de Instituciones Educativas Privadas</div>
                    <div class="details">
                        <p>
                            <strong>Nombre del Alumno:</strong> {{ $complemento_educativo['nombre_alumno'] }}<br>
                            <strong>CURP:</strong> {{ $complemento_educativo['curp'] }}<br>
                            <strong>Nivel Educativo:</strong> {{ $complemento_educativo['nivel'] }}<br>
                            <strong>Clave de Autorización:</strong> {{ $complemento_educativo['aut'] }}
                        </p>
                    </div>
                @endif
            </td>
            <td style="width: 40%; vertical-align: top;">
                <table class="totals-inner-table">
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td>{{ $subtotal ?? '0.00' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total:</td>
                        <td>{{ $total ?? '0.00' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <div class="section-title" style="margin-top: 25px;">Sellos Digitales y QR</div>
    {{-- ✅ AJUSTE FINAL: Se añade "table-layout: fixed;" para forzar el ajuste del texto --}}
    <table style="width: 100%; margin-top: 10px; table-layout: fixed;">
        <tr>
            <td style="width: 25%; text-align: center; vertical-align: top;">
                @if(isset($qr_code_base64))
                    <img src="{{ $qr_code_base64 }}" style="width: 130px; height: auto;" alt="Código QR">
                @endif
            </td>
            <td style="width: 75%; vertical-align: top; padding-left: 15px;">
                <div class="seals">
                    <p><strong>Sello Digital del CFDI:</strong></p>
                    <div class="seal-block">{{ $sello_cfdi ?? 'N/A' }}</div>
                    <p><strong>Sello Digital del SAT:</strong></p>
                    <div class="seal-block">{{ $sello_sat ?? 'N/A' }}</div>
                    <p><strong>Cadena Original del Complemento de Certificación Digital del SAT:</strong></p>
                    <div class="seal-block">{{ $cadena_original ?? 'N/A' }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>