<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - <?= $pago->folio ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .recibo-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }
        .empresa-info {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .titulo-recibo {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 15px 0;
            text-transform: uppercase;
        }
        .folio-fecha {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            font-weight: bold;
        }
        .datos-cliente {
            margin: 20px 0;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .datos-cliente h4 {
            margin: 0 0 10px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            text-align: right;
        }
        .detalle-pago {
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .detalle-header {
            background: #f5f5f5;
            padding: 10px 15px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }
        .detalle-content {
            padding: 15px;
        }
        .monto-total {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
            padding: 15px;
            background: #ecf0f1;
            border-radius: 5px;
        }
        .observaciones {
            margin: 20px 0;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .recibo-container { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="recibo-container">
        <!-- Header -->
        <div class="header">
            <div class="empresa-info"><?= esc($empresa['nombre']) ?></div>
            <?php if (!empty($empresa['rfc'])): ?>
                <div>RFC: <?= esc($empresa['rfc']) ?></div>
            <?php endif; ?>
            <?php if (!empty($empresa['direccion'])): ?>
                <div><?= esc($empresa['direccion']) ?></div>
            <?php endif; ?>
            
            <div class="titulo-recibo">Recibo de Pago</div>
        </div>

        <!-- Folio y Fecha -->
        <div class="folio-fecha">
            <div>
                <span class="label">FOLIO:</span> 
                <span style="color: #e74c3c; font-size: 14px;"><?= esc($pago['folio']) ?></span>
            </div>
            <div>
                <span class="label">FECHA:</span> 
                <span><?= date('d/m/Y H:i', strtotime($pago['fecha'])) ?></span>
            </div>
        </div>

        <!-- Datos del Cliente -->
        <div class="datos-cliente">
            <h4>Datos del Cliente</h4>
            <div class="row">
                <span class="label">Nombre:</span>
                <span class="value"><?= esc(trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno)) ?></span>
            </div>
            <?php if (!empty($cliente->rfc)): ?>
            <div class="row">
                <span class="label">RFC:</span>
                <span class="value"><?= esc($cliente->rfc) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($cliente->email)): ?>
            <div class="row">
                <span class="label">Email:</span>
                <span class="value"><?= esc($cliente->email) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($cliente->telefono)): ?>
            <div class="row">
                <span class="label">Teléfono:</span>
                <span class="value"><?= esc($cliente->telefono) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Datos de la Venta -->
        <?php if ($venta): ?>
        <div class="datos-cliente">
            <h4>Datos de la Venta</h4>
            <div class="row">
                <span class="label">Folio de Venta:</span>
                <span class="value"><?= esc($venta['folio']) ?></span>
            </div>
            <div class="row">
                <span class="label">Fecha de Venta:</span>
                <span class="value"><?= date('d/m/Y', strtotime($venta['fecha'])) ?></span>
            </div>
            <div class="row">
                <span class="label">Precio Total:</span>
                <span class="value">$<?= number_format($venta['precio_final'], 2) ?></span>
            </div>
            <div class="row">
                <span class="label">Saldo Actual:</span>
                <span class="value">$<?= number_format($venta['saldo_actual'], 2) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Datos del Lote -->
        <?php if ($lote && !empty($lote['nombre'])): ?>
        <div class="datos-cliente">
            <h4>Datos del Lote</h4>
            <div class="row">
                <span class="label">Lote:</span>
                <span class="value"><?= esc($lote['nombre']) ?></span>
            </div>
            <?php if (!empty($lote['manzana'])): ?>
            <div class="row">
                <span class="label">Manzana:</span>
                <span class="value"><?= esc($lote['manzana']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($lote['superficie'])): ?>
            <div class="row">
                <span class="label">Superficie:</span>
                <span class="value"><?= esc($lote['superficie']) ?> m²</span>
            </div>
            <?php endif; ?>
            <?php if (!empty($proyecto['nombre'])): ?>
            <div class="row">
                <span class="label">Proyecto:</span>
                <span class="value"><?= esc($proyecto['nombre']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Detalle del Pago -->
        <div class="detalle-pago">
            <div class="detalle-header">Detalle del Pago</div>
            <div class="detalle-content">
                <div class="row">
                    <span class="label">Concepto:</span>
                    <span class="value"><?= esc($concepto) ?></span>
                </div>
                <div class="row">
                    <span class="label">Forma de Pago:</span>
                    <span class="value"><?= esc($pago['forma_pago']) ?></span>
                </div>
                <?php if (!empty($pago['referencia'])): ?>
                <div class="row">
                    <span class="label">Referencia:</span>
                    <span class="value"><?= esc($pago['referencia']) ?></span>
                </div>
                <?php endif; ?>
                <div class="row">
                    <span class="label">Tipo de Pago:</span>
                    <span class="value"><?= ucfirst(esc($pago['tipo_pago'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Monto Total -->
        <div class="monto-total">
            MONTO RECIBIDO: $<?= number_format($monto_total, 2) ?>
            <div style="font-size: 12px; margin-top: 5px; font-weight: normal;">
                <?= $this->numberToWords($monto_total) ?? '' ?> PESOS
            </div>
        </div>

        <!-- Observaciones -->
        <?php if (!empty($pago['observaciones'])): ?>
        <div class="observaciones">
            <strong>Observaciones:</strong><br>
            <?= nl2br(esc($pago['observaciones'])) ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>Recibo generado electrónicamente el <?= date('d/m/Y H:i:s') ?></p>
            <p>Este recibo es válido sin necesidad de firma autógrafa</p>
            <?php if (!empty($empresa['nombre'])): ?>
                <p><?= esc($empresa['nombre']) ?> - Sistema de Gestión de Ventas</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-print en algunos navegadores
        if (window.location.search.includes('print=1')) {
            window.print();
        }
    </script>
</body>
</html>