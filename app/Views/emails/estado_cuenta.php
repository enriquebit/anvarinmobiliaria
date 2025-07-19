<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Estado de Cuenta</title>
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
            background: #007bff;
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
        .document-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="document-icon">📊</div>
        <h1>Tu Estado de Cuenta</h1>
        <p>Documento adjunto listo para consulta</p>
    </div>
    
    <div class="content">
        <p>Estimado(a) <strong><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno) ?></strong>,</p>
        
        <p>Adjunto a este correo encontrarás tu estado de cuenta actualizado, generado el <strong><?= $fecha_generacion ?></strong>.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0;">📄 Contenido del Documento</h3>
            <ul>
                <li><strong>Resumen financiero general</strong> de todas tus propiedades</li>
                <li><strong>Detalle de cada propiedad</strong> con su tabla de amortización</li>
                <li><strong>Historial completo de pagos</strong> realizados</li>
                <li><strong>Estado actual</strong> de cada mensualidad</li>
                <li><strong>Proyección de liquidación</strong> de tus propiedades</li>
            </ul>
        </div>
        
        <div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #1976d2;">💡 ¿Cómo usar tu estado de cuenta?</h4>
            <ul style="margin-bottom: 0; padding-left: 20px;">
                <li><strong>Revisa tu progreso:</strong> Ve cuánto has pagado y cuánto te falta</li>
                <li><strong>Planifica tus pagos:</strong> Consulta las próximas fechas de vencimiento</li>
                <li><strong>Guarda el documento:</strong> Úsalo como comprobante para tus registros</li>
                <li><strong>Comparte si es necesario:</strong> Con tu contador o asesor financiero</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= site_url('/cliente/estado-cuenta') ?>" class="btn btn-secondary">
                🌐 Ver en Portal Web
            </a>
            <a href="<?= site_url('/cliente/pagos') ?>" class="btn">
                💳 Realizar Pagos
            </a>
        </div>
        
        <div class="info-box">
            <h4 style="margin-top: 0; color: #28a745;">✅ Ventajas del Portal Web</h4>
            <p style="margin-bottom: 0;">Recuerda que también puedes consultar tu estado de cuenta actualizado en tiempo real a través de nuestro portal web, donde además podrás:</p>
            <ul style="margin-top: 10px; margin-bottom: 0;">
                <li>Realizar pagos en línea de forma segura</li>
                <li>Descargar comprobantes de pagos anteriores</li>
                <li>Ver calendarios de vencimientos interactivos</li>
                <li>Recibir notificaciones automáticas</li>
            </ul>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #856404;">📞 ¿Tienes preguntas?</h4>
            <p style="margin-bottom: 0;">Si tienes alguna duda sobre tu estado de cuenta o necesitas asistencia, no dudes en contactarnos. Estamos aquí para ayudarte:</p>
            <p style="margin-top: 10px; margin-bottom: 0;">
                📞 <strong>Teléfono:</strong> [TELÉFONO_ATENCION]<br>
                📧 <strong>Email:</strong> [EMAIL_SOPORTE]<br>
                💬 <strong>WhatsApp:</strong> [WHATSAPP_SOPORTE]
            </p>
        </div>
    </div>
    
    <div class="footer">
        <p>Gracias por ser parte de nuestra familia de clientes y por mantener tus pagos al día.</p>
        <p style="margin-top: 20px;">
            <strong>Tu Estado de Cuenta se actualiza automáticamente</strong><br>
            Puedes solicitar una nueva copia cuando lo necesites desde tu portal web
        </p>
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #999;">
            Este estado de cuenta fue generado automáticamente el <?= $fecha_generacion ?>.<br>
            Si no puedes abrir el archivo adjunto, <a href="<?= site_url('/cliente/estado-cuenta') ?>">accede a tu portal web</a> para consultarlo en línea.
        </p>
    </div>
</body>
</html>