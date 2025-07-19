# FASE 6: Generación de Documentos

## Objetivo
Implementar la generación automática de documentos (recibos, estados de cuenta, contratos) integrado con el flujo de ventas, siguiendo los documentos del sistema legacy.

## Análisis del Sistema Legacy

### Documentos Generados:
- **Recibo de Apartado**: Para pagos de enganche
- **Recibo de Venta**: Para el contrato final
- **Recibo de Capital**: Para abonos adicionales
- **Estado de Cuenta**: Resumen de pagos y pendientes

### Archivos Legacy Identificados:
- `ventas_recibo_apartado.php`: Recibo de apartado
- `ventas_recibo_venta.php`: Recibo de venta
- `ventas_recibo_capital.php`: Recibo de abono
- `ventas_estado_cuenta.php`: Estado de cuenta

## Estado Actual del Sistema

### Servicios Existentes:
- ✅ `PdfService.php` - Generación de PDF con dompdf
- ✅ `DocumentoService.php` - Gestión de documentos

### Funcionalidad Existente:
- ✅ Generación de presupuestos en PDF
- ✅ Envío de documentos por email
- ✅ Plantillas básicas de documentos

### Funcionalidad Faltante:
- ❌ Generación automática de recibos por pago
- ❌ Plantillas de documentos específicos de ventas
- ❌ Estado de cuenta dinámico
- ❌ Integración con flujo de pagos

## SUBTAREAS FASE 6

### 6.1 Crear Servicio de Generación de Recibos
**Prioridad**: ALTA
**Tiempo estimado**: 3 horas

**Acciones**:
- Crear `ReciboGeneradorService.php`
- Implementar generación automática por tipo de pago
- Integrar con `PdfService` existente

**Archivo a crear**: `app/Services/ReciboGeneradorService.php`

**Métodos principales**:
```php
class ReciboGeneradorService
{
    public function generarReciboApartado(Venta $venta, VentaIngreso $ingreso): array
    public function generarReciboVenta(Venta $venta): array
    public function generarReciboCapital(Venta $venta, VentaIngreso $ingreso): array
    public function generarReciboMensualidad(Venta $venta, VentaIngreso $ingreso): array
    public function determinarTipoRecibo(VentaIngreso $ingreso): string
}
```

**Lógica de generación**:
```php
public function generarReciboApartado(Venta $venta, VentaIngreso $ingreso): array
{
    $datos = [
        'venta' => $venta,
        'ingreso' => $ingreso,
        'tipo_recibo' => 'apartado',
        'folio' => $this->generarFolioRecibo('APT'),
        'fecha' => date('Y-m-d H:i:s'),
        'concepto' => 'Pago de Apartado',
        'observaciones' => 'Pago inicial para apartar lote'
    ];
    
    $pdfContent = $this->pdfService->generarReciboApartado($datos);
    
    return [
        'pdf' => $pdfContent,
        'datos' => $datos,
        'filename' => "recibo_apartado_{$venta->folio}.pdf"
    ];
}
```

### 6.2 Crear Plantillas de Documentos
**Prioridad**: ALTA
**Tiempo estimado**: 4 horas

**Acciones**:
- Crear plantillas PHP para cada tipo de documento
- Diseñar layout responsivo y compatible con PDF
- Integrar con datos de empresa y proyecto

**Archivos a crear**:
- `app/Views/documentos/recibo_apartado.php`
- `app/Views/documentos/recibo_venta.php`
- `app/Views/documentos/recibo_capital.php`
- `app/Views/documentos/estado_cuenta.php`

**Plantilla base**:
```php
<!-- app/Views/documentos/recibo_apartado.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo de Apartado - <?= $venta->folio ?></title>
    <style>
        @page { size: letter portrait; margin: 0.75in; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; }
        .info-empresa { margin-bottom: 20px; }
        .info-cliente { margin-bottom: 15px; }
        .tabla-pago { width: 100%; border-collapse: collapse; }
        .tabla-pago th, .tabla-pago td { border: 1px solid #000; padding: 8px; }
        .footer { margin-top: 30px; font-size: 10px; }
    </style>
</head>
<body>
    <!-- Header con logo y datos empresa -->
    <div class=\"header\">
        <img src=\"<?= base_url('assets/img/logo.png') ?>\" class=\"logo\" alt=\"Logo\">
        <h2><?= $empresa->razon_social ?></h2>
        <p><?= $empresa->direccion ?></p>
        <p>Tel: <?= $empresa->telefono ?> | Email: <?= $empresa->email ?></p>
    </div>\n    \n    <!-- Información del recibo -->\n    <div class=\"info-recibo\">\n        <h3>RECIBO DE APARTADO</h3>\n        <p><strong>Folio:</strong> <?= $datos['folio'] ?></p>\n        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($datos['fecha'])) ?></p>\n    </div>\n    \n    <!-- Información del cliente -->\n    <div class=\"info-cliente\">\n        <p><strong>Cliente:</strong> <?= $cliente->nombre_completo ?></p>\n        <p><strong>Lote:</strong> <?= $lote->numero ?> <strong>Manzana:</strong> <?= $manzana->nombre ?></p>\n        <p><strong>Proyecto:</strong> <?= $proyecto->nombre ?></p>\n    </div>\n    \n    <!-- Tabla de pago -->\n    <table class=\"tabla-pago\">\n        <thead>\n            <tr>\n                <th>Concepto</th>\n                <th>Monto</th>\n            </tr>\n        </thead>\n        <tbody>\n            <tr>\n                <td><?= $datos['concepto'] ?></td>\n                <td>$<?= number_format($ingreso->total, 2) ?></td>\n            </tr>\n        </tbody>\n    </table>\n    \n    <!-- Formas de pago -->\n    <div class=\"formas-pago\">\n        <h4>Formas de Pago:</h4>\n        <?php if ($ingreso->efectivo > 0): ?>\n            <p>Efectivo: $<?= number_format($ingreso->efectivo, 2) ?></p>\n        <?php endif; ?>\n        <?php if ($ingreso->transferencia > 0): ?>\n            <p>Transferencia: $<?= number_format($ingreso->transferencia, 2) ?></p>\n        <?php endif; ?>\n        <?php if ($ingreso->cheque > 0): ?>\n            <p>Cheque: $<?= number_format($ingreso->cheque, 2) ?></p>\n        <?php endif; ?>\n        <?php if ($ingreso->tarjeta > 0): ?>\n            <p>Tarjeta: $<?= number_format($ingreso->tarjeta, 2) ?></p>\n        <?php endif; ?>\n    </div>\n    \n    <!-- Footer -->\n    <div class=\"footer\">\n        <p>Este recibo es válido únicamente con el comprobante bancario correspondiente.</p>\n        <p>Documento generado automáticamente el <?= date('d/m/Y H:i:s') ?></p>\n    </div>\n</body>\n</html>\n```\n\n### 6.3 Integrar con Flujo de Pagos\n**Prioridad**: ALTA\n**Tiempo estimado**: 2 horas\n\n**Acciones**:\n- Modificar `PagosIngresoService` para generar recibos automáticamente\n- Integrar con `ReciboGeneradorService`\n- Guardar documentos generados\n\n**Código de integración**:\n```php\n// En PagosIngresoService\npublic function procesarPago(Venta $venta, array $datosPago): array\n{\n    // 1. Procesar pago (existente)\n    $resultado = $this->procesarPagoExistente($venta, $datosPago);\n    \n    // 2. Generar recibo automáticamente\n    $reciboGenerator = new ReciboGeneradorService();\n    $recibo = $reciboGenerator->generarReciboPorTipo($venta, $resultado['ingreso']);\n    \n    // 3. Guardar documento\n    $this->documentoService->guardarDocumento([\n        'venta_id' => $venta->id,\n        'tipo_documento' => 'recibo',\n        'nombre_archivo' => $recibo['filename'],\n        'contenido' => $recibo['pdf'],\n        'fecha_generacion' => date('Y-m-d H:i:s')\n    ]);\n    \n    return array_merge($resultado, ['recibo' => $recibo]);\n}\n```\n\n### 6.4 Crear Servicio de Estado de Cuenta\n**Prioridad**: ALTA\n**Tiempo estimado**: 2.5 horas\n\n**Acciones**:\n- Crear `EstadoCuentaService.php`\n- Implementar generación dinámica de estado de cuenta\n- Integrar datos de cobranza y pagos\n\n**Archivo a crear**: `app/Services/EstadoCuentaService.php`\n\n**Métodos principales**:\n```php\nclass EstadoCuentaService\n{\n    public function generarEstadoCuenta(Venta $venta): array\n    public function obtenerDatosEstadoCuenta(Venta $venta): array\n    public function calcularResumenFinanciero(Venta $venta): array\n    public function obtenerHistorialPagos(Venta $venta): array\n    public function obtenerPagosPendientes(Venta $venta): array\n}\n```\n\n**Datos del estado de cuenta**:\n```php\npublic function obtenerDatosEstadoCuenta(Venta $venta): array\n{\n    return [\n        'venta' => $venta,\n        'cliente' => $this->obtenerDatosCliente($venta),\n        'lote' => $this->obtenerDatosLote($venta),\n        'proyecto' => $this->obtenerDatosProyecto($venta),\n        'resumen_financiero' => $this->calcularResumenFinanciero($venta),\n        'historial_pagos' => $this->obtenerHistorialPagos($venta),\n        'pagos_pendientes' => $this->obtenerPagosPendientes($venta),\n        'proxima_cuota' => $this->obtenerProximaCuota($venta),\n        'fecha_generacion' => date('Y-m-d H:i:s')\n    ];\n}\n```\n\n### 6.5 Implementar Envío Automático de Documentos\n**Prioridad**: MEDIA\n**Tiempo estimado**: 1.5 horas\n\n**Acciones**:\n- Crear `DocumentoNotificacionService.php`\n- Implementar envío automático por email\n- Integrar con configuración de email existente\n\n**Archivo a crear**: `app/Services/DocumentoNotificacionService.php`\n\n**Métodos principales**:\n```php\nclass DocumentoNotificacionService\n{\n    public function enviarReciboEmail(Venta $venta, array $recibo): bool\n    public function enviarEstadoCuentaEmail(Venta $venta): bool\n    public function configurarEnvioAutomatico(Venta $venta, array $configuracion): void\n    public function obtenerTemplateEmail(string $tipoDocumento): string\n}\n```\n\n### 6.6 Crear Controlador de Documentos\n**Prioridad**: MEDIA\n**Tiempo estimado**: 1 hora\n\n**Acciones**:\n- Crear `AdminDocumentosVentaController.php`\n- Implementar endpoints para generación manual\n- Integrar con sistema de permisos\n\n**Archivo a crear**: `app/Controllers/Admin/AdminDocumentosVentaController.php`\n\n**Métodos principales**:\n```php\nclass AdminDocumentosVentaController extends BaseController\n{\n    public function generarReciboApartado($ventaId)\n    public function generarReciboVenta($ventaId)\n    public function generarEstadoCuenta($ventaId)\n    public function reenviarDocumento($ventaId, $tipoDocumento)\n    public function descargarDocumento($ventaId, $tipoDocumento)\n}\n```\n\n## ARCHIVOS A CREAR\n\n### Nuevos Servicios:\n- ✅ `app/Services/ReciboGeneradorService.php`\n- ✅ `app/Services/EstadoCuentaService.php`\n- ✅ `app/Services/DocumentoNotificacionService.php`\n\n### Nuevas Plantillas:\n- ✅ `app/Views/documentos/recibo_apartado.php`\n- ✅ `app/Views/documentos/recibo_venta.php`\n- ✅ `app/Views/documentos/recibo_capital.php`\n- ✅ `app/Views/documentos/estado_cuenta.php`\n\n### Nuevos Controllers:\n- ✅ `app/Controllers/Admin/AdminDocumentosVentaController.php`\n\n### Nuevos Models:\n- ✅ `app/Models/DocumentoVentaModel.php`\n\n## ARCHIVOS A ACTUALIZAR\n\n### Servicios:\n- 🔄 `app/Services/PagosIngresoService.php`\n- 🔄 `app/Services/PdfService.php`\n- 🔄 `app/Services/DocumentoService.php`\n\n### Controllers:\n- 🔄 `app/Controllers/Admin/AdminVentasController.php`\n- 🔄 `app/Controllers/Admin/AdminPagosController.php`\n\n## FLUJO DE DOCUMENTOS IMPLEMENTADO\n\n### 1. Pago de Apartado:\n```\nPago procesado\n→ ReciboGeneradorService::generarReciboApartado()\n→ PdfService::generarPdf()\n→ DocumentoService::guardarDocumento()\n→ DocumentoNotificacionService::enviarReciboEmail()\n```\n\n### 2. Confirmación de Venta:\n```\nVenta confirmada\n→ ReciboGeneradorService::generarReciboVenta()\n→ DocumentoService::guardarDocumento()\n→ DocumentoNotificacionService::enviarReciboEmail()\n```\n\n### 3. Estado de Cuenta:\n```\nSolicitud de estado\n→ EstadoCuentaService::generarEstadoCuenta()\n→ PdfService::generarPdf()\n→ DocumentoNotificacionService::enviarEstadoCuentaEmail()\n```\n\n## COMPATIBILIDAD CON SISTEMA LEGACY\n\n### Documentos Equivalentes:\n- `recibo_apartado.php` → `ReciboGeneradorService::generarReciboApartado()`\n- `recibo_venta.php` → `ReciboGeneradorService::generarReciboVenta()`\n- `recibo_capital.php` → `ReciboGeneradorService::generarReciboCapital()`\n- `estado_cuenta.php` → `EstadoCuentaService::generarEstadoCuenta()`\n\n### Estructura de Datos Compatible:\n```php\n// Datos de recibo compatibles con legacy\n[\n    'folio' => 'APT-000001',\n    'fecha' => '2024-08-15 10:30:00',\n    'cliente' => 'Juan Pérez',\n    'lote' => 'Lote 15 Manzana A',\n    'proyecto' => 'Residencial Los Pinos',\n    'total' => 25000.00,\n    'efectivo' => 15000.00,\n    'transferencia' => 10000.00,\n    'concepto' => 'Pago de Apartado'\n]\n```\n\n## PRUEBAS REQUERIDAS\n\n### 1. Pruebas de Generación:\n- Recibos se generan correctamente por tipo\n- PDF se genera sin errores\n- Plantillas renderizan datos correctamente\n\n### 2. Pruebas de Integración:\n- Documentos se generan automáticamente en pagos\n- Email se envía correctamente\n- Documentos se guardan en sistema\n\n### 3. Pruebas de Compatibilidad:\n- Documentos son compatibles con legacy\n- Datos se mapean correctamente\n- Formato es consistente\n\n## BENEFICIOS ESPERADOS\n\n### Técnicos:\n- Generación automática de documentos\n- Plantillas reutilizables y mantenibles\n- Integración completa con flujo de pagos\n\n### Negocio:\n- Documentación automática de todas las transacciones\n- Envío inmediato de comprobantes\n- Mejor experiencia del cliente\n\n## CRITERIOS DE ACEPTACIÓN\n\n### ✅ Funcionalidad:\n- Documentos se generan automáticamente\n- Envío por email funciona correctamente\n- Plantillas son profesionales y completas\n\n### ✅ Integración:\n- Integración con pagos funciona\n- Datos se obtienen correctamente\n- PDF se genera sin errores\n\n### ✅ Usabilidad:\n- Interfaz administrativa para documentos\n- Regeneración manual disponible\n- Descarga de documentos funciona\n\n---\n\n**DEPENDENCIAS**: Fase 5 completada\n**TIEMPO TOTAL ESTIMADO**: 14 horas\n**SIGUIENTES FASES**: Fase 7 - Reportes y dashboard