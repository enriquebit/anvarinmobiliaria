<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago - <?= $pago->folio_pago ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            margin: 0;
            padding: 5px;
            background: #fff;
        }
        .comprobante-container {
            width: 60mm;
            max-width: 60mm;
            margin: 0;
            border: none;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #333;
            padding-bottom: 5px;
        }
        .empresa-info {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .folio {
            font-size: 8px;
            font-weight: bold;
            color: #000;
            margin-top: 3px;
        }
        .info-section {
            margin-bottom: 8px;
        }
        .info-section h3 {
            font-size: 8px;
            margin: 5px 0 3px 0;
            color: #333;
            text-align: center;
            text-decoration: underline;
        }
        .info-row {
            display: block;
            margin-bottom: 2px;
            font-size: 8px;
        }
        .info-label {
            font-weight: bold;
            display: inline;
        }
        .info-value {
            display: inline;
            word-wrap: break-word;
        }
        .amount-section {
            background: none;
            border: 1px dashed #333;
            padding: 5px;
            margin: 8px 0;
            text-align: center;
        }
        .amount {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
        }
        .footer {
            margin-top: 8px;
            border-top: 1px dashed #333;
            padding-top: 5px;
            text-align: center;
            font-size: 6px;
            color: #666;
        }
        .legal-notice {
            background: none;
            border: none;
            padding: 3px;
            margin: 5px 0;
            text-align: center;
            font-weight: bold;
            color: #000;
            font-size: 6px;
        }
        @media print {
            body { 
                padding: 0; 
                font-size: 8px;
            }
            .comprobante-container { 
                border: none;
                width: 60mm;
                max-width: 60mm;
            }
        }
    </style>
</head>
<body>
    <div class="comprobante-container">
        <!-- Header -->
        <div class="header">
            <div class="empresa-info">
                <?= strtoupper($pago->empresa_razon_social ?? 'INMOBILIARIA ANVAR') ?>
            </div>
            <div><?= $pago->empresa_nombre ?? 'ANVAR DESARROLLOS INMOBILIARIOS' ?></div>
            <div class="folio">COMPROBANTE DE PAGO: <?= $pago->folio_pago ?></div>
        </div>

        <!-- Información del Cliente -->
        <div class="info-section">
            <h3 style="margin-bottom: 15px; color: #333;">DATOS DEL CLIENTE</h3>
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value"><?= $cliente_nombre ?></span>
            </div>
        </div>

        <!-- Información del Contrato -->
        <div class="info-section">
            <h3 style="margin-bottom: 15px; color: #333;">DATOS DEL CONTRATO</h3>
            <div class="info-row">
                <span class="info-label">Folio Venta:</span>
                <span class="info-value"><?= $pago->folio_venta ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Proyecto:</span>
                <span class="info-value"><?= $pago->proyecto_nombre ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Manzana:</span>
                <span class="info-value"><?= $pago->manzana_nombre ?? 'N/A' ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Lote:</span>
                <span class="info-value">#<?= $pago->lote_numero ?> (<?= $pago->lote_superficie ?> m²)</span>
            </div>
        </div>

        <!-- Información del Pago -->
        <div class="info-section">
            <h3 style="margin-bottom: 15px; color: #333;">DETALLE DEL PAGO</h3>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value"><?= date('d/m/Y H:i', strtotime($pago->fecha_pago)) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Concepto:</span>
                <span class="info-value"><?= strtoupper(str_replace('_', ' ', $pago->concepto_pago)) ?></span>
            </div>
            <?php if($pago->numero_mensualidad): ?>
            <div class="info-row">
                <span class="info-label">Mensualidad:</span>
                <span class="info-value"># <?= $pago->numero_mensualidad ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Forma de Pago:</span>
                <span class="info-value"><?= strtoupper($pago->forma_pago) ?></span>
            </div>
            <?php if($pago->referencia_pago): ?>
            <div class="info-row">
                <span class="info-label">Referencia:</span>
                <span class="info-value"><?= $pago->referencia_pago ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value"><?= strtoupper($pago->estatus_pago) ?></span>
            </div>
        </div>

        <!-- Monto del Pago -->
        <div class="amount-section">
            <div class="amount">$<?= number_format($pago->monto_pago, 2) ?></div>
            <div>(<?= strtoupper(num_to_words($pago->monto_pago)) ?> PESOS)</div>
        </div>

        <!-- Aviso Legal -->
        <div class="legal-notice">
            IMPORTANTE: Este documento no es un comprobante fiscal válido para efectos del SAT
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Documento generado el <?= $fecha_actual ?></p>
            <p>Este comprobante es únicamente para control interno</p>
            <p>Para dudas o aclaraciones contacte a su asesor de ventas</p>
        </div>
    </div>

    <script>
        // Auto-imprimir cuando se abre en nueva ventana
        window.onload = function() {
            if (window.opener) {
                window.print();
            }
        }
    </script>
</body>
</html>