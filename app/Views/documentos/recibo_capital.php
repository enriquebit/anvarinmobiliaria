<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Abono a Capital - <?= $venta->folio ?></title>
    <style>
        @page { 
            size: letter portrait; 
            margin: 0.75in; 
        }
        
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #fd7e14;
            padding-bottom: 20px;
        }
        
        .logo { 
            max-width: 120px; 
            height: auto;
        }
        
        .empresa-info {
            margin-top: 10px;
        }
        
        .empresa-info h2 {
            margin: 5px 0;
            color: #fd7e14;
            font-size: 18px;
        }
        
        .empresa-info p {
            margin: 2px 0;
            font-size: 10px;
            color: #666;
        }
        
        .info-recibo { 
            background: #ffeaa7;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #fd7e14;
        }
        
        .info-recibo h3 {
            margin: 0 0 10px 0;
            color: #d63031;
            font-size: 16px;
        }
        
        .info-recibo p {
            margin: 3px 0;
            font-size: 11px;
            color: #2d3436;
        }
        
        .datos-principales {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .columna-izq, .columna-der {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        
        .columna-izq {
            border-right: 1px solid #ddd;
        }
        
        .seccion {
            margin-bottom: 15px;
        }
        
        .seccion h4 {
            margin: 0 0 8px 0;
            color: #495057;
            font-size: 12px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 3px;
        }
        
        .seccion p {
            margin: 2px 0;
            font-size: 11px;
        }
        
        .tabla-saldos { 
            width: 100%; 
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
        }
        
        .tabla-saldos th {
            background: #fd7e14;
            color: white;
            border: 1px solid #fd7e14;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .tabla-saldos td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .tabla-saldos tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .saldo-anterior {
            background: #f8d7da !important;
            color: #721c24;
        }
        
        .abono-capital {
            background: #d4edda !important;
            color: #155724;
            font-weight: bold;
        }
        
        .saldo-actual {
            background: #fff3cd !important;
            color: #856404;
            font-weight: bold;
        }
        
        .formas-pago {
            background: #e2e3e5;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #6c757d;
        }
        
        .formas-pago h4 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 12px;
        }
        
        .forma-pago-item {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .forma-pago-concepto {
            display: table-cell;
            width: 70%;
            font-size: 11px;
        }
        
        .forma-pago-monto {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }
        
        .impacto-financiero {
            background: #d1ecf1;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
        }
        
        .impacto-financiero h4 {
            margin: 0 0 10px 0;
            color: #0c5460;
            font-size: 12px;
        }
        
        .impacto-item {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .impacto-concepto {
            display: table-cell;
            width: 70%;
            font-size: 11px;
            color: #0c5460;
        }
        
        .impacto-valor {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: bold;
            font-size: 11px;
            color: #0c5460;
        }
        
        .footer { 
            margin-top: 40px; 
            font-size: 9px; 
            color: #6c757d;
            text-align: center;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        .numero-folio {
            font-size: 14px;
            font-weight: bold;
            color: #fd7e14;
        }
        
        .monto-destacado {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
        }
        
        .fecha-destacada {
            font-weight: bold;
            color: #495057;
        }
        
        .separador {
            height: 20px;
        }
        
        .beneficios {
            background: #d4edda;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        
        .beneficios h4 {
            margin: 0 0 10px 0;
            color: #155724;
            font-size: 12px;
        }
        
        .beneficios ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .beneficios li {
            margin: 3px 0;
            font-size: 10px;
            color: #155724;
        }
    </style>
</head>
<body>
    <!-- Header con logo y datos empresa -->
    <div class="header">
        <?php if (isset($empresa->logo_url)): ?>
            <img src="<?= $empresa->logo_url ?>" class="logo" alt="Logo <?= $empresa->razon_social ?>">
        <?php endif; ?>
        
        <div class="empresa-info">
            <h2><?= $empresa->razon_social ?? 'Empresa' ?></h2>
            <p><?= $empresa->direccion ?? 'Dirección no disponible' ?></p>
            <p>Tel: <?= $empresa->telefono ?? 'N/A' ?> | Email: <?= $empresa->email ?? 'N/A' ?></p>
            <?php if (isset($empresa->rfc)): ?>
                <p>RFC: <?= $empresa->rfc ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Información del recibo -->
    <div class="info-recibo">
        <h3><?= $titulo ?></h3>
        <p><strong>Folio:</strong> <span class="numero-folio"><?= $folio ?></span></p>
        <p><strong>Fecha:</strong> <span class="fecha-destacada"><?= date('d/m/Y H:i', strtotime($fecha_generacion)) ?></span></p>
        <p><strong>Venta:</strong> <?= $venta->folio ?></p>
        <p><strong>Tipo:</strong> PAGO EXTRAORDINARIO A CAPITAL</p>
    </div>
    
    <!-- Datos principales en dos columnas -->
    <div class="datos-principales">
        <div class="columna-izq">
            <!-- Información del cliente -->
            <div class="seccion">
                <h4>DATOS DEL CLIENTE</h4>
                <p><strong>Cliente:</strong> <?= $cliente->nombre . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno ?></p>
                <p><strong>Email:</strong> <?= $cliente->email ?? 'N/A' ?></p>
                <p><strong>Teléfono:</strong> <?= $cliente->telefono ?? 'N/A' ?></p>
                <?php if (isset($cliente->rfc)): ?>
                    <p><strong>RFC:</strong> <?= $cliente->rfc ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Información del proyecto -->
            <div class="seccion">
                <h4>DATOS DEL PROYECTO</h4>
                <p><strong>Proyecto:</strong> <?= $proyecto->nombre ?></p>
                <p><strong>Ubicación:</strong> <?= $proyecto->ubicacion ?? 'N/A' ?></p>
                <?php if (isset($proyecto->descripcion)): ?>
                    <p><strong>Descripción:</strong> <?= $proyecto->descripcion ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="columna-der">
            <!-- Información del lote -->
            <div class="seccion">
                <h4>DATOS DEL LOTE</h4>
                <p><strong>Lote:</strong> <?= $lote->numero ?></p>
                <p><strong>Manzana:</strong> <?= $manzana->nombre ?? 'N/A' ?></p>
                <p><strong>Superficie:</strong> <?= $lote->superficie ?? 'N/A' ?> m²</p>
                <?php if (isset($lote->precio_m2)): ?>
                    <p><strong>Precio por m²:</strong> $<?= number_format($lote->precio_m2, 2) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Información del pago -->
            <div class="seccion">
                <h4>DATOS DEL PAGO</h4>
                <p><strong>Fecha de Pago:</strong> <?= date('d/m/Y', strtotime($ingreso->fecha_pago ?? $fecha_generacion)) ?></p>
                <p><strong>Concepto:</strong> <?= $concepto ?></p>
                <p><strong>Monto:</strong> <span class="monto-destacado">$<?= number_format($ingreso->total, 2) ?></span></p>
                <p><strong>Estado:</strong> APLICADO</p>
            </div>
        </div>
    </div>
    
    <div class="separador"></div>
    
    <!-- Tabla de saldos -->
    <table class="tabla-saldos">
        <thead>
            <tr>
                <th>Concepto</th>
                <th style="width: 30%; text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr class="saldo-anterior">
                <td>Saldo Anterior</td>
                <td style="text-align: right;">$<?= number_format($saldo_anterior, 2) ?></td>
            </tr>
            <tr class="abono-capital">
                <td>(-) Abono a Capital</td>
                <td style="text-align: right;">$<?= number_format($ingreso->total, 2) ?></td>
            </tr>
            <tr class="saldo-actual">
                <td><strong>Saldo Actual</strong></td>
                <td style="text-align: right;"><strong>$<?= number_format($saldo_actual, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
    
    <!-- Formas de pago -->
    <?php if ($ingreso): ?>
        <div class="formas-pago">
            <h4>FORMAS DE PAGO UTILIZADAS:</h4>
            
            <?php if ($ingreso->efectivo > 0): ?>
                <div class="forma-pago-item">
                    <div class="forma-pago-concepto">💵 Efectivo</div>
                    <div class="forma-pago-monto">$<?= number_format($ingreso->efectivo, 2) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($ingreso->transferencia > 0): ?>
                <div class="forma-pago-item">
                    <div class="forma-pago-concepto">🏦 Transferencia Bancaria</div>
                    <div class="forma-pago-monto">$<?= number_format($ingreso->transferencia, 2) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($ingreso->cheque > 0): ?>
                <div class="forma-pago-item">
                    <div class="forma-pago-concepto">📄 Cheque</div>
                    <div class="forma-pago-monto">$<?= number_format($ingreso->cheque, 2) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($ingreso->tarjeta > 0): ?>
                <div class="forma-pago-item">
                    <div class="forma-pago-concepto">💳 Tarjeta de Crédito/Débito</div>
                    <div class="forma-pago-monto">$<?= number_format($ingreso->tarjeta, 2) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($ingreso->referencia_pago) && !empty($ingreso->referencia_pago)): ?>
                <div class="forma-pago-item">
                    <div class="forma-pago-concepto">🔢 Referencia de Pago</div>
                    <div class="forma-pago-monto"><?= $ingreso->referencia_pago ?></div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Impacto financiero -->
    <div class="impacto-financiero">
        <h4>IMPACTO FINANCIERO:</h4>
        
        <div class="impacto-item">
            <div class="impacto-concepto">📉 Reducción del Saldo</div>
            <div class="impacto-valor">$<?= number_format($ingreso->total, 2) ?></div>
        </div>
        
        <?php 
        $porcentajeReduccion = $saldo_anterior > 0 ? ($ingreso->total / $saldo_anterior) * 100 : 0;
        ?>
        <div class="impacto-item">
            <div class="impacto-concepto">📊 Porcentaje de Reducción</div>
            <div class="impacto-valor"><?= number_format($porcentajeReduccion, 2) ?>%</div>
        </div>
        
        <?php if ($saldo_actual > 0): ?>
            <div class="impacto-item">
                <div class="impacto-concepto">💰 Saldo Restante</div>
                <div class="impacto-valor">$<?= number_format($saldo_actual, 2) ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($saldo_actual <= 0): ?>
            <div class="impacto-item">
                <div class="impacto-concepto">🎉 Estado del Lote</div>
                <div class="impacto-valor">LIQUIDADO</div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Beneficios del pago -->
    <div class="beneficios">
        <h4>BENEFICIOS DE ESTE PAGO:</h4>
        <ul>
            <li>Se reduce el saldo principal de la deuda</li>
            <li>Se disminuye el monto de intereses futuros</li>
            <li>Se acelera el proceso de liquidación del lote</li>
            <li>Se mejora el historial crediticio del cliente</li>
            <?php if ($saldo_actual <= 0): ?>
                <li><strong>¡FELICITACIONES! Su lote ha sido liquidado completamente</strong></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Observaciones -->
    <?php if (isset($observaciones) && !empty($observaciones)): ?>
        <div class="observaciones">
            <h4>OBSERVACIONES:</h4>
            <p><?= $observaciones ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>INFORMACIÓN IMPORTANTE:</strong></p>
        <p>• Este pago se aplicó directamente al saldo de capital del lote</p>
        <p>• Los abonos a capital reducen el monto total a pagar por intereses</p>
        <p>• Conserve este recibo como comprobante de pago</p>
        <p>• Para cualquier aclaración, contacte al área de cobranza</p>
        
        <div class="separador"></div>
        
        <p><strong>Documento generado automáticamente el <?= date('d/m/Y H:i:s') ?></strong></p>
        <p>Sistema de Gestión de Ventas - <?= $empresa->razon_social ?? 'Empresa' ?></p>
        
        <?php if (isset($usuario_genera)): ?>
            <p>Aplicado por: <?= $usuario_genera->first_name . ' ' . $usuario_genera->last_name ?></p>
        <?php endif; ?>
    </div>
</body>
</html>