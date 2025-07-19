<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Venta - <?= $pago->folio ?></title>
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
            width: 21cm;
            margin: 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .empresa-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .recibo-title {
            font-size: 16px;
            font-weight: bold;
            color: #0066cc;
            margin-top: 10px;
        }
        
        .section {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #0066cc;
        }
        
        .row {
            margin-bottom: 5px;
            display: flex;
        }
        
        .label {
            font-weight: bold;
            width: 120px;
            color: #666;
        }
        
        .value {
            flex: 1;
        }
        
        .amount {
            font-size: 16px;
            font-weight: bold;
            color: #006600;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        @media print {
            body { background: white; }
            .recibo-container { 
                box-shadow: none; 
                margin: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="recibo-container">
        <!-- Header -->
        <div class="header">
            <div class="empresa-name"><?= $empresa->nombre ?></div>
            <div><?= $empresa->direccion ?></div>
            <div>Tel: <?= $empresa->telefono ?> | RFC: <?= $empresa->rfc ?></div>
            <div class="recibo-title">RECIBO DE PAGO - VENTA</div>
        </div>

        <!-- Datos del Recibo -->
        <div class="section">
            <div class="section-title">Datos del Recibo</div>
            <div class="row">
                <span class="label">Folio:</span>
                <span class="value"><?= $pago->folio ?></span>
            </div>
            <div class="row">
                <span class="label">Fecha:</span>
                <span class="value"><?= date('d/m/Y', strtotime($pago->fecha)) ?></span>
            </div>
            <div class="row">
                <span class="label">Concepto:</span>
                <span class="value"><?= $pago->concepto ?></span>
            </div>
        </div>

        <!-- Datos del Cliente -->
        <div class="section">
            <div class="section-title">Datos del Cliente</div>
            <div class="row">
                <span class="label">Nombre:</span>
                <span class="value"><?= trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) ?></span>
            </div>
            <?php if (!empty($cliente->rfc)): ?>
            <div class="row">
                <span class="label">RFC:</span>
                <span class="value"><?= $cliente->rfc ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($cliente->telefono)): ?>
            <div class="row">
                <span class="label">Teléfono:</span>
                <span class="value"><?= $cliente->telefono ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Datos del Lote -->
        <div class="section">
            <div class="section-title">Datos del Lote</div>
            <div class="row">
                <span class="label">Clave:</span>
                <span class="value"><?= $lote->clave ?></span>
            </div>
            <div class="row">
                <span class="label">Área:</span>
                <span class="value"><?= number_format($lote->area, 2) ?> m²</span>
            </div>
            <div class="row">
                <span class="label">Precio/m²:</span>
                <span class="value"><?= formatPrecio($lote->precio_m2) ?></span>
            </div>
            <div class="row">
                <span class="label">Precio Total:</span>
                <span class="value"><?= formatPrecio($lote->precio_total) ?></span>
            </div>
        </div>

        <!-- Datos del Pago -->
        <div class="section">
            <div class="section-title">Datos del Pago</div>
            <div class="row">
                <span class="label">Método:</span>
                <span class="value"><?= $pago->metodo_pago ?></span>
            </div>
            <?php if (!empty($pago->referencia)): ?>
            <div class="row">
                <span class="label">Referencia:</span>
                <span class="value"><?= $pago->referencia ?></span>
            </div>
            <?php endif; ?>
            <div class="row">
                <span class="label">Monto:</span>
                <span class="value amount"><?= formatPrecio($pago->monto) ?></span>
            </div>
        </div>

        <!-- Datos del Vendedor -->
        <div class="section">
            <div class="section-title">Vendedor</div>
            <div class="row">
                <span class="label">Nombre:</span>
                <span class="value"><?= $vendedor->username ?? 'Sin asignar' ?></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este recibo fue generado automáticamente por el sistema ANVAR</p>
            <p>Fecha de generación: <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>

    <script>
        // Auto-imprimir al cargar
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>