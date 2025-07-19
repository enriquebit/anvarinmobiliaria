<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Aplicado</title>
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
            background: #28a745;
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
        .success-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .details-box {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
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
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="success-icon">✅</div>
        <h1>¡Pago Aplicado Exitosamente!</h1>
        <p>Tu pago ha sido procesado y aplicado a tu cuenta</p>
    </div>
    
    <div class="content">
        <p>Estimado(a) <strong><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno) ?></strong>,</p>
        
        <p>Nos complace informarte que tu pago ha sido aplicado exitosamente a tu cuenta. A continuación encontrarás los detalles de la transacción:</p>
        
        <div class="details-box">
            <h3>Detalles del Pago</h3>
            
            <div class="detail-row">
                <span class="detail-label">Folio de Pago:</span>
                <span class="detail-value"><?= esc($pago['folio_pago']) ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Monto Pagado:</span>
                <span class="detail-value amount">$<?= number_format($pago['monto_pago'], 2) ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Fecha de Pago:</span>
                <span class="detail-value"><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Forma de Pago:</span>
                <span class="detail-value"><?= ucfirst($pago['forma_pago']) ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Propiedad:</span>
                <span class="detail-value"><?= esc($pago['lote_clave']) ?> (<?= esc($pago['folio_venta']) ?>)</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Mensualidad:</span>
                <span class="detail-value">#<?= esc($pago['numero_mensualidad']) ?></span>
            </div>
        </div>
        
        <p><strong>Estado:</strong> ✅ <span style="color: #28a745;">Pago Aplicado y Confirmado</span></p>
        
        <p>Tu pago ha sido registrado en nuestro sistema y aplicado automáticamente a tu cuenta. Puedes consultar tu estado de cuenta actualizado en cualquier momento a través de nuestro portal cliente.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= site_url('/cliente/estado-cuenta') ?>" class="btn">
                Ver Mi Estado de Cuenta
            </a>
        </div>
        
        <div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #1976d2;">💡 Tip útil:</h4>
            <p style="margin-bottom: 0;">Recuerda que puedes programar recordatorios en tu calendario para no olvidar las próximas fechas de vencimiento. También puedes activar notificaciones automáticas en tu perfil.</p>
        </div>
    </div>
    
    <div class="footer">
        <p>Gracias por tu pago puntual. Si tienes alguna pregunta o necesitas asistencia, no dudes en contactarnos.</p>
        <p style="margin-top: 20px;">
            <strong>Atención al Cliente:</strong><br>
            📞 Teléfono: [TELÉFONO]<br>
            📧 Email: [EMAIL_SOPORTE]<br>
            💬 WhatsApp: [WHATSAPP]
        </p>
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #999;">
            Este es un email automático generado por nuestro sistema. Por favor no responder a este correo.<br>
            Si no puedes ver este email correctamente, <a href="<?= site_url('/cliente/pagos/comprobante/' . ($pago['id'] ?? '')) ?>">haz clic aquí</a>.
        </p>
    </div>
</body>
</html>