<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Comisión - <?= $comision['folio'] ?></title>
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
            border: 2px solid #2c3e50;
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
            color: #2c3e50;
            margin: 15px 0;
            text-transform: uppercase;
        }
        .folio-fecha {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            font-weight: bold;
        }
        .datos-vendedor {
            margin: 20px 0;
            background: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
        }
        .datos-vendedor h4 {
            margin: 0 0 10px 0;
            color: #27ae60;
            border-bottom: 1px solid #27ae60;
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
        .detalle-comision {
            margin: 20px 0;
            border: 1px solid #27ae60;
            border-radius: 5px;
        }
        .detalle-header {
            background: #27ae60;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
        }
        .detalle-content {
            padding: 15px;
        }
        .calculo-comision {
            background: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #27ae60;
        }
        .monto-total {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #27ae60;
            margin: 20px 0;
            padding: 15px;
            background: #e8f5e8;
            border-radius: 5px;
            border: 2px solid #27ae60;
        }
        .datos-venta {
            margin: 20px 0;
            background: #f0f7ff;
            padding: 15px;
            border-radius: 5px;
        }
        .datos-venta h4 {
            margin: 0 0 10px 0;
            color: #3498db;
            border-bottom: 1px solid #3498db;
            padding-bottom: 5px;
        }
        .observaciones {
            margin: 20px 0;
            padding: 10px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
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
        .tipo-comision {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .tipo-apartado {
            background: #e74c3c;
            color: white;
        }
        .tipo-total {
            background: #27ae60;
            color: white;
        }
        .tipo-diferido {
            background: #f39c12;
            color: white;
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
            
            <div class="titulo-recibo">Recibo de Comisión</div>
        </div>

        <!-- Folio y Fecha -->
        <div class="folio-fecha">
            <div>
                <span class="label">FOLIO:</span> 
                <span style="color: #27ae60; font-size: 14px;"><?= esc($comision['folio']) ?></span>
            </div>
            <div>
                <span class="label">FECHA:</span> 
                <span><?= date('d/m/Y H:i', strtotime($comision['fecha'])) ?></span>
            </div>
        </div>

        <!-- Datos del Vendedor -->
        <div class="datos-vendedor">
            <h4>Datos del Vendedor</h4>
            <div class="row">
                <span class="label">Nombre:</span>
                <span class="value"><?= esc($vendedor['nombre']) ?></span>
            </div>
            <div class="row">
                <span class="label">Email:</span>
                <span class="value"><?= esc($vendedor['email']) ?></span>
            </div>
            <div class="row">
                <span class="label">ID Vendedor:</span>
                <span class="value"><?= esc($vendedor['id']) ?></span>
            </div>
        </div>

        <!-- Datos de la Venta -->
        <?php if ($venta): ?>
        <div class="datos-venta">
            <h4>Datos de la Venta Relacionada</h4>
            <div class="row">
                <span class="label">Folio de Venta:</span>
                <span class="value"><?= esc($venta['folio']) ?></span>
            </div>
            <div class="row">
                <span class="label">Cliente:</span>
                <span class="value"><?= esc($venta['cliente']) ?></span>
            </div>
            <div class="row">
                <span class="label">Fecha de Venta:</span>
                <span class="value"><?= date('d/m/Y', strtotime($venta['fecha'])) ?></span>
            </div>
            <div class="row">
                <span class="label">Precio Final:</span>
                <span class="value">$<?= number_format($venta['precio_final'], 2) ?></span>
            </div>
            <?php if ($lote): ?>
            <div class="row">
                <span class="label">Lote:</span>
                <span class="value"><?= esc($lote['nombre']) ?> - <?= esc($lote['proyecto']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Detalle de la Comisión -->
        <div class="detalle-comision">
            <div class="detalle-header">Detalle de la Comisión</div>
            <div class="detalle-content">
                <div class="row">
                    <span class="label">Tipo de Comisión:</span>
                    <span class="value">
                        <span class="tipo-comision tipo-<?= esc($comision['tipo']) ?>"><?= ucfirst(esc($comision['tipo'])) ?></span>
                    </span>
                </div>
                <div class="row">
                    <span class="label">Concepto:</span>
                    <span class="value"><?= esc($concepto) ?></span>
                </div>
                
                <!-- Cálculo de la Comisión -->
                <div class="calculo-comision">
                    <div class="row">
                        <span class="label">Monto Base:</span>
                        <span class="value">$<?= number_format($comision['monto_base'], 2) ?></span>
                    </div>
                    <div class="row">
                        <span class="label">Porcentaje Aplicado:</span>
                        <span class="value"><?= number_format($comision['porcentaje'], 2) ?>%</span>
                    </div>
                    <div class="row" style="border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px;">
                        <span class="label">Monto de Comisión:</span>
                        <span class="value" style="font-weight: bold; color: #27ae60;">$<?= number_format($comision['monto'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monto Total -->
        <div class="monto-total">
            COMISIÓN A PAGAR: $<?= number_format($monto_total, 2) ?>
            <div style="font-size: 12px; margin-top: 5px; font-weight: normal;">
                <?= $this->numberToWords($monto_total) ?? '' ?> PESOS
            </div>
        </div>

        <!-- Observaciones -->
        <?php if (!empty($comision['observaciones'])): ?>
        <div class="observaciones">
            <strong>Observaciones:</strong><br>
            <?= nl2br(esc($comision['observaciones'])) ?>
        </div>
        <?php endif; ?>

        <!-- Información Adicional para Comisiones Diferidas -->
        <?php if (isset($comision['numero_mensualidad']) && $comision['numero_mensualidad']): ?>
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0; color: #856404;">Información de Pago Diferido</h4>
            <div class="row">
                <span class="label">Mensualidad:</span>
                <span class="value"><?= esc($comision['numero_mensualidad']) ?> de <?= esc($comision['total_mensualidades']) ?></span>
            </div>
            <div class="row">
                <span class="label">Modalidad:</span>
                <span class="value">Comisión Diferida - Cero Enganche</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>Recibo generado electrónicamente el <?= date('d/m/Y H:i:s') ?></p>
            <p><strong>COMPROBANTE DE COMISIÓN</strong> - Válido sin necesidad de firma autógrafa</p>
            <?php if (!empty($empresa['nombre'])): ?>
                <p><?= esc($empresa['nombre']) ?> - Sistema de Gestión de Comisiones</p>
            <?php endif; ?>
            
            <div style="margin-top: 15px; font-size: 9px; color: #999;">
                <p>Este documento certifica el pago de la comisión correspondiente por las actividades de venta realizadas.</p>
                <p>Para cualquier aclaración, contacte al departamento de administración.</p>
            </div>
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