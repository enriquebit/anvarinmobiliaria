<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto de Financiamiento - <?= esc($cliente['nombre_completo']) ?></title>
    
    <style>
        @page {
            size: letter portrait;
            margin: 0.75in;
        }
        
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            background-color: white;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }
        
        .container {
            width: 100%;
            max-width: 7.5in; /* Letter width minus margins */
            margin: 0;
            padding: 0;
        }
        
        .header-presupuesto {
            background-color: #007bff;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .header-presupuesto h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .info-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .section-title {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #007bff;
            margin: 20px 0 10px 0;
            font-weight: bold;
            font-size: 14px;
        }
        
        .table-amortizacion {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 15px;
        }
        
        .table-amortizacion th, .table-amortizacion td {
            border: 1px solid #ddd;
            padding: 3px 2px;
            text-align: center;
        }
        
        .table-amortizacion th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .table-amortizacion .sin-intereses {
            background-color: #f8fff8;
        }
        
        .table-amortizacion .con-intereses {
            background-color: #fffcf8;
        }
        
        .table-amortizacion .pago-anticipado {
            background-color: #e8f4f8;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer-info {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .row {
            display: table;
            width: 100%;
        }
        
        .col-50 {
            display: table-cell;
            width: 50%;
            padding: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        
        <!-- HEADER -->
        <div class="header-presupuesto">
            <h1>Presupuesto de Financiamiento</h1>
            <p><strong>Cliente:</strong> <?= esc($cliente['nombre_completo']) ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y') ?> | <strong>Folio:</strong> <?= strtoupper(substr(uniqid(), -8)) ?></p>
        </div>
        
        <!-- INFORMACIÓN DEL CLIENTE Y RESUMEN -->
        <div class="info-section">
            <div class="row">
                <div class="col-50">
                    <h3>Información del Cliente</h3>
                    <table class="info-table">
                        <tr>
                            <th>Nombre:</th>
                            <td><?= esc($cliente['nombre_completo']) ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= esc($cliente['email'] ?: 'No especificado') ?></td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td><?= esc($cliente['telefono'] ?: 'No especificado') ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-50">
                    <h3>Resumen Financiero</h3>
                    <table class="info-table">
                        <tr>
                            <th>Precio del Lote:</th>
                            <td class="text-right">$<?= number_format($datos['precio_total'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Enganche:</th>
                            <td class="text-right">$<?= number_format($datos['enganche_monto'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Monto a Financiar:</th>
                            <td class="text-right"><strong>$<?= number_format($datos['monto_financiar'], 2) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Plazo:</th>
                            <td class="text-right"><?= $datos['plazo_meses'] ?> meses</td>
                        </tr>
                        <tr>
                            <th>Tasa de Interés:</th>
                            <td class="text-right"><?= $datos['tasa_interes'] ?>% anual</td>
                        </tr>
                        <tr style="background-color: #fff3cd;">
                            <th>Mensualidad:</th>
                            <td class="text-right"><strong>$<?= number_format($datos['mensualidad'], 2) ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- PAGOS ANTICIPADOS -->
        <?php if (!empty($pagos_anticipados)): ?>
        <div class="info-section">
            <h3>Pagos Anticipados Programados</h3>
            <table class="info-table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagos_anticipados as $pago): ?>
                    <tr>
                        <td class="text-center"><?= $pago['mes_aplicacion'] ?></td>
                        <td class="text-right">$<?= number_format($pago['monto'], 2) ?></td>
                        <td><?= esc($pago['descripcion']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- TABLA DE AMORTIZACIÓN -->
        <?php
        // Calcular la tabla de amortización aquí directamente
        $saldoCapital = $datos['monto_financiar'];
        $fechaBase = new DateTime();
        $mensualidadActual = $datos['mensualidad'];
        $totalInteresesPagados = 0;
        $totalCapitalPagado = 0;
        $totalPagado = 0;
        
        // Preparar array de pagos anticipados por mes
        $pagosAnticipadosPorMes = [];
        if (!empty($pagos_anticipados)) {
            foreach ($pagos_anticipados as $pago) {
                $mes = $pago['mes_aplicacion'];
                if (!isset($pagosAnticipadosPorMes[$mes])) {
                    $pagosAnticipadosPorMes[$mes] = 0;
                }
                $pagosAnticipadosPorMes[$mes] += $pago['monto'];
            }
        }
        ?>

        <div class="section-title">Tabla de Amortización</div>
        <table class="table-amortizacion">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Fecha</th>
                    <th>Mensualidad</th>
                    <th>Capital</th>
                    <th>Intereses</th>
                    <th>Saldo</th>
                    <?php if (!empty($pagos_anticipados)): ?>
                    <th>Pago Extra</th>
                    <th>Total Pagado</th>
                    <?php endif; ?>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= $datos['plazo_meses']; $i++): 
                    $pagoAnticipado = $pagosAnticipadosPorMes[$i] ?? 0;
                    
                    // Determinar tipo de mes
                    $esSinInteres = false;
                    $tipoMes = 'Con intereses';
                    
                    if ($configuracion->meses_sin_intereses > 0) {
                        if ($configuracion->meses_con_intereses > 0) {
                            if ($i <= $configuracion->meses_sin_intereses) {
                                $esSinInteres = true;
                                $tipoMes = 'Sin intereses';
                            }
                        } else {
                            $esSinInteres = true;
                            $tipoMes = 'Sin intereses';
                        }
                    }
                    
                    $intereses = $esSinInteres ? 0 : $saldoCapital * ($datos['tasa_interes'] / 12 / 100);
                    $capital = $mensualidadActual - $intereses;
                    
                    // Ajustar último pago
                    if ($i == $datos['plazo_meses']) {
                        $capital = $saldoCapital;
                        $mensualidadActual = $capital + $intereses;
                    }
                    
                    // Actualizar saldo
                    $saldoCapital -= $capital;
                    if ($pagoAnticipado > 0) {
                        $saldoCapital -= $pagoAnticipado;
                        // Recalcular mensualidad para los meses restantes
                        $mesesRestantes = $datos['plazo_meses'] - $i;
                        if ($mesesRestantes > 0 && $saldoCapital > 0) {
                            $mensualidadActual = $saldoCapital / $mesesRestantes;
                        }
                    }
                    
                    // Acumular totales
                    $totalInteresesPagados += $intereses;
                    $totalCapitalPagado += $capital;
                    $totalPagado += ($mensualidadActual + $pagoAnticipado);
                    
                    // Fecha
                    $fechaPago = clone $fechaBase;
                    $fechaPago->add(new DateInterval('P' . $i . 'M'));
                    
                    $rowClass = $esSinInteres ? 'sin-intereses' : 'con-intereses';
                    if ($pagoAnticipado > 0) {
                        $rowClass .= ' pago-anticipado';
                    }
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><strong><?= $i ?></strong></td>
                    <td><?= $fechaPago->format('d/m/Y') ?></td>
                    <td class="text-right">$<?= number_format($mensualidadActual, 2) ?></td>
                    <td class="text-right">$<?= number_format($capital, 2) ?></td>
                    <td class="text-right"><?= $intereses > 0 ? '$' . number_format($intereses, 2) : '-' ?></td>
                    <td class="text-right">$<?= number_format(max(0, $saldoCapital), 2) ?></td>
                    <?php if (!empty($pagos_anticipados)): ?>
                    <td class="text-right">
                        <?php if ($pagoAnticipado > 0): ?>
                            <strong>$<?= number_format($pagoAnticipado, 2) ?></strong>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <strong>$<?= number_format($mensualidadActual + $pagoAnticipado, 2) ?></strong>
                    </td>
                    <?php endif; ?>
                    <td class="text-center"><?= $tipoMes ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #343a40; color: white; font-weight: bold;">
                    <td colspan="2" class="text-center">TOTALES</td>
                    <td class="text-right">-</td>
                    <td class="text-right">$<?= number_format($totalCapitalPagado, 2) ?></td>
                    <td class="text-right">$<?= number_format($totalInteresesPagados, 2) ?></td>
                    <td class="text-right">$0.00</td>
                    <?php if (!empty($pagos_anticipados)): ?>
                    <td class="text-right">$<?= number_format(array_sum(array_column($pagos_anticipados, 'monto')), 2) ?></td>
                    <td class="text-right">$<?= number_format($totalPagado, 2) ?></td>
                    <?php endif; ?>
                    <td>-</td>
                </tr>
            </tfoot>
        </table>

        <!-- FOOTER -->
        <div class="footer-info">
            <p>
                <strong>Presupuesto generado el <?= date('d/m/Y \a \l\a\s H:i') ?></strong><br>
                Este presupuesto es válido por 30 días y está sujeto a cambios sin previo aviso.
            </p>
        </div>
    </div>
</body>
</html>