<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Vencimientos</title>
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
            background: #ffc107;
            color: #212529;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .reminder-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .vencimiento-item {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #ffc107;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .vencimiento-urgente {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .vencimiento-critico {
            border-left-color: #dc3545;
            background: #ffebee;
        }
        .property-name {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        .days-left {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .days-urgent {
            background: #ffebee;
            color: #d32f2f;
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
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn-secondary {
            background: #007bff;
        }
        .alert-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="reminder-icon">📅</div>
        <h1>Recordatorio de Pagos</h1>
        <p>Tienes mensualidades próximas a vencer</p>
    </div>
    
    <div class="content">
        <p>Estimado(a) <strong><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno) ?></strong>,</p>
        
        <p>Te recordamos que tienes <strong><?= count($alertas) ?></strong> mensualidad(es) que vencen en los próximos <strong><?= $dias_anticipacion ?></strong> días. Mantener tus pagos al día te ayuda a evitar intereses moratorios.</p>
        
        <?php 
        $totalMonto = 0;
        $vencimientosUrgentes = 0;
        ?>
        
        <?php foreach ($alertas as $alerta): ?>
        <?php 
        $diasRestantes = (strtotime($alerta->fecha_vencimiento) - time()) / (60 * 60 * 24);
        $esUrgente = $diasRestantes <= 3;
        $esCritico = $diasRestantes <= 1;
        $totalMonto += $alerta->monto_total;
        if ($esUrgente) $vencimientosUrgentes++;
        ?>
        
        <div class="vencimiento-item <?= $esCritico ? 'vencimiento-critico' : ($esUrgente ? 'vencimiento-urgente' : '') ?>">
            <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 10px;">
                <div class="property-name"><?= esc($alerta->lote_clave ?? 'Propiedad') ?></div>
                <span class="days-left <?= $esUrgente ? 'days-urgent' : '' ?>">
                    <?= floor($diasRestantes) ?> día<?= floor($diasRestantes) != 1 ? 's' : '' ?>
                </span>
            </div>
            
            <div style="margin-bottom: 8px;">
                <strong>Mensualidad:</strong> #<?= $alerta->numero_pago ?> | 
                <strong>Folio:</strong> <?= esc($alerta->folio_venta) ?>
            </div>
            
            <div style="margin-bottom: 8px;">
                <strong>Fecha de Vencimiento:</strong> <?= date('d/m/Y', strtotime($alerta->fecha_vencimiento)) ?>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="amount">$<?= number_format($alerta->monto_total, 2) ?></span>
                <?php if ($diasRestantes <= 2): ?>
                    <span style="color: #dc3545; font-weight: bold; font-size: 12px;">
                        ⚠️ URGENTE
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if ($vencimientosUrgentes > 0): ?>
        <div class="alert-box">
            <strong>⚠️ Atención:</strong> Tienes <?= $vencimientosUrgentes ?> mensualidad(es) que vencen en 3 días o menos. Te recomendamos realizar el pago lo antes posible para evitar intereses moratorios.
        </div>
        <?php endif; ?>
        
        <div style="background: white; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;">
            <h3 style="margin-top: 0; color: #007bff;">Resumen de Pagos Próximos</h3>
            <p style="margin: 5px 0;"><strong>Total de mensualidades:</strong> <?= count($alertas) ?></p>
            <p style="margin: 5px 0;"><strong>Monto total:</strong> <span class="amount">$<?= number_format($totalMonto, 2) ?></span></p>
            <p style="margin: 5px 0;"><strong>Vencimientos urgentes:</strong> <?= $vencimientosUrgentes ?></p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= site_url('/cliente/pagos') ?>" class="btn">
                💳 Realizar Pagos
            </a>
            <a href="<?= site_url('/cliente/estado-cuenta') ?>" class="btn btn-secondary">
                📊 Ver Estado de Cuenta
            </a>
        </div>
        
        <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #28a745;">💡 Opciones de Pago Disponibles:</h4>
            <ul style="margin-bottom: 0; padding-left: 20px;">
                <li><strong>Pago en línea:</strong> Tarjeta de crédito/débito o transferencia (inmediato)</li>
                <li><strong>Depósito bancario:</strong> En cualquiera de nuestras cuentas bancarias</li>
                <li><strong>Transferencia SPEI:</strong> Usando nuestras claves interbancarias</li>
                <li><strong>En oficina:</strong> Efectivo, tarjeta o cheque</li>
            </ul>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #856404;">📱 ¡Programa tus recordatorios!</h4>
            <p style="margin-bottom: 0;">Configura alertas en tu calendario o celular para no olvidar las fechas de vencimiento. También puedes activar notificaciones automáticas en tu perfil de cliente.</p>
        </div>
    </div>
    
    <div class="footer">
        <p>Este recordatorio se envía automáticamente para ayudarte a mantener tus pagos al día.</p>
        <p style="margin-top: 20px;">
            <strong>¿Necesitas ayuda?</strong><br>
            📞 Teléfono: [TELÉFONO]<br>
            📧 Email: [EMAIL_SOPORTE]<br>
            💬 WhatsApp: [WHATSAPP]
        </p>
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #999;">
            Este es un recordatorio automático. Si ya realizaste el pago, puedes ignorar este mensaje.<br>
            Para dejar de recibir recordatorios, ajusta tus preferencias en tu <a href="<?= site_url('/cliente/mi-perfil') ?>">perfil de cliente</a>.
        </p>
    </div>
</body>
</html>