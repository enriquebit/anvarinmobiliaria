<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto de Financiamiento - ANVAR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 0;
        }
        
        .header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
        }
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .message {
            background-color: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .presupuesto-info {
            background-color: #e8f4f8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .presupuesto-info h3 {
            margin: 0 0 15px 0;
            color: #0056b3;
            font-size: 18px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .info-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-table td:first-child {
            font-weight: bold;
            width: 40%;
            color: #495057;
        }
        
        .info-table td:last-child {
            text-align: right;
            color: #007bff;
            font-weight: bold;
        }
        
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            margin: 20px 0;
        }
        
        .highlight .amount {
            font-size: 24px;
            font-weight: bold;
            color: #856404;
        }
        
        .attachment-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
        }
        
        .attachment-info .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        
        .footer p {
            margin: 5px 0;
            color: #6c757d;
            font-size: 12px;
        }
        
        .contact-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .contact-info h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .contact-info p {
            margin: 5px 0;
            color: #6c757d;
        }
        
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .button:hover {
            background-color: #0056b3;
        }
        
        .disclaimer {
            font-size: 11px;
            color: #6c757d;
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>ANVAR</h1>
            <p>Presupuesto de Financiamiento</p>
        </div>
        
        <!-- CONTENIDO PRINCIPAL -->
        <div class="content">
            <div class="greeting">
                <strong>Estimado(a) <?= esc($cliente['nombre_completo']) ?>,</strong>
            </div>
            
            <div class="message">
                <?= nl2br(esc($mensaje)) ?>
            </div>
            
            <!-- INFORMACIÓN DEL PRESUPUESTO -->
            <div class="presupuesto-info">
                <h3>Resumen de su Presupuesto</h3>
                <table class="info-table">
                    <tr>
                        <td>Folio:</td>
                        <td><?= esc($folio) ?></td>
                    </tr>
                    <tr>
                        <td>Fecha:</td>
                        <td><?= esc($fecha) ?></td>
                    </tr>
                    <tr>
                        <td>Precio del Lote:</td>
                        <td>$<?= number_format($datos['precio_total'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Enganche:</td>
                        <td>$<?= number_format($datos['enganche_monto'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Monto a Financiar:</td>
                        <td>$<?= number_format($datos['monto_financiar'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Plazo:</td>
                        <td><?= $datos['plazo_meses'] ?> meses</td>
                    </tr>
                    <tr>
                        <td>Tasa de Interés:</td>
                        <td><?= $datos['tasa_interes'] ?>% anual</td>
                    </tr>
                </table>
            </div>
            
            <!-- MENSUALIDAD DESTACADA -->
            <div class="highlight">
                <div>Su mensualidad será de:</div>
                <div class="amount">$<?= number_format($datos['mensualidad'], 2) ?></div>
            </div>
            
            <!-- INFORMACIÓN DEL ADJUNTO -->
            <div class="attachment-info">
                <div class="icon">📄</div>
                <strong>Adjunto:</strong> Presupuesto detallado con tabla de amortización completa<br>
                <small>El archivo PDF contiene todos los detalles de su plan de financiamiento</small>
            </div>
            
            <!-- INFORMACIÓN DE CONTACTO -->
            <div class="contact-info">
                <h4>¿Necesita más información?</h4>
                <p>Nuestro equipo está disponible para resolver cualquier duda sobre su presupuesto.</p>
                <p><strong>Teléfono:</strong> (555) 123-4567</p>
                <p><strong>Email:</strong> info@anvar.com</p>
                <p><strong>Horario:</strong> Lunes a Viernes, 9:00 AM - 6:00 PM</p>
            </div>
            
            <!-- DISCLAIMER -->
            <div class="disclaimer">
                <strong>Importante:</strong> Este presupuesto es válido por 30 días a partir de la fecha de emisión. 
                Los términos y condiciones pueden cambiar sin previo aviso. Para formalizar su financiamiento, 
                será necesario completar el proceso de solicitud y aprobación crediticia.
            </div>
        </div>
        
        <!-- FOOTER -->
        <div class="footer">
            <p><strong>ANVAR - Sistema de Presupuestos</strong></p>
            <p>Este es un mensaje automático, por favor no responda a este correo.</p>
            <p>© <?= date('Y') ?> ANVAR. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>