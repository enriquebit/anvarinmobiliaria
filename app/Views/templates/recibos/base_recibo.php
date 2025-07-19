<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_recibo ?> - <?= $folio ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.2;
            color: #000;
            background: white;
        }
        
        .recibo-container {
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            background: white;
            display: flex;
            flex-direction: column;
            padding: 0;
        }
        
        .recibo-half {
            height: 13.5cm;
            padding: 15px;
            border-bottom: 1px dashed #000;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .recibo-half:last-child {
            border-bottom: none;
        }
        
        .recibo-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }
        
        .empresa-info {
            flex: 1;
            display: flex;
            align-items: center;
        }
        
        .logo {
            width: 80px;
            height: auto;
            margin-right: 15px;
        }
        
        .empresa-datos h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #000;
        }
        
        .empresa-datos p {
            font-size: 9px;
            color: #000;
            margin-bottom: 1px;
        }
        
        .recibo-info {
            text-align: right;
            flex-shrink: 0;
        }
        
        .folio {
            background: #000;
            color: white;
            padding: 6px 12px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .fecha {
            font-size: 9px;
            color: #000;
        }
        
        .recibo-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 12px;
            flex: 1;
        }
        
        .datos-cliente, .datos-transaccion {
            border: 1px solid #000;
            padding: 8px;
        }
        
        .section-title {
            background: #000;
            color: white;
            padding: 3px 6px;
            margin: -8px -8px 6px -8px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .campo {
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .campo label {
            font-weight: bold;
            color: #000;
            font-size: 9px;
            min-width: 60px;
            margin-right: 5px;
        }
        
        .campo span {
            font-size: 9px;
            color: #000;
            text-align: right;
            flex: 1;
            word-wrap: break-word;
        }
        
        .monto-principal {
            background: #f0f0f0;
            border: 2px solid #000;
            padding: 12px;
            text-align: center;
            margin: 10px 0;
        }
        
        .monto-numero {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #000;
        }
        
        .monto-letras {
            font-size: 9px;
            font-style: italic;
            color: #000;
            text-transform: uppercase;
        }
        
        .detalles-pago {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin: 10px 0;
        }
        
        .detalle-item {
            text-align: center;
            padding: 6px;
            border: 1px solid #000;
        }
        
        .detalle-item strong {
            display: block;
            font-size: 9px;
            margin-bottom: 2px;
            font-weight: bold;
        }
        
        .detalle-item span {
            font-size: 8px;
            color: #000;
        }
        
        .observaciones {
            border: 1px solid #000;
            padding: 8px;
            margin: 8px 0;
            font-size: 8px;
        }
        
        .observaciones strong {
            font-size: 9px;
            margin-bottom: 3px;
            display: block;
        }
        
        .observaciones p {
            margin-bottom: 2px;
        }
        
        .footer-recibo {
            margin-top: auto;
            padding-top: 8px;
            border-top: 1px solid #000;
            display: flex;
            justify-content: space-between;
        }
        
        .firma-section {
            text-align: center;
            flex: 1;
            margin: 0 10px;
        }
        
        .firma-linea {
            border-top: 1px solid #000;
            margin: 20px 0 3px 0;
        }
        
        .firma-texto {
            font-size: 8px;
            color: #000;
        }
        
        .tipo-copia {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #000;
            color: white;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .tipo-copia.cliente {
            background: #666;
        }
        
        @media print {
            body {
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .recibo-container {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .recibo-half {
                page-break-inside: avoid;
            }
        }
        
        @media screen and (max-width: 768px) {
            .recibo-container {
                width: 100%;
                margin: 5px;
            }
            
            .recibo-body {
                grid-template-columns: 1fr;
            }
            
            .detalles-pago {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="recibo-container">
        <!-- COPIA PARA CLIENTE -->
        <div class="recibo-half">
            <div class="tipo-copia cliente">COPIA CLIENTE</div>
            
            <div class="recibo-header">
                <div class="empresa-info">
                    <img src="<?= base_url('assets/img/logo_admin.png') ?>" alt="Logo" class="logo">
                    <div class="empresa-datos">
                        <h1><?= esc($empresa['nombre']) ?></h1>
                        <p><?= esc($empresa['direccion']) ?></p>
                        <p>Tel: <?= esc($empresa['telefono']) ?> | <?= esc($empresa['email']) ?></p>
                        <p>RFC: <?= esc($empresa['rfc']) ?></p>
                    </div>
                </div>
                <div class="recibo-info">
                    <div class="folio"><?= esc($titulo_recibo) ?> <?= esc($folio) ?></div>
                    <div class="fecha"><?= $fecha_formateada ?></div>
                </div>
            </div>
            
            <div class="recibo-body">
                <div class="datos-cliente">
                    <div class="section-title">Datos del Cliente</div>
                    <div class="campo">
                        <label>Nombre:</label>
                        <span><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . ($cliente->apellido_materno ?? '')) ?></span>
                    </div>
                    <?php if (!empty($cliente->email)): ?>
                    <div class="campo">
                        <label>Email:</label>
                        <span><?= esc($cliente->email) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($cliente->telefono)): ?>
                    <div class="campo">
                        <label>Teléfono:</label>
                        <span><?= esc($cliente->telefono) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="datos-transaccion">
                    <div class="section-title"><?= isset($section_title_custom) ? $section_title_custom : 'Datos del Pago' ?></div>
                    <?= $this->renderSection('datos_especificos_cliente') ?>
                </div>
            </div>
            
            <div class="monto-principal">
                <div class="monto-numero"><?= $monto_formateado ?></div>
                <div class="monto-letras">(<?= $monto_letras ?>)</div>
                <div style="font-size: 10px; margin-top: 5px; font-weight: bold;">
                    <?= esc($concepto_principal ?? 'PAGO DE SERVICIOS INMOBILIARIOS') ?>
                </div>
            </div>
            
            <div class="detalles-pago">
                <div class="detalle-item">
                    <strong>FORMA DE PAGO</strong>
                    <span><?= esc($metodo_pago ?? 'Efectivo') ?></span>
                </div>
                <div class="detalle-item">
                    <strong>REFERENCIA</strong>
                    <span><?= esc(($referencia && $referencia !== '0') ? $referencia : 'N/A') ?></span>
                </div>
                <div class="detalle-item">
                    <strong>ATENDIÓ</strong>
                    <span><?= esc($vendedor->nombre_completo ?? $vendedor->username ?? 'N/A') ?></span>
                </div>
            </div>
            
            <div class="observaciones">
                <strong><?= esc($titulo_recibo) ?>:</strong>
                <?php foreach($observaciones_especiales as $observacion): ?>
                    <p><?= esc($observacion) ?></p>
                <?php endforeach; ?>
            </div>
            
            <div class="footer-recibo">
                <div class="firma-section">
                    <div class="firma-linea"></div>
                    <div class="firma-texto">Firma del Cliente</div>
                </div>
                <div class="firma-section">
                    <div class="firma-linea"></div>
                    <div class="firma-texto">Firma del Vendedor</div>
                </div>
            </div>
        </div>
        
        <!-- COPIA PARA ADMINISTRACIÓN -->
        <div class="recibo-half">
            <div class="tipo-copia">COPIA ADMINISTRACIÓN</div>
            
            <div class="recibo-header">
                <div class="empresa-info">
                    <img src="<?= base_url('assets/img/logo_admin.png') ?>" alt="Logo" class="logo">
                    <div class="empresa-datos">
                        <h1><?= esc($empresa['nombre']) ?></h1>
                        <p><?= esc($empresa['direccion']) ?></p>
                        <p>Tel: <?= esc($empresa['telefono']) ?> | <?= esc($empresa['email']) ?></p>
                        <p>RFC: <?= esc($empresa['rfc']) ?></p>
                    </div>
                </div>
                <div class="recibo-info">
                    <div class="folio"><?= esc($titulo_recibo) ?> <?= esc($folio) ?></div>
                    <div class="fecha"><?= $fecha_formateada ?></div>
                </div>
            </div>
            
            <div class="recibo-body">
                <div class="datos-cliente">
                    <div class="section-title">Datos del Cliente</div>
                    <div class="campo">
                        <label>Nombre:</label>
                        <span><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . ($cliente->apellido_materno ?? '')) ?></span>
                    </div>
                    <div class="campo">
                        <label>ID Cliente:</label>
                        <span>#<?= $cliente->id ?></span>
                    </div>
                    <?php if (!empty($cliente->email)): ?>
                    <div class="campo">
                        <label>Email:</label>
                        <span><?= esc($cliente->email) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($cliente->telefono)): ?>
                    <div class="campo">
                        <label>Teléfono:</label>
                        <span><?= esc($cliente->telefono) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="datos-transaccion">
                    <div class="section-title">Control Interno</div>
                    <?= $this->renderSection('datos_especificos_admin') ?>
                </div>
            </div>
            
            <div class="monto-principal">
                <div class="monto-numero"><?= $monto_formateado ?></div>
                <div class="monto-letras">(<?= $monto_letras ?>)</div>
                <div style="font-size: 10px; margin-top: 5px; font-weight: bold;">
                    <?= esc($concepto_principal ?? 'PAGO DE SERVICIOS INMOBILIARIOS') ?>
                </div>
            </div>
            
            <div class="detalles-pago">
                <div class="detalle-item">
                    <strong>FORMA DE PAGO</strong>
                    <span><?= esc($metodo_pago ?? 'Efectivo') ?></span>
                </div>
                <div class="detalle-item">
                    <strong>REFERENCIA</strong>
                    <span><?= esc(($referencia && $referencia !== '0') ? $referencia : 'N/A') ?></span>
                </div>
                <div class="detalle-item">
                    <strong>ATENDIÓ</strong>
                    <span><?= esc($vendedor->nombre_completo ?? $vendedor->username ?? 'N/A') ?></span>
                </div>
            </div>
            
            <div class="observaciones">
                <strong>NOTAS ADMINISTRATIVAS:</strong>
                <?= $this->renderSection('observaciones_admin') ?>
            </div>
            
            <div class="footer-recibo">
                <div class="firma-section">
                    <div class="firma-linea"></div>
                    <div class="firma-texto">Recibido por</div>
                </div>
                <div class="firma-section">
                    <div class="firma-linea"></div>
                    <div class="firma-texto">Autorizado por</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Controles de impresión (no se imprimen) -->
    <div class="no-print" style="text-align: center; padding: 15px; background: #f8f9fa;">
        <button onclick="window.print()" style="background: #000; color: white; padding: 8px 15px; border: none; cursor: pointer; margin-right: 8px;">
            🖨️ Imprimir Recibo
        </button>
        <button onclick="window.close()" style="background: #666; color: white; padding: 8px 15px; border: none; cursor: pointer;">
            ✖️ Cerrar
        </button>
    </div>
    
    <script>
        // Auto-imprimir al cargar la página
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>