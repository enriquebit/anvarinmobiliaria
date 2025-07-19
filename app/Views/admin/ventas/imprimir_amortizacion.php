<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Amortización - <?= esc($datos['empresa_nombre']) ?></title>
    <style>
        /* CONFIGURACIÓN DE PÁGINA VERTICAL CARTA */
        @page {
            size: letter portrait;
            margin: 0.5in 0.4in;
        }
        
        /* RESET Y CONFIGURACIÓN BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            width: 100%;
            max-width: 7.5in; /* Carta vertical menos márgenes */
            margin: 0 auto;
            color: #2c3e50;
        }
        
        /* ENCABEZADO ESTILO ESTADO DE CUENTA */
        .header {
            margin-bottom: 15px;
            border: 2px solid #2c3e50;
            background: #ffffff;
        }
        
        .header-top {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }
        
        .logo-section {
            width: 80px;
            height: 60px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .logo-placeholder {
            font-size: 10px;
            color: #666;
            text-align: center;
            line-height: 1.2;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-tagline {
            font-size: 11px;
            opacity: 0.9;
            margin-bottom: 4px;
        }
        
        .document-info {
            text-align: right;
            min-width: 180px;
        }
        
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .document-date {
            font-size: 10px;
            opacity: 0.8;
        }
        
        .header-details {
            padding: 10px 15px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            font-size: 11px;
        }
        
        .details-section {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        
        .detail-line {
            display: flex;
            justify-content: space-between;
            margin: 1px 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 80px;
        }
        
        .detail-value {
            font-weight: bold;
            color: #2c3e50;
            text-align: right;
        }
        
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 2px;
        }
        
        /* TABLA OPTIMIZADA PARA VERTICAL */
        .table-container {
            width: 100%;
            margin: 0 auto;
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin: 0 auto;
        }
        
        th {
            background-color: #2c3e50;
            color: #fff;
            font-weight: bold;
            padding: 10px 6px;
            text-align: center;
            font-size: 11px;
            border: 1px solid #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 8px 6px;
            text-align: center;
            font-size: 10px;
            border: 1px solid #000;
            vertical-align: middle;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-left {
            text-align: left;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* FILAS ALTERNAS */
        tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }
        
        tbody tr:nth-child(even) {
            background-color: #ffffff;
        }
        
        /* FILA DE TOTALES */
        .totals-row {
            background-color: #2c3e50 !important;
            color: #fff !important;
            font-weight: bold;
            border-top: 3px solid #000;
        }
        
        .totals-row td {
            background-color: #2c3e50 !important;
            color: #fff !important;
            font-weight: bold;
            padding: 8px 4px;
            font-size: 10px;
        }
        
        /* COLUMNAS OPTIMIZADAS PARA VERTICAL (SIN TIPO) */
        .col-periodo { width: 12%; }
        .col-fecha { width: 16%; }
        .col-saldo-inicial { width: 18%; }
        .col-pago { width: 18%; }
        .col-interes { width: 15%; }
        .col-capital { width: 18%; }
        .col-saldo-final { width: 18%; }
        
        /* PIE DE PÁGINA */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        
        /* ÁREA DE FIRMA DEL ASESOR */
        .signature-area {
            margin-top: 30px;
            padding: 20px 0;
            border-top: 1px solid #ccc;
        }
        
        .signature-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 20px;
        }
        
        .signature-line {
            flex: 1;
            margin: 0 20px;
            text-align: center;
        }
        
        .signature-line .line {
            border-bottom: 2px solid #000;
            margin-bottom: 5px;
            height: 40px;
            position: relative;
        }
        
        .signature-line .label {
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .signature-line .sublabel {
            font-size: 9px;
            color: #666;
            font-style: italic;
            margin-top: 2px;
        }
        
        .date-box {
            text-align: center;
            margin-top: 15px;
        }
        
        .date-line {
            display: inline-block;
            border-bottom: 2px solid #000;
            width: 200px;
            height: 30px;
            margin: 0 10px;
        }
        
        .date-label {
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* IMPRESIÓN */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .signature-area {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO ESTILO ESTADO DE CUENTA -->
    <div class="header">
        <!-- BARRA SUPERIOR CON LOGO Y DATOS PRINCIPALES -->
        <div class="header-top">
            <div class="logo-section">
                <div class="logo-placeholder">
                    LOGO<br>EMPRESA
                </div>
            </div>
            <div class="company-info">
                <div class="company-name"><?= esc($datos['empresa_nombre']) ?></div>
                <div class="company-tagline">Soluciones Inmobiliarias Profesionales</div>
                <div style="font-size: 10px; margin-top: 4px;">
                    📞 <?= esc($datos['empresa_telefono']) ?> • 📧 <?= esc($datos['empresa_email']) ?>
                </div>
            </div>
            <div class="document-info">
                <div class="document-title">TABLA DE AMORTIZACIÓN</div>
                <div class="document-date"><?= date('d/m/Y H:i:s') ?></div>
            </div>
        </div>
        
        <!-- DETALLES COMPACTOS ESTILO ESTADO DE CUENTA -->
        <div class="header-details">
            <div class="details-grid">
                <!-- SECCIÓN IZQUIERDA: DATOS DEL INMUEBLE -->
                <div class="details-section">
                    <div class="section-title">DATOS DEL INMUEBLE</div>
                    <div class="detail-line">
                        <span class="detail-label">Proyecto:</span>
                        <span class="detail-value"><?= esc($datos['proyecto_nombre']) ?></span>
                    </div>
                    <div class="detail-line">
                        <span class="detail-label">Lote:</span>
                        <span class="detail-value"><?= esc($datos['lote_clave']) ?><?php if (!empty($datos['lote_numero'])): ?> (#<?= esc($datos['lote_numero']) ?>)<?php endif; ?></span>
                    </div>
                    <div class="detail-line">
                        <span class="detail-label">Manzana:</span>
                        <span class="detail-value"><?= esc($datos['manzana_nombre']) ?></span>
                    </div>
                    <?php if (!empty($datos['lote_area'])): ?>
                    <div class="detail-line">
                        <span class="detail-label">Área:</span>
                        <span class="detail-value"><?= number_format($datos['lote_area'], 2) ?> m²</span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-line">
                        <span class="detail-label">Vendedor:</span>
                        <span class="detail-value"><?= esc($datos['vendedor_nombre']) ?></span>
                    </div>
                </div>
                
                <!-- SECCIÓN DERECHA: DATOS FINANCIEROS -->
                <div class="details-section">
                    <div class="section-title">DATOS FINANCIEROS</div>
                    <div class="detail-line">
                        <span class="detail-label">Cliente:</span>
                        <span class="detail-value"><?= esc($datos['cliente_nombre']) ?></span>
                    </div>
                    <div class="detail-line">
                        <span class="detail-label">Precio Total:</span>
                        <span class="detail-value"><?= $datos['precio_formateado'] ?></span>
                    </div>
                    <div class="detail-line">
                        <span class="detail-label">Enganche:</span>
                        <span class="detail-value"><?= $datos['enganche_formateado'] ?></span>
                    </div>
                    <div class="detail-line">
                        <span class="detail-label">A Financiar:</span>
                        <span class="detail-value"><?= $datos['financiar_formateado'] ?></span>
                    </div>
                    <div class="detail-line">
                        <span class="detail-label">Plazo:</span>
                        <span class="detail-value"><?= $datos['plazo_meses'] ?> meses</span>
                    </div>
                    <div class="detail-line" style="border-top: 1px solid #dee2e6; padding-top: 3px; margin-top: 3px;">
                        <span class="detail-label" style="font-weight: bold;">Pago Mensual:</span>
                        <span class="detail-value" style="font-size: 13px; color: #e74c3c; font-weight: bold;"><?= $datos['pago_formateado'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- TABLA DE AMORTIZACIÓN -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="col-periodo">Período</th>
                    <th class="col-fecha">Fecha Pago</th>
                    <th class="col-saldo-inicial">Saldo Inicial</th>
                    <th class="col-pago">Pago Mensual</th>
                    <th class="col-interes">Interés</th>
                    <th class="col-capital">Capital</th>
                    <th class="col-saldo-final">Saldo Final</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagos as $pago): ?>
                <tr>
                    <td class="text-center"><?= $pago['periodo'] ?></td>
                    <td class="text-center"><?= $pago['fecha'] ?></td>
                    <td class="text-right"><?= $pago['saldo_inicial'] ?></td>
                    <td class="text-right"><strong><?= $pago['pago'] ?></strong></td>
                    <td class="text-right"><?= $pago['interes'] ?></td>
                    <td class="text-right"><?= $pago['capital'] ?></td>
                    <td class="text-right"><?= $pago['saldo_final'] ?></td>
                </tr>
                <?php endforeach; ?>
                
                <!-- FILA DE TOTALES -->
                <tr class="totals-row">
                    <td colspan="3" class="text-center"><strong>TOTALES</strong></td>
                    <td class="text-right"><strong><?= $datos['total_pagos'] ?></strong></td>
                    <td class="text-right"><strong><?= $datos['total_intereses'] ?></strong></td>
                    <td class="text-right"><strong><?= $datos['total_capital'] ?></strong></td>
                    <td class="text-right"><strong>$0.00</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <div><strong><?= esc($datos['empresa_nombre']) ?></strong> - Tabla de Amortización Oficial</div>
    </div>
    
    <!-- ÁREA DE FIRMA DEL ASESOR -->
    <div class="signature-area">
        <div class="signature-box">
            <div class="signature-line">
                <div class="line"></div>
                <div class="label">ASESOR QUE ATENDIÓ</div>
                <div class="sublabel">(Firma y nombre completo)</div>
            </div>
            <div class="signature-line">
                <div class="line"></div>
                <div class="label">CLIENTE</div>
                <div class="sublabel">(Firma de conformidad)</div>
            </div>
        </div>
        
        <div class="date-box">
            <span class="date-label">FECHA: </span>
            <div class="date-line"></div>
        </div>
    </div>
    
    <!-- BOTÓN DE IMPRESIÓN (solo visible en pantalla) -->
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            🖨️ Imprimir
        </button>
        <button onclick="window.close()" style="padding: 12px 24px; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            ✖️ Cerrar
        </button>
    </div>
</body>
</html>