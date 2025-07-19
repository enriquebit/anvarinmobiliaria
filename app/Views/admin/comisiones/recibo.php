<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Comisión</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/anvar-override.css') ?>">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .recibo-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }
        
        .header-recibo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #0066cc;
        }
        
        .empresa-info {
            flex: 1;
        }
        
        .empresa-info h1 {
            color: #0066cc;
            font-size: 24px;
            margin: 0 0 5px 0;
        }
        
        .empresa-info p {
            margin: 2px 0;
            color: #666;
        }
        
        .recibo-info {
            text-align: right;
            flex: 1;
        }
        
        .recibo-numero {
            font-size: 18px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 10px;
        }
        
        .fecha-recibo {
            font-size: 14px;
            color: #666;
        }
        
        .vendedor-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .vendedor-info h3 {
            color: #0066cc;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #333;
        }
        
        .info-value {
            flex: 1;
            color: #666;
        }
        
        .comision-detalles {
            margin-bottom: 30px;
        }
        
        .comision-detalles h3 {
            color: #0066cc;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .tabla-comision {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .tabla-comision th,
        .tabla-comision td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .tabla-comision th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .tabla-comision .money {
            text-align: right;
            font-weight: bold;
        }
        
        .resumen-pago {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .resumen-pago h3 {
            color: #0066cc;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 0;
            border-top: 2px solid #0066cc;
            margin-top: 10px;
        }
        
        .total-label {
            color: #333;
        }
        
        .total-amount {
            color: #0066cc;
            font-size: 18px;
        }
        
        .footer-recibo {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        
        .estatus-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .estatus-pagada {
            background: #d4edda;
            color: #155724;
        }
        
        .estatus-parcial {
            background: #fff3cd;
            color: #856404;
        }
        
        .observaciones {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #0066cc;
        }
        
        .observaciones h4 {
            margin-top: 0;
            color: #0066cc;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .recibo-container {
                border: none;
                box-shadow: none;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="recibo-container">
        <!-- Header -->
        <div class="header-recibo">
            <div class="empresa-info">
                <h1><?= esc($empresa->nombre ?? 'ANVAR INMOBILIARIA') ?></h1>
                <p><?= esc($empresa->direccion ?? 'Dirección de la empresa') ?></p>
                <p>Tel: <?= esc($empresa->telefono ?? 'N/A') ?> | Email: <?= esc($empresa->email ?? 'N/A') ?></p>
                <p>RFC: <?= esc($empresa->rfc ?? 'N/A') ?></p>
            </div>
            <div class="recibo-info">
                <div class="recibo-numero">RECIBO DE COMISIÓN</div>
                <div class="recibo-numero">#<?= str_pad($comision->id, 6, '0', STR_PAD_LEFT) ?></div>
                <div class="fecha-recibo">
                    Fecha: <?= date('d/m/Y') ?><br>
                    Hora: <?= date('H:i:s') ?>
                </div>
            </div>
        </div>

        <!-- Información del vendedor -->
        <div class="vendedor-info">
            <h3>Información del Vendedor</h3>
            <div class="info-row">
                <div class="info-label">Nombre:</div>
                <div class="info-value"><?= esc($comision->vendedor_nombre) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?= esc($comision->vendedor_email) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Cliente:</div>
                <div class="info-value"><?= esc($comision->cliente_nombre) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Lote:</div>
                <div class="info-value"><?= esc($comision->lote_clave) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Folio Venta:</div>
                <div class="info-value"><?= esc($comision->folio_venta ?? 'N/A') ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Estatus:</div>
                <div class="info-value">
                    <span class="estatus-badge <?= $comision->estatus === 'pagada' ? 'estatus-pagada' : 'estatus-parcial' ?>">
                        <?= ucfirst(str_replace('_', ' ', $comision->estatus)) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Detalles de la comisión -->
        <div class="comision-detalles">
            <h3>Detalles de la Comisión</h3>
            <table class="tabla-comision">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Base de Cálculo</th>
                        <th>Porcentaje</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Comisión por Venta</td>
                        <td class="money">$<?= number_format($comision->base_calculo, 2) ?></td>
                        <td><?= number_format($comision->porcentaje_aplicado, 2) ?>%</td>
                        <td class="money">$<?= number_format($comision->monto_comision_total, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Resumen de pago -->
        <div class="resumen-pago">
            <h3>Resumen de Pago</h3>
            <div class="info-row">
                <div class="info-label">Monto Total Comisión:</div>
                <div class="info-value">$<?= number_format($comision->monto_comision_total, 2) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Monto Pagado:</div>
                <div class="info-value">$<?= number_format($comision->monto_pagado_total, 2) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Saldo Pendiente:</div>
                <div class="info-value">$<?= number_format($comision->monto_comision_total - $comision->monto_pagado_total, 2) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Generación:</div>
                <div class="info-value"><?= date('d/m/Y', strtotime($comision->fecha_generacion)) ?></div>
            </div>
            <?php if ($comision->fecha_ultimo_pago): ?>
            <div class="info-row">
                <div class="info-label">Último Pago:</div>
                <div class="info-value"><?= date('d/m/Y', strtotime($comision->fecha_ultimo_pago)) ?></div>
            </div>
            <?php endif; ?>
            
            <div class="total-row">
                <div class="total-label">TOTAL PAGADO:</div>
                <div class="total-amount">$<?= number_format($comision->monto_pagado_total, 2) ?></div>
            </div>
        </div>

        <?php if ($comision->observaciones): ?>
        <div class="observaciones">
            <h4>Observaciones</h4>
            <p><?= nl2br(esc($comision->observaciones)) ?></p>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer-recibo">
            <p>
                Este recibo fue generado automáticamente por el sistema de gestión de comisiones.<br>
                Para cualquier duda o aclaración, favor de contactar al departamento de administración.
            </p>
            <p>
                <strong>Generado el:</strong> <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>

    <!-- Botones de acción (no se imprimen) -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>

    <script>
        // Auto-focus para impresión
        window.addEventListener('load', function() {
            // Opcional: auto-imprimir al cargar
            // window.print();
        });
    </script>
</body>
</html>