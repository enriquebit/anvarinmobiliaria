<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta - <?= $venta->folio_venta ?></title>
    <style>
        @page { 
            size: letter portrait; 
            margin: 0.5in; 
        }
        
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 10px; 
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 15px;
        }
        
        .logo { 
            max-width: 100px; 
            height: auto;
        }
        
        .empresa-info h2 {
            margin: 5px 0;
            color: #17a2b8;
            font-size: 16px;
        }
        
        .empresa-info p {
            margin: 2px 0;
            font-size: 9px;
            color: #666;
        }
        
        .info-documento { 
            background: #d1ecf1;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
        }
        
        .info-documento h3 {
            margin: 0 0 8px 0;
            color: #0c5460;
            font-size: 14px;
        }
        
        .info-documento p {
            margin: 2px 0;
            font-size: 9px;
            color: #0c5460;
        }
        
        .datos-cliente {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .columna-izq, .columna-der {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 8px;
            font-size: 9px;
        }
        
        .columna-izq {
            border-right: 1px solid #ddd;
        }
        
        .seccion h4 {
            margin: 0 0 6px 0;
            color: #495057;
            font-size: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 2px;
        }
        
        .seccion p {
            margin: 1px 0;
            font-size: 8px;
        }
        
        .resumen-financiero {
            background: #f8f9fa;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        
        .resumen-financiero h4 {
            margin: 0 0 8px 0;
            color: #155724;
            font-size: 11px;
        }
        
        .resumen-item {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }
        
        .resumen-concepto {
            display: table-cell;
            width: 60%;
            font-size: 9px;
        }
        
        .resumen-valor {
            display: table-cell;
            width: 40%;
            text-align: right;
            font-weight: bold;
            font-size: 9px;
        }
        
        .tabla-historial { 
            width: 100%; 
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 8px;
        }
        
        .tabla-historial th {
            background: #17a2b8;
            color: white;
            border: 1px solid #17a2b8;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
        }
        
        .tabla-historial td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }
        
        .tabla-historial tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .cobranza-pendiente {
            background: #fff3cd;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
        }
        
        .cobranza-pendiente h4 {
            margin: 0 0 8px 0;
            color: #856404;
            font-size: 11px;
        }
        
        .vencida {
            background: #f8d7da !important;
            color: #721c24;
        }
        
        .por-vencer {
            background: #d4edda !important;
            color: #155724;
        }
        
        .estadisticas {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        
        .estadistica-col {
            display: table-cell;
            width: 33%;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        .estadistica-valor {
            font-size: 12px;
            font-weight: bold;
            color: #17a2b8;
        }
        
        .estadistica-label {
            font-size: 8px;
            color: #666;
        }
        
        .footer { 
            margin-top: 20px; 
            font-size: 8px; 
            color: #6c757d;
            text-align: center;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        
        .monto-destacado {
            font-weight: bold;
            color: #28a745;
        }
        
        .monto-pendiente {
            font-weight: bold;
            color: #dc3545;
        }
        
        .separador {
            height: 10px;
        }
        
        .estado-liquidado {
            background: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-apartado {
            background: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-en-pagos {
            background: #d1ecf1;
            color: #0c5460;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <?php if (isset($empresa->logo_url)): ?>
            <img src="<?= $empresa->logo_url ?>" class="logo" alt="Logo">
        <?php endif; ?>
        
        <div class="empresa-info">
            <h2><?= $empresa->razon_social ?? 'Empresa' ?></h2>
            <p><?= $empresa->direccion ?? 'Dirección no disponible' ?></p>
            <p>Tel: <?= $empresa->telefono ?? 'N/A' ?> | Email: <?= $empresa->email ?? 'N/A' ?></p>
        </div>
    </div>
    
    <!-- Información del documento -->
    <div class="info-documento">
        <h3>ESTADO DE CUENTA</h3>
        <p><strong>Folio:</strong> <?= $folio ?></p>
        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($fecha_generacion)) ?></p>
        <p><strong>Venta:</strong> <?= $venta->folio_venta ?></p>
        <p><strong>Estado:</strong> 
            <span class="estado-<?= $resumen_financiero['estado_cuenta'] ?>">
                <?= strtoupper(str_replace('_', ' ', $resumen_financiero['estado_cuenta'])) ?>
            </span>
        </p>
    </div>
    
    <!-- Datos del cliente y lote -->
    <div class="datos-cliente">
        <div class="columna-izq">
            <div class="seccion">
                <h4>DATOS DEL CLIENTE</h4>
                <p><strong>Nombre:</strong> <?= $cliente->nombre . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno ?></p>
                <p><strong>Email:</strong> <?= $cliente->email ?? 'N/A' ?></p>
                <p><strong>Teléfono:</strong> <?= $cliente->telefono ?? 'N/A' ?></p>
            </div>
            
            <div class="seccion">
                <h4>DATOS DEL PROYECTO</h4>
                <p><strong>Proyecto:</strong> <?= $proyecto->nombre ?></p>
                <p><strong>Ubicación:</strong> <?= $proyecto->ubicacion ?? 'N/A' ?></p>
            </div>
        </div>
        
        <div class="columna-der">
            <div class="seccion">
                <h4>DATOS DEL LOTE</h4>
                <p><strong>Lote:</strong> <?= $lote->lote->numero ?></p>
                <p><strong>Manzana:</strong> <?= $lote->manzana->nombre ?? 'N/A' ?></p>
                <p><strong>Superficie:</strong> <?= $lote->lote->superficie ?? 'N/A' ?> m²</p>
            </div>
            
            <div class="seccion">
                <h4>PLAN DE PAGOS</h4>
                <p><strong>Plazo:</strong> <?= $plan_pagos['plazo_meses'] ?> meses</p>
                <p><strong>Mensualidad:</strong> $<?= number_format($plan_pagos['mensualidad'], 2) ?></p>
                <p><strong>Pagos restantes:</strong> <?= $plan_pagos['pagos_restantes'] ?></p>
            </div>
        </div>
    </div>
    
    <!-- Resumen financiero -->
    <div class="resumen-financiero">
        <h4>RESUMEN FINANCIERO</h4>
        
        <div class="resumen-item">
            <div class="resumen-concepto">💰 Precio Total</div>
            <div class="resumen-valor">$<?= number_format($resumen_financiero['precio_total'], 2) ?></div>
        </div>
        
        <div class="resumen-item">
            <div class="resumen-concepto">✅ Total Pagado</div>
            <div class="resumen-valor monto-destacado">$<?= number_format($resumen_financiero['total_pagado'], 2) ?></div>
        </div>
        
        <div class="resumen-item">
            <div class="resumen-concepto">⏳ Saldo Pendiente</div>
            <div class="resumen-valor monto-pendiente">$<?= number_format($resumen_financiero['saldo_pendiente'], 2) ?></div>
        </div>
        
        <div class="resumen-item">
            <div class="resumen-concepto">📊 Porcentaje Pagado</div>
            <div class="resumen-valor"><?= number_format($resumen_financiero['porcentaje_pagado'], 1) ?>%</div>
        </div>
        
        <div class="resumen-item">
            <div class="resumen-concepto">🔢 Número de Pagos</div>
            <div class="resumen-valor"><?= $resumen_financiero['numero_pagos'] ?></div>
        </div>
        
        <div class="resumen-item">
            <div class="resumen-concepto">📈 Promedio por Pago</div>
            <div class="resumen-valor">$<?= number_format($resumen_financiero['promedio_pago'], 2) ?></div>
        </div>
    </div>
    
    <!-- Historial de pagos -->
    <?php if (!empty($historial_pagos)): ?>
        <h4>HISTORIAL DE PAGOS</h4>
        <table class="tabla-historial">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Forma</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($historial_pagos, 0, 15) as $pago): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                        <td><?= $pago['concepto'] ?></td>
                        <td>$<?= number_format($pago['monto'], 2) ?></td>
                        <td><?= ucfirst($pago['tipo_pago']) ?></td>
                        <td>$<?= number_format($pago['saldo_actual'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Cobranza pendiente -->
    <?php if ($cobranza_pendiente['total_pendiente'] > 0): ?>
        <div class="cobranza-pendiente">
            <h4>COBRANZA PENDIENTE</h4>
            
            <div class="resumen-item">
                <div class="resumen-concepto">💸 Total Pendiente</div>
                <div class="resumen-valor monto-pendiente">$<?= number_format($cobranza_pendiente['total_pendiente'], 2) ?></div>
            </div>
            
            <div class="resumen-item">
                <div class="resumen-concepto">🔴 Vencidas</div>
                <div class="resumen-valor"><?= $cobranza_pendiente['cantidad_vencidas'] ?></div>
            </div>
            
            <div class="resumen-item">
                <div class="resumen-concepto">🟡 Por Vencer</div>
                <div class="resumen-valor"><?= $cobranza_pendiente['cantidad_por_vencer'] ?></div>
            </div>
            
            <?php if (!empty($cobranza_pendiente['detalle'])): ?>
                <div class="separador"></div>
                <table class="tabla-historial">
                    <thead>
                        <tr>
                            <th>Vencimiento</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($cobranza_pendiente['detalle'], 0, 10) as $pendiente): ?>
                            <tr class="<?= $pendiente['estado'] === 'vencida' ? 'vencida' : 'por-vencer' ?>">
                                <td><?= date('d/m/Y', strtotime($pendiente['fecha_vencimiento'])) ?></td>
                                <td><?= $pendiente['concepto'] ?></td>
                                <td>$<?= number_format($pendiente['monto'], 2) ?></td>
                                <td><?= ucfirst($pendiente['estado']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Próximos vencimientos -->
    <?php if (!empty($proximos_vencimientos)): ?>
        <div class="cobranza-pendiente">
            <h4>PRÓXIMOS VENCIMIENTOS (30 DÍAS)</h4>
            <table class="tabla-historial">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Días</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proximos_vencimientos as $vencimiento): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($vencimiento['fecha'])) ?></td>
                            <td><?= $vencimiento['concepto'] ?></td>
                            <td>$<?= number_format($vencimiento['monto'], 2) ?></td>
                            <td><?= $vencimiento['dias_restantes'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Estadísticas -->
    <div class="estadisticas">
        <div class="estadistica-col">
            <div class="estadistica-valor"><?= $estadisticas['dias_desde_apartado'] ?></div>
            <div class="estadistica-label">Días desde apartado</div>
        </div>
        
        <div class="estadistica-col">
            <div class="estadistica-valor"><?= $estadisticas['dias_desde_ultimo_pago'] ?></div>
            <div class="estadistica-label">Días desde último pago</div>
        </div>
        
        <div class="estadistica-col">
            <div class="estadistica-valor"><?= number_format($estadisticas['cumplimiento_pagos'], 1) ?>%</div>
            <div class="estadistica-label">Cumplimiento de pagos</div>
        </div>
    </div>
    
    <div class="estadisticas">
        <div class="estadistica-col">
            <div class="estadistica-valor"><?= ucfirst($estadisticas['tendencia_pagos']) ?></div>
            <div class="estadistica-label">Tendencia de pagos</div>
        </div>
        
        <div class="estadistica-col">
            <div class="estadistica-valor"><?= ucfirst($estadisticas['riesgo_morosidad']) ?></div>
            <div class="estadistica-label">Riesgo de morosidad</div>
        </div>
        
        <div class="estadistica-col">
            <div class="estadistica-valor">
                <?= $estadisticas['proyeccion_liquidacion'] ? date('m/Y', strtotime($estadisticas['proyeccion_liquidacion'])) : 'N/A' ?>
            </div>
            <div class="estadistica-label">Proyección liquidación</div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>IMPORTANTE:</strong> Este estado de cuenta refleja la situación al <?= date('d/m/Y H:i', strtotime($fecha_generacion)) ?></p>
        <p>Para cualquier aclaración, contacte al área de cobranza</p>
        <p>Documento generado automáticamente - Sistema de Gestión de Ventas</p>
        
        <?php if (isset($usuario_genera)): ?>
            <p>Generado por: <?= $usuario_genera->first_name . ' ' . $usuario_genera->last_name ?></p>
        <?php endif; ?>
    </div>
</body>
</html>