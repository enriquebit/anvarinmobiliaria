<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas Administrativas - Pagos Vencidos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .alert-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .critical-item {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .client-name {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
        }
        .days-overdue {
            background: #ffebee;
            color: #d32f2f;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .summary-box {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .stats-table th,
        .stats-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .stats-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="alert-icon">🚨</div>
        <h1>Alerta Crítica</h1>
        <p>Pagos Vencidos Requieren Atención Inmediata</p>
    </div>
    
    <div class="content">
        <p><strong>Equipo Administrativo,</strong></p>
        
        <p>Se han detectado <strong><?= count($mensualidades_criticas) ?></strong> mensualidades con pagos vencidos que requieren atención inmediata. Esta alerta se genera automáticamente para ayudar en la gestión de cobranza.</p>
        
        <?php 
        $totalMonto = 0;
        $clientesAfectados = [];
        $maxDiasAtraso = 0;
        ?>
        
        <h3>🔴 Mensualidades Críticas:</h3>
        
        <?php foreach ($mensualidades_criticas as $mensualidad): ?>
        <?php 
        $totalMonto += $mensualidad->monto_total + ($mensualidad->interes_moratorio ?? 0);
        $clientesAfectados[$mensualidad->cliente_id] = true;
        $maxDiasAtraso = max($maxDiasAtraso, $mensualidad->dias_atraso ?? 0);
        ?>
        
        <div class="critical-item">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div class="client-name">
                    <?= esc($mensualidad->cliente_nombre ?? 'Cliente N/A') ?>
                </div>
                <span class="days-overdue">
                    <?= $mensualidad->dias_atraso ?? 0 ?> días vencida
                </span>
            </div>
            
            <div style="margin-bottom: 8px;">
                <strong>Propiedad:</strong> <?= esc($mensualidad->lote_clave ?? 'N/A') ?> | 
                <strong>Folio:</strong> <?= esc($mensualidad->folio_venta ?? 'N/A') ?>
            </div>
            
            <div style="margin-bottom: 8px;">
                <strong>Mensualidad:</strong> #<?= $mensualidad->numero_pago ?? 'N/A' ?> | 
                <strong>Vencimiento:</strong> <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Monto Original:</strong> $<?= number_format($mensualidad->monto_total, 2) ?><br>
                    <?php if (!empty($mensualidad->interes_moratorio) && $mensualidad->interes_moratorio > 0): ?>
                    <strong>Mora Acumulada:</strong> <span style="color: #ffc107;">$<?= number_format($mensualidad->interes_moratorio, 2) ?></span>
                    <?php endif; ?>
                </div>
                <div class="amount">
                    $<?= number_format($mensualidad->monto_total + ($mensualidad->interes_moratorio ?? 0), 2) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="summary-box">
            <h3 style="margin-top: 0; color: #dc3545;">📊 Resumen de la Situación</h3>
            
            <table class="stats-table">
                <tr>
                    <th>Total Mensualidades Vencidas:</th>
                    <td><strong><?= count($mensualidades_criticas) ?></strong></td>
                </tr>
                <tr>
                    <th>Clientes Afectados:</th>
                    <td><strong><?= count($clientesAfectados) ?></strong></td>
                </tr>
                <tr>
                    <th>Monto Total en Riesgo:</th>
                    <td><strong class="amount">$<?= number_format($totalMonto, 2) ?></strong></td>
                </tr>
                <tr>
                    <th>Mayor Atraso:</th>
                    <td><strong><?= $maxDiasAtraso ?> días</strong></td>
                </tr>
                <tr>
                    <th>Fecha del Reporte:</th>
                    <td><?= $fecha_reporte ?></td>
                </tr>
            </table>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #856404;">📋 Acciones Recomendadas:</h4>
            <ul style="margin-bottom: 0; padding-left: 20px;">
                <li><strong>Contactar inmediatamente</strong> a los clientes con más de 30 días de atraso</li>
                <li><strong>Negociar planes de pago</strong> para regularizar la situación</li>
                <li><strong>Aplicar políticas de cobranza</strong> según los procedimientos establecidos</li>
                <li><strong>Documentar todas las gestiones</strong> realizadas en el sistema</li>
                <li><strong>Evaluar casos para proceso jurídico</strong> si es necesario</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= site_url('/admin/mensualidades/pendientes') ?>" class="btn">
                🔍 Ver Dashboard de Cobranza
            </a>
            <a href="<?= site_url('/admin/estado-cuenta') ?>" class="btn" style="background: #007bff;">
                📊 Ver Estado de Cuenta
            </a>
        </div>
        
        <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #f5c6cb;">
            <h4 style="margin-top: 0; color: #721c24;">⚠️ Importante:</h4>
            <p style="margin-bottom: 0;">Esta alerta se genera automáticamente cuando se detectan pagos vencidos críticos. Es fundamental actuar rápidamente para minimizar el impacto en el flujo de caja y mantener la salud financiera de la empresa.</p>
        </div>
    </div>
    
    <div class="footer">
        <p>Esta alerta fue generada automáticamente por el sistema el <?= $fecha_reporte ?></p>
        <p style="margin-top: 20px;">
            <strong>Sistema de Gestión Inmobiliaria</strong><br>
            Para soporte técnico contacta al administrador del sistema
        </p>
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #999;">
            Este es un email automático de alertas críticas. No responder a este correo.<br>
            Si necesitas ajustar la frecuencia de estas alertas, contacta al administrador del sistema.
        </p>
    </div>
</body>
</html>