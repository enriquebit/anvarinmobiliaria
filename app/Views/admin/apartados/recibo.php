<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Apartado - <?= $folio ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #333;
            background: #f5f5f5;
        }
        
        .recibo-container {
            width: 21cm; /* Tamaño carta */
            height: 27.9cm;
            margin: 0 auto;
            background: white;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .recibo-half {
            flex: 1;
            padding: 15px;
            border-bottom: 2px dashed #666;
            position: relative;
        }
        
        .recibo-half:last-child {
            border-bottom: none;
        }
        
        .recibo-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .empresa-info h1 {
            color: #1a1360;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .empresa-info p {
            font-size: 10px;
            color: #666;
            margin-bottom: 2px;
        }
        
        .recibo-info {
            text-align: right;
        }
        
        .folio {
            background: #1a1360;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .fecha {
            margin-top: 5px;
            font-size: 10px;
            color: #666;
        }
        
        .recibo-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .datos-cliente, .datos-apartado {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            background: #1a1360;
            color: white;
            padding: 5px 10px;
            margin: -12px -12px 10px -12px;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
            font-size: 11px;
        }
        
        .campo {
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
        }
        
        .campo label {
            font-weight: bold;
            color: #555;
            font-size: 10px;
        }
        
        .campo span {
            font-size: 10px;
            color: #333;
        }
        
        .monto-principal {
            background: #07c15b;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 15px 0;
        }
        
        .monto-numero {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .monto-letras {
            font-size: 11px;
            font-style: italic;
        }
        
        .detalles-pago {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        
        .detalle-item {
            text-align: center;
            padding: 8px;
            background: #f1f3f4;
            border-radius: 5px;
        }
        
        .detalle-item strong {
            display: block;
            color: #1a1360;
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .detalle-item span {
            font-size: 10px;
            color: #666;
        }
        
        .observaciones {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .observaciones strong {
            color: #856404;
            font-size: 10px;
        }
        
        .observaciones p {
            font-size: 9px;
            color: #856404;
            margin-top: 5px;
        }
        
        .footer-recibo {
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .firma-section {
            text-align: center;
            flex: 1;
        }
        
        .firma-linea {
            border-top: 1px solid #333;
            margin: 25px 20px 5px 20px;
        }
        
        .firma-texto {
            font-size: 9px;
            color: #666;
        }
        
        .tipo-copia {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .tipo-copia.cliente {
            background: #07c15b;
        }
        
        @media print {
            body {
                background: white;
            }
            
            .recibo-container {
                box-shadow: none;
                margin: 0;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        @media screen and (max-width: 768px) {
            .recibo-container {
                width: 100%;
                margin: 10px;
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
                    <h1><?= $empresa['nombre'] ?></h1>
                    <p><?= $empresa['direccion'] ?></p>
                    <p>Tel: <?= $empresa['telefono'] ?> | <?= $empresa['email'] ?></p>
                </div>
                <div class="recibo-info">
                    <div class="folio">RECIBO <?= $folio ?></div>
                    <div class="fecha"><?= $fecha_formateada ?></div>
                </div>
            </div>
            
            <div class="recibo-body">
                <div class="datos-cliente">
                    <div class="section-title">DATOS DEL CLIENTE</div>
                    <div class="campo">
                        <label>Nombre:</label>
                        <span><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . ($cliente->apellido_materno ?? '')) ?></span>
                    </div>
                    <div class="campo">
                        <label>Email:</label>
                        <span><?= esc($cliente->email) ?></span>
                    </div>
                    <div class="campo">
                        <label>Teléfono:</label>
                        <span><?= esc($cliente->telefono) ?></span>
                    </div>
                </div>
                
                <div class="datos-apartado">
                    <div class="section-title">DATOS DEL APARTADO</div>
                    <div class="campo">
                        <label>Folio Apartado:</label>
                        <span><?= esc($apartado->folio_apartado) ?></span>
                    </div>
                    <div class="campo">
                        <label>Lote:</label>
                        <span><?= esc($lote->clave ?? 'N/A') ?> - <?= esc($lote->area ?? 0) ?>m²</span>
                    </div>
                    <div class="campo">
                        <label>Fecha Apartado:</label>
                        <span><?= date('d/m/Y', strtotime($apartado->fecha_apartado)) ?></span>
                    </div>
                    <div class="campo">
                        <label>Límite Enganche:</label>
                        <span><?= formatear_fecha_espanol($apartado->fecha_limite_enganche) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="monto-principal">
                <div class="monto-numero"><?= $monto_formateado ?></div>
                <div class="monto-letras"><?= $monto_letras ?></div>
            </div>
            
            <div class="detalles-pago">
                <div class="detalle-item">
                    <strong>FORMA DE PAGO</strong>
                    <span><?= $metodo_pago ?></span>
                </div>
                <div class="detalle-item">
                    <strong>REFERENCIA</strong>
                    <span><?= esc($referencia ?: 'N/A') ?></span>
                </div>
                <div class="detalle-item">
                    <strong>ATENDIÓ</strong>
                    <span><?= esc($vendedor->username ?? 'N/A') ?></span>
                </div>
            </div>
            
            <div class="observaciones">
                <strong>IMPORTANTE:</strong>
                <p>• Este apartado tiene vigencia hasta el <?= formatear_fecha_espanol($apartado->fecha_limite_enganche) ?></p>
                <p>• Para conservar el lote debe liquidar el enganche de <?= formatear_moneda_mexicana($apartado->monto_enganche_requerido) ?></p>
                <p>• Conserve este recibo como comprobante de pago</p>
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
                    <h1><?= $empresa['nombre'] ?></h1>
                    <p><?= $empresa['direccion'] ?></p>
                    <p>Tel: <?= $empresa['telefono'] ?> | <?= $empresa['email'] ?></p>
                </div>
                <div class="recibo-info">
                    <div class="folio">RECIBO <?= $folio ?></div>
                    <div class="fecha"><?= $fecha_formateada ?></div>
                </div>
            </div>
            
            <div class="recibo-body">
                <div class="datos-cliente">
                    <div class="section-title">DATOS DEL CLIENTE</div>
                    <div class="campo">
                        <label>Nombre:</label>
                        <span><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . ($cliente->apellido_materno ?? '')) ?></span>
                    </div>
                    <div class="campo">
                        <label>Email:</label>
                        <span><?= esc($cliente->email) ?></span>
                    </div>
                    <div class="campo">
                        <label>Teléfono:</label>
                        <span><?= esc($cliente->telefono) ?></span>
                    </div>
                    <div class="campo">
                        <label>ID Cliente:</label>
                        <span>#<?= $cliente->id ?></span>
                    </div>
                </div>
                
                <div class="datos-apartado">
                    <div class="section-title">CONTROL INTERNO</div>
                    <div class="campo">
                        <label>ID Apartado:</label>
                        <span>#<?= $apartado->id ?></span>
                    </div>
                    <div class="campo">
                        <label>ID Ingreso:</label>
                        <span>#<?= $ingreso->id ?? 'N/A' ?></span>
                    </div>
                    <div class="campo">
                        <label>Plan:</label>
                        <span><?= esc($configuracion->nombre ?? 'N/A') ?></span>
                    </div>
                    <div class="campo">
                        <label>User ID:</label>
                        <span>#<?= $vendedor->id ?? 'N/A' ?></span>
                    </div>
                </div>
            </div>
            
            <div class="monto-principal">
                <div class="monto-numero"><?= $monto_formateado ?></div>
                <div class="monto-letras"><?= $monto_letras ?></div>
            </div>
            
            <div class="detalles-pago">
                <div class="detalle-item">
                    <strong>FORMA DE PAGO</strong>
                    <span><?= $metodo_pago ?></span>
                </div>
                <div class="detalle-item">
                    <strong>REFERENCIA</strong>
                    <span><?= esc($referencia ?: 'N/A') ?></span>
                </div>
                <div class="detalle-item">
                    <strong>ATENDIÓ</strong>
                    <span><?= esc($vendedor->username ?? 'N/A') ?></span>
                </div>
            </div>
            
            <div class="observaciones">
                <strong>NOTAS ADMINISTRATIVAS:</strong>
                <p>• Enganche requerido: <?= formatear_moneda_mexicana($apartado->monto_enganche_requerido) ?></p>
                <p>• Plazo liquidación: <?= formatear_fecha_espanol($apartado->fecha_limite_enganche) ?></p>
                <p>• Configuración: <?= esc($configuracion->nombre ?? 'N/A') ?></p>
                <p>• Observaciones: <?= esc($apartado->observaciones ?: 'Ninguna') ?></p>
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
    <div class="no-print" style="text-align: center; padding: 20px; background: #f8f9fa;">
        <button onclick="window.print()" style="background: #1a1360; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            <i class="fas fa-print"></i> Imprimir Recibo
        </button>
        <button onclick="window.close()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>
</body>
</html>