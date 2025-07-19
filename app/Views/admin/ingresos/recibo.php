<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de <?= ucfirst($tipo_operacion) ?> - <?= $folio ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.1;
            color: #000;
            background: white;
        }
        
        .container {
            width: 21.6cm;
            height: 27.9cm;
            margin: 0 auto;
            padding: 0.5cm;
        }
        
        /* Header empresarial */
        .company-header {
            border: 2px solid #000;
            padding: 6px;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .company-name {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .company-info {
            font-size: 7px;
            margin: 1px 0;
        }
        
        /* Cada copia del recibo */
        .receipt-copy {
            border: 1px solid #000;
            margin-bottom: 10px;
            height: 12cm;
            page-break-inside: avoid;
        }
        
        .copy-header {
            background: #000;
            color: white;
            text-align: center;
            padding: 3px;
            font-weight: bold;
            font-size: 8px;
        }
        
        .receipt-info {
            padding: 5px;
        }
        
        .receipt-title-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-weight: bold;
            font-size: 10px;
        }
        
        .receipt-folio {
            font-weight: bold;
            font-size: 10px;
        }
        
        /* Información en tabla */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        
        .info-table td {
            padding: 2px;
            border-bottom: 1px dotted #666;
            font-size: 8px;
        }
        
        .info-table .label {
            font-weight: bold;
            width: 30%;
        }
        
        .info-table .value {
            width: 70%;
        }
        
        /* Monto destacado */
        .amount-section {
            border: 2px solid #000;
            padding: 5px;
            text-align: center;
            margin: 5px 0;
            background: #f9f9f9;
        }
        
        .amount-label {
            font-size: 8px;
            font-weight: bold;
        }
        
        .amount-value {
            font-size: 14px;
            font-weight: bold;
            margin: 2px 0;
        }
        
        .amount-words {
            font-size: 7px;
            font-style: italic;
            text-transform: uppercase;
        }
        
        /* Información específica del concepto */
        .concept-info {
            border: 1px solid #666;
            padding: 4px;
            margin: 5px 0;
            background: #f5f5f5;
        }
        
        .concept-title {
            font-weight: bold;
            font-size: 8px;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        
        /* Firmas */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #000;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 20px;
            padding-top: 2px;
            font-size: 7px;
        }
        
        /* Divisor entre copias */
        .copy-divider {
            text-align: center;
            border-top: 1px dashed #000;
            margin: 5px 0;
            padding-top: 2px;
            font-size: 7px;
            color: #666;
        }
        
        /* Media queries para impresión */
        @media print {
            body {
                font-size: 8px;
            }
            
            .container {
                margin: 0;
                padding: 0.3cm;
            }
            
            .receipt-copy {
                height: 11.5cm;
            }
            
            .copy-divider {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header de la empresa (una vez arriba) -->
        <div class="company-header">
            <div class="company-name"><?= strtoupper($empresa_nombre) ?></div>
            <div class="company-info">RFC: <?= $empresa_rfc ?> | <?= $empresa_direccion ?></div>
            <div class="company-info">Tel: <?= $empresa_telefono ?> | Email: <?= $empresa_email ?></div>
            <div class="company-info">Desarrolladora Inmobiliaria Autorizada</div>
        </div>

        <!-- COPIA PARA EL CLIENTE -->
        <div class="receipt-copy">
            <div class="copy-header">COPIA PARA EL CLIENTE</div>
            <div class="receipt-info">
                <div class="receipt-title-row">
                    <span class="receipt-title">RECIBO DE <?= strtoupper($tipo_operacion) ?></span>
                    <span class="receipt-folio">FOLIO: <?= $folio ?></span>
                </div>
                
                <!-- Monto principal -->
                <div class="amount-section">
                    <div class="amount-label">IMPORTE RECIBIDO</div>
                    <div class="amount-value">$<?= number_format($monto, 2) ?> MXN</div>
                    <div class="amount-words"><?= $monto_letra ?></div>
                </div>

                <!-- Información específica según el concepto -->
                <?php if (strtolower($tipo_operacion) == 'apartado'): ?>
                <div class="concept-info">
                    <div class="concept-title">INFORMACIÓN DEL APARTADO</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Lote:</td>
                            <td class="value"><?= $lote_clave ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Proyecto:</td>
                            <td class="value"><?= $proyecto_nombre ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Plazo máx. enganche:</td>
                            <td class="value"><?= $dias_plazo ?? 90 ?> días naturales desde esta fecha</td>
                        </tr>
                        <tr>
                            <td class="label">Superficie:</td>
                            <td class="value"><?= isset($lote_area) ? number_format($lote_area, 2) . ' m²' : 'N/A' ?></td>
                        </tr>
                    </table>
                </div>
                <?php elseif (strtolower($tipo_operacion) == 'enganche'): ?>
                <div class="concept-info">
                    <div class="concept-title">INFORMACIÓN DEL ENGANCHE</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Lote:</td>
                            <td class="value"><?= $lote_clave ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Proyecto:</td>
                            <td class="value"><?= $proyecto_nombre ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Concepto:</td>
                            <td class="value">Enganche para compraventa de lote</td>
                        </tr>
                        <tr>
                            <td class="label">Superficie:</td>
                            <td class="value"><?= isset($lote_area) ? number_format($lote_area, 2) . ' m²' : 'N/A' ?></td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Información general -->
                <table class="info-table">
                    <tr>
                        <td class="label">Cliente:</td>
                        <td class="value"><?= $cliente_nombre ?></td>
                    </tr>
                    <tr>
                        <td class="label">Fecha/Hora:</td>
                        <td class="value"><?= $fecha_formateada ?></td>
                    </tr>
                    <tr>
                        <td class="label">Método de pago:</td>
                        <td class="value"><?= $metodo_pago ?></td>
                    </tr>
                    <?php if (!empty($referencia)): ?>
                    <tr>
                        <td class="label">Referencia:</td>
                        <td class="value"><?= $referencia ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <!-- Firmas -->
                <div class="signatures">
                    <div class="signature-box">
                        <div class="signature-line">RECIBÍ CONFORME</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line">CLIENTE</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divisor -->
        <div class="copy-divider">✂ - - - - - CORTAR AQUÍ - - - - - ✂</div>

        <!-- COPIA PARA ADMINISTRACIÓN -->
        <div class="receipt-copy">
            <div class="copy-header">COPIA PARA ADMINISTRACIÓN</div>
            <div class="receipt-info">
                <div class="receipt-title-row">
                    <span class="receipt-title">RECIBO DE <?= strtoupper($tipo_operacion) ?></span>
                    <span class="receipt-folio">FOLIO: <?= $folio ?></span>
                </div>
                
                <!-- Monto principal -->
                <div class="amount-section">
                    <div class="amount-label">IMPORTE RECIBIDO</div>
                    <div class="amount-value">$<?= number_format($monto, 2) ?> MXN</div>
                    <div class="amount-words"><?= $monto_letra ?></div>
                </div>

                <!-- Información específica según el concepto -->
                <?php if (strtolower($tipo_operacion) == 'apartado'): ?>
                <div class="concept-info">
                    <div class="concept-title">INFORMACIÓN DEL APARTADO</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Lote:</td>
                            <td class="value"><?= $lote_clave ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Proyecto:</td>
                            <td class="value"><?= $proyecto_nombre ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Plazo máx. enganche:</td>
                            <td class="value"><?= $dias_plazo ?? 90 ?> días naturales desde esta fecha</td>
                        </tr>
                        <tr>
                            <td class="label">Superficie:</td>
                            <td class="value"><?= isset($lote_area) ? number_format($lote_area, 2) . ' m²' : 'N/A' ?></td>
                        </tr>
                    </table>
                </div>
                <?php elseif (strtolower($tipo_operacion) == 'enganche'): ?>
                <div class="concept-info">
                    <div class="concept-title">INFORMACIÓN DEL ENGANCHE</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Lote:</td>
                            <td class="value"><?= $lote_clave ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Proyecto:</td>
                            <td class="value"><?= $proyecto_nombre ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="label">Concepto:</td>
                            <td class="value">Enganche para compraventa de lote</td>
                        </tr>
                        <tr>
                            <td class="label">Superficie:</td>
                            <td class="value"><?= isset($lote_area) ? number_format($lote_area, 2) . ' m²' : 'N/A' ?></td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Información general -->
                <table class="info-table">
                    <tr>
                        <td class="label">Cliente:</td>
                        <td class="value"><?= $cliente_nombre ?></td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td class="value"><?= $cliente_email ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Fecha/Hora:</td>
                        <td class="value"><?= $fecha_formateada ?></td>
                    </tr>
                    <tr>
                        <td class="label">Método de pago:</td>
                        <td class="value"><?= $metodo_pago ?></td>
                    </tr>
                    <?php if (!empty($referencia)): ?>
                    <tr>
                        <td class="label">Referencia:</td>
                        <td class="value"><?= $referencia ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Registrado por:</td>
                        <td class="value"><?= $vendedor_nombre ?? 'Sistema' ?></td>
                    </tr>
                </table>

                <!-- Firmas -->
                <div class="signatures">
                    <div class="signature-box">
                        <div class="signature-line">ENTREGÓ</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line">RECIBIÓ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-imprimir si se especifica
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 1000);
            }
        });
    </script>
</body>
</html>