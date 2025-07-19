<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================================
// RUTAS PRINCIPALES DEL SISTEMA
// =====================================================================

// Manejar assets faltantes (favicon, imágenes, etc.) - Evitar 404 en logs
// COMENTADO: Esta ruta interfiere con assets válidos como DataTables
// $routes->get('assets/(:any)', function($asset) {
//     throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
// });

// Manejar rutas especiales de Chrome DevTools y otras herramientas
$routes->get('.well-known/(:any)', function() {
    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
});

// Ruta por defecto
$routes->get('/', 'DashboardController::index', ['filter' => 'login']);

// Redirección para admin sin ruta específica
$routes->get('admin', 'Admin\DashboardController::index', ['filter' => 'admin']);
$routes->get('admin/', 'Admin\DashboardController::index', ['filter' => 'admin']);

// Registro
$routes->get('register', 'Auth\RegisterController::index');
$routes->post('register', 'Auth\RegisterController::attemptRegister', ['priority' => 1]);
$routes->post('register/check-email', 'Auth\RegisterController::checkEmail');

// Dashboard que redirige según rol
$routes->get('/dashboard', 'DashboardController::index', ['filter' => 'login']);

// =====================================================================
// SISTEMA DE DOCUMENTOS SEGUROS - RUTAS UNIVERSALES
// =====================================================================

// Rutas principales para ver y descargar documentos
$routes->get('documento/ver/(:segment)/(:num)', 'SecureFileController::verDocumento/$1/$2', ['filter' => 'login']);
$routes->get('documento/descargar/(:segment)/(:num)', 'SecureFileController::descargarDocumento/$1/$2', ['filter' => 'login']);

// Compatibilidad con rutas anteriores
$routes->get('secure/avatar/(:segment)', 'SecureFileController::avatar/$1', ['filter' => 'login']);
$routes->get('secure/staff/(:segment)', 'SecureFileController::staff/$1', ['filter' => 'login']);

// =====================================================================
// RUTAS PÚBLICAS - REGISTRO DE CLIENTES (SIN AUTENTICACIÓN)
// =====================================================================

// Módulo de registro público de clientes con integración HubSpot y Google Drive
$routes->group('registro-clientes', function($routes) {
    // Vista principal del formulario
    $routes->get('/', 'Public\RegistroClientesController::index');                        // GET /registro-clientes - Formulario público
    
    // Procesamiento del registro
    $routes->post('procesar', 'Public\RegistroClientesController::procesar');             // POST /registro-clientes/procesar - Procesar registro completo
    
    // APIs para el frontend
    $routes->post('validar-paso1', 'Public\RegistroClientesController::validarPaso1');    // POST /registro-clientes/validar-paso1 - Validar datos personales
    $routes->post('subir-documentos', 'Public\RegistroClientesController::subirDocumentos'); // POST /registro-clientes/subir-documentos - Subir archivos
    
    // FASE 1: APIs para filtrado de lotes por desarrollo
    $routes->get('obtener-desarrollos', 'Public\RegistroClientesController::obtenerDesarrollosDisponibles'); // GET /registro-clientes/obtener-desarrollos - Lista de desarrollos
    $routes->post('obtener-lotes', 'Public\RegistroClientesController::obtenerLotesPorDesarrollo');           // POST /registro-clientes/obtener-lotes - Lotes por desarrollo
    $routes->get('estadisticas-desarrollo', 'Public\RegistroClientesController::obtenerEstadisticasDesarrollo'); // GET /registro-clientes/estadisticas-desarrollo - Stats por desarrollo
});

// API para logs de debugging desde el frontend
$routes->post('api/debug-log', 'Public\RegistroClientesController::debugLog');           // POST /api/debug-log - Logs desde JS

// FASE 4: Validación del sistema (solo desarrollo)
$routes->get('api/validar-sistema', 'Public\RegistroClientesController::validarSistema'); // GET /api/validar-sistema - Debug completo

// =====================================================================
// RUTAS DE CLIENTES - SOLO CLIENTES AUTENTICADOS (CON FILTRO)
// =====================================================================

$routes->group('cliente', ['filter' => 'cliente'], function($routes) {
    $routes->get('dashboard', 'Cliente\DashboardController::index');
    
    // Redirección para /cliente/perfil
    $routes->get('perfil', function() {
        return redirect()->to('/cliente/mi-perfil');
    });
    
    // ===== MÓDULO MI-PERFIL (CLIENTE) =====
    $routes->group('mi-perfil', function($routes) {
        $routes->get('/', 'Cliente\MiPerfilController::index');                                // GET /cliente/mi-perfil - Vista principal
        $routes->post('actualizar-info', 'Cliente\MiPerfilController::actualizarInfo');       // POST /cliente/mi-perfil/actualizar-info - AJAX
        $routes->post('subir-foto-perfil', 'Cliente\MiPerfilController::subirFotoPerfil');    // POST /cliente/mi-perfil/subir-foto-perfil - AJAX
        $routes->post('subir-documento', 'Cliente\MiPerfilController::subirDocumento');       // POST /cliente/mi-perfil/subir-documento - AJAX
        $routes->post('cambiar-password', 'Cliente\MiPerfilController::cambiarPassword');     // POST /cliente/mi-perfil/cambiar-password - AJAX
        $routes->post('eliminar-documento/(:num)', 'Cliente\MiPerfilController::eliminarDocumento/$1'); // POST /cliente/mi-perfil/eliminar-documento/123 - AJAX
    });
    
    // ===== MÓDULO ESTADO DE CUENTA (CLIENTE) =====
    $routes->group('estado-cuenta', function($routes) {
        // Rutas principales del portal cliente
        $routes->get('/', 'Cliente\ClienteEstadoCuentaController::index');                     // GET /cliente/estado-cuenta - Dashboard financiero
        $routes->get('propiedad/(:num)', 'Cliente\ClienteEstadoCuentaController::propiedad/$1'); // GET /cliente/estado-cuenta/propiedad/123 - Detalle específico
        $routes->get('propiedad', 'Cliente\ClienteEstadoCuentaController::propiedad');         // GET /cliente/estado-cuenta/propiedad - Sin ID (primera propiedad)
        $routes->get('historialPagos', 'Cliente\ClienteEstadoCuentaController::historialPagos'); // GET /cliente/estado-cuenta/historialPagos - Historia completa
        $routes->get('proximosVencimientos', 'Cliente\ClienteEstadoCuentaController::proximosVencimientos'); // GET /cliente/estado-cuenta/proximosVencimientos - Calendar view
        
        // Rutas para descarga y exportación
        $routes->get('descargarPDF', 'Cliente\ClienteEstadoCuentaController::descargarPDF');   // GET /cliente/estado-cuenta/descargarPDF - PDF estado general
        $routes->get('descargarPDF/(:num)', 'Cliente\ClienteEstadoCuentaController::descargarPDF/$1'); // GET /cliente/estado-cuenta/descargarPDF/123 - PDF propiedad específica
        
        // Rutas AJAX para widgets dinámicos
        $routes->get('widgetResumenFinanciero', 'Cliente\ClienteEstadoCuentaController::widgetResumenFinanciero'); // GET /cliente/estado-cuenta/widgetResumenFinanciero - AJAX Widget
        $routes->get('widgetAlertas', 'Cliente\ClienteEstadoCuentaController::widgetAlertas'); // GET /cliente/estado-cuenta/widgetAlertas - AJAX Widget alertas
        $routes->get('widgetProximosPagos', 'Cliente\ClienteEstadoCuentaController::widgetProximosPagos'); // GET /cliente/estado-cuenta/widgetProximosPagos - AJAX Widget próximos
    });
    
    // ===== MÓDULO DE PAGOS (CLIENTE) =====
    $routes->group('pagos', function($routes) {
        // Rutas principales de gestión de pagos
        $routes->get('/', 'Cliente\ClientePagosController::index');                            // GET /cliente/pagos - Dashboard de pagos
        $routes->get('mensualidad/(:num)', 'Cliente\ClientePagosController::mensualidad/$1');  // GET /cliente/pagos/mensualidad/123 - Detalle mensualidad específica
        $routes->get('mensualidad', 'Cliente\ClientePagosController::mensualidad');            // GET /cliente/pagos/mensualidad - Próxima mensualidad
        
        // Rutas para gestión de comprobantes y reportes
        $routes->get('comprobante/(:num)', 'Cliente\ClientePagosController::comprobante/$1');  // GET /cliente/pagos/comprobante/123 - Ver comprobante de pago
        $routes->get('reportarPago', 'Cliente\ClientePagosController::reportarPago');          // GET /cliente/pagos/reportarPago - Formulario reportar pago offline
        $routes->post('procesarReporte', 'Cliente\ClientePagosController::procesarReporte');   // POST /cliente/pagos/procesarReporte - Procesar reporte pago
        
        // Rutas para subida de comprobantes
        $routes->post('subirComprobante', 'Cliente\ClientePagosController::subirComprobante'); // POST /cliente/pagos/subirComprobante - AJAX Subir comprobante
        $routes->post('eliminarComprobante/(:num)', 'Cliente\ClientePagosController::eliminarComprobante/$1'); // POST /cliente/pagos/eliminarComprobante/123 - AJAX Eliminar comprobante
        
        // Rutas AJAX para operaciones dinámicas
        $routes->get('obtenerMensualidadesPendientes', 'Cliente\ClientePagosController::obtenerMensualidadesPendientes'); // GET /cliente/pagos/obtenerMensualidadesPendientes - AJAX Select mensualidades
        $routes->post('calcularMontoPago', 'Cliente\ClientePagosController::calcularMontoPago'); // POST /cliente/pagos/calcularMontoPago - AJAX Calcular monto con mora
        $routes->get('estadisticasPagos', 'Cliente\ClientePagosController::estadisticasPagos'); // GET /cliente/pagos/estadisticasPagos - AJAX Widget estadísticas
    });
});

// =====================================================================
// RUTAS ESPECÍFICAS SIN FILTRO (MAGIC LINK Y CONFIGURACIÓN DE CONTRASEÑA)
// =====================================================================

// Magic link para acceso de clientes (sin autenticación previa)
$routes->get('cliente/magic-login/(:any)', 'Cliente\MagicLoginController::login/$1');    // GET /cliente/magic-login/TOKEN - Acceso directo con token
$routes->get('cliente/bienvenida', 'Cliente\BienvenidaController::index');               // GET /cliente/bienvenida - Magic link bienvenida (nueva)
$routes->get('cliente/configurar-password', 'Cliente\BienvenidaController::configurarPassword'); // GET /cliente/configurar-password - Formulario contraseña
$routes->post('cliente/guardar-password', 'Cliente\BienvenidaController::guardarPassword'); // POST /cliente/guardar-password - Procesar contraseña

// =====================================================================
// RUTAS DE ADMINISTRACIÓN - SOLO ADMIN/SUPERADMIN
// =====================================================================

$routes->group('admin', ['filter' => 'admin'], function($routes) {
    // Dashboard administrativo
    $routes->get('dashboard', 'Admin\DashboardController::index');
    
    // ===== MÓDULO MI-PERFIL (ADMIN/STAFF) =====
    $routes->group('mi-perfil', function($routes) {
        $routes->get('/', 'Admin\MiPerfilController::index');                                // GET /admin/mi-perfil - Vista principal
        $routes->post('actualizar-info', 'Admin\MiPerfilController::actualizarInfo');       // POST /admin/mi-perfil/actualizar-info - AJAX
        $routes->post('subir-foto-perfil', 'Admin\MiPerfilController::subirFotoPerfil');    // POST /admin/mi-perfil/subir-foto-perfil - AJAX
        $routes->post('subir-documento', 'Admin\MiPerfilController::subirDocumento');       // POST /admin/mi-perfil/subir-documento - AJAX
        $routes->post('cambiar-password', 'Admin\MiPerfilController::cambiarPassword');     // POST /admin/mi-perfil/cambiar-password - AJAX
        
        // ===== GESTIÓN DE CAMBIO DE EMAIL =====
        $routes->post('solicitar-cambio-email', 'Admin\MiPerfilController::solicitarCambioEmail');   // POST /admin/mi-perfil/solicitar-cambio-email - AJAX
        $routes->get('obtener-email-actual', 'Admin\MiPerfilController::obtenerEmailActual');        // GET /admin/mi-perfil/obtener-email-actual - AJAX
        $routes->get('solicitudes-pendientes', 'Admin\MiPerfilController::obtenerSolicitudesPendientes'); // GET /admin/mi-perfil/solicitudes-pendientes - AJAX
        $routes->post('cancelar-cambio-email', 'Admin\MiPerfilController::cancelarCambioEmail');     // POST /admin/mi-perfil/cancelar-cambio-email - AJAX
        
        // DOCUMENTOS: Funcionalidad temporal con métodos stub
        $routes->post('subir-documento', 'Admin\MiPerfilController::subirDocumento');       // POST /admin/mi-perfil/subir-documento - AJAX (STUB)
        $routes->post('eliminar-documento/(:num)', 'Admin\MiPerfilController::eliminarDocumento/$1'); // POST /admin/mi-perfil/eliminar-documento/123 - AJAX (STUB)
        $routes->get('ver-documento/(:num)', 'Admin\MiPerfilController::verDocumento/$1');   // GET /admin/mi-perfil/ver-documento/123 - Ver en navegador (STUB)
        $routes->get('descargar-documento/(:num)', 'Admin\MiPerfilController::descargarDocumento/$1'); // GET /admin/mi-perfil/descargar-documento/123 - Descargar (STUB)
    });
    
    // ===== MÓDULO DE AUTENTICACIÓN GOOGLE DRIVE =====
    $routes->group('google-drive', function($routes) {
        $routes->get('authorize', 'Admin\\GoogleDriveAuthController::authorize');     // GET /admin/google-drive/authorize - Iniciar OAuth2
        $routes->get('callback', 'Admin\\GoogleDriveAuthController::callback');       // GET /admin/google-drive/callback - Callback OAuth2
        $routes->get('status', 'Admin\\GoogleDriveAuthController::status');           // GET /admin/google-drive/status - Estado de tokens
        $routes->get('test', 'Admin\\GoogleDriveAuthController::testConnection');     // GET /admin/google-drive/test - Probar conexión
        $routes->post('revoke', 'Admin\\GoogleDriveAuthController::revoke');          // POST /admin/google-drive/revoke - Revocar tokens
    });
    
    // ===== MÓDULO DE VENTAS =====
    $routes->group('ventas', function($routes) {
        $routes->get('/', 'Admin\\AdminVentasController::index');                     // GET /admin/ventas - Lista de ventas
        $routes->get('registradas', 'Admin\\AdminVentasController::registradas');     // GET /admin/ventas/registradas - Ventas registradas
        $routes->get('create', 'Admin\\AdminVentasController::create');               // GET /admin/ventas/create - Formulario nueva venta
        $routes->post('store', 'Admin\\AdminVentasController::store');                // POST /admin/ventas/store - Guardar nueva venta
        $routes->get('(:num)', 'Admin\\AdminVentasController::show/$1');              // GET /admin/ventas/123 - Ver detalle venta
        $routes->get('edit/(:num)', 'Admin\\AdminVentasController::edit/$1');         // GET /admin/ventas/edit/123 - Editar venta
        $routes->post('update/(:num)', 'Admin\\AdminVentasController::update/$1');    // POST /admin/ventas/update/123 - Actualizar venta
        $routes->get('cancelar/(:num)', 'Admin\\AdminVentasController::cancelar/$1'); // GET /admin/ventas/cancelar/123 - Formulario cancelar
        $routes->post('cancelar/(:num)', 'Admin\\AdminVentasController::cancelar/$1'); // POST /admin/ventas/cancelar/123 - Procesar cancelación
        $routes->get('datatables', 'Admin\\AdminVentasController::datatables');      // GET /admin/ventas/datatables - AJAX DataTables
        $routes->get('historial', 'Admin\\AdminVentasController::historial');        // GET /admin/ventas/historial - Historial de ventas
        $routes->get('configurar/(:num)', 'Admin\\AdminVentasController::configurar/$1'); // GET /admin/ventas/configurar/123 - Configurar venta
        $routes->match(['GET', 'POST'], 'imprimir-amortizacion', 'Admin\\AdminVentasController::imprimirAmortizacion'); // GET/POST /admin/ventas/imprimir-amortizacion - Vista impresión
        $routes->get('reportes', 'Admin\\AdminVentasController::reportes');           // GET /admin/ventas/reportes - Reportes de ventas
        $routes->get('generar-folio', 'Admin\\AdminVentasController::generarFolio');  // GET /admin/ventas/generar-folio - AJAX generar folio
        $routes->post('filtrar-configuraciones', 'Admin\\AdminVentasController::filtrarConfiguraciones'); // POST /admin/ventas/filtrar-configuraciones - AJAX filter
        $routes->get('recibo/(:num)', 'Admin\\AdminVentasController::recibo/$1');     // GET /admin/ventas/recibo/123 - Imprimir recibo de venta
        $routes->get('generar-amortizacion/(:num)', 'Admin\\AdminVentasController::generarAmortizacion/$1'); // GET /admin/ventas/generar-amortizacion/123 - Generar tabla amortización
        $routes->get('estado-cuenta/(:num)', 'Admin\\AdminVentasController::estadoCuenta/$1'); // GET /admin/ventas/estado-cuenta/123 - Ver estado de cuenta
        $routes->post('aplicar-pago-rapido/(:num)', 'Admin\\AdminVentasController::aplicarPagoRapido/$1'); // POST /admin/ventas/aplicar-pago-rapido/123 - Aplicar pago rápido
    });
    
    // ===== MÓDULO DE APARTADOS =====
    $routes->group('apartados', function($routes) {
        $routes->get('/', 'Admin\\AdminApartadosController::index');                     // GET /admin/apartados - Lista de apartados
        $routes->get('create', 'Admin\\AdminApartadosController::create');               // GET /admin/apartados/create - Formulario nuevo apartado
        $routes->post('store', 'Admin\\AdminApartadosController::store');                // POST /admin/apartados/store - Guardar nuevo apartado
        $routes->get('(:num)', 'Admin\\AdminApartadosController::show/$1');              // GET /admin/apartados/123 - Ver detalle apartado
        $routes->get('edit/(:num)', 'Admin\\AdminApartadosController::edit/$1');         // GET /admin/apartados/edit/123 - Editar apartado
        $routes->post('update/(:num)', 'Admin\\AdminApartadosController::update/$1');    // POST /admin/apartados/update/123 - Actualizar apartado
        $routes->get('cancelar/(:num)', 'Admin\\AdminApartadosController::cancelar/$1'); // GET /admin/apartados/cancelar/123 - Formulario cancelar
        $routes->post('cancelar/(:num)', 'Admin\\AdminApartadosController::cancelar/$1'); // POST /admin/apartados/cancelar/123 - Procesar cancelación
        $routes->post('procesar-vencidos', 'Admin\\AdminApartadosController::procesar_vencidos'); // POST /admin/apartados/procesar-vencidos - Procesar apartados vencidos
        $routes->get('comprobante/(:num)', 'Admin\\AdminApartadosController::subirComprobante/$1'); // GET /admin/apartados/comprobante/123 - Formulario subir comprobante
        $routes->post('comprobante/(:num)', 'Admin\\AdminApartadosController::subirComprobante/$1'); // POST /admin/apartados/comprobante/123 - Subir comprobante
        $routes->get('recibo/(:num)', 'Admin\\AdminApartadosController::recibo/$1'); // GET /admin/apartados/recibo/123 - Ver recibo de apartado
        $routes->post('simular-amortizacion', 'Admin\\AdminApartadosController::simularAmortizacion'); // POST /admin/apartados/simular-amortizacion - Simular tabla de amortización
        $routes->post('obtener-lotes-modal', 'Admin\\AdminApartadosController::obtenerLotesModal'); // POST /admin/apartados/obtener-lotes-modal - Obtener lotes para modal
        $routes->post('imprimir-amortizacion', 'Admin\\AdminApartadosController::imprimirAmortizacion'); // POST /admin/apartados/imprimir-amortizacion - Imprimir simulación
        $routes->get('perfiles-por-lote', 'Admin\\AdminApartadosController::getPerfilesPorLote'); // GET /admin/apartados/perfiles-por-lote - Obtener perfiles filtrados por lote
    });

    // ===== MÓDULO DE INGRESOS =====
    $routes->group('ingresos', function($routes) {
        $routes->get('/', 'Admin\\AdminIngresosController::index');                     // GET /admin/ingresos - Lista de ingresos
        $routes->get('(:num)', 'Admin\\AdminIngresosController::show/$1');              // GET /admin/ingresos/123 - Ver detalle ingreso
        $routes->get('recibo/(:num)', 'Admin\\AdminIngresosController::recibo/$1');     // GET /admin/ingresos/recibo/123 - Ver recibo
        $routes->get('comprobante/(:num)', 'Admin\\AdminIngresosController::comprobante/$1'); // GET /admin/ingresos/comprobante/123 - Ver comprobante de pago
        $routes->post('getData', 'Admin\\AdminIngresosController::getData');           // POST /admin/ingresos/getData - DataTables AJAX
    });
    
    
    // ===== MÓDULO DE CLIENTES =====
    $routes->group('clientes', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\AdminClientesController::index');                      // GET /admin/clientes - Listado
        $routes->get('create', 'Admin\AdminClientesController::create');               // GET /admin/clientes/create - Formulario crear
        $routes->post('store', 'Admin\AdminClientesController::store');                // POST /admin/clientes/store - Procesar crear
        $routes->get('show/(:num)', 'Admin\AdminClientesController::show/$1');         // GET /admin/clientes/show/123 - Ver detalles
        $routes->get('edit/(:num)', 'Admin\AdminClientesController::edit/$1');         // GET /admin/clientes/edit/123 - Formulario editar
        $routes->post('update/(:num)', 'Admin\AdminClientesController::update/$1');    // POST /admin/clientes/update/123 - Procesar editar
        // DOCUMENTOS - FUNCIONALIDAD NO IMPLEMENTADA AÚN
        // $routes->get('documento/(:num)', 'Admin\AdminClientesController::verDocumento/$1'); // GET /admin/clientes/documento/123 - Ver documento
        // $routes->post('subir-documento/(:num)', 'Admin\AdminClientesController::subirDocumento/$1'); // POST /admin/clientes/subir-documento/123 - Subir documento
        // $routes->delete('eliminar-documento/(:num)', 'Admin\AdminClientesController::eliminarDocumento/$1'); // DELETE /admin/clientes/eliminar-documento/123 - Eliminar documento
        $routes->get('delete/(:num)', 'Admin\AdminClientesController::delete/$1');
        
        // Rutas AJAX para acciones rápidas
        $routes->post('cambiarEstado', 'Admin\AdminClientesController::cambiarEstado'); // POST /admin/clientes/cambiarEstado - AJAX
        $routes->post('cambiarEtapa', 'Admin\AdminClientesController::cambiarEtapa');   // POST /admin/clientes/cambiarEtapa - AJAX
        $routes->post('eliminar/(:num)', 'Admin\AdminClientesController::delete/$1'); // POST /admin/clientes/eliminar/123 - AJAX Soft Delete
        $routes->get('buscar', 'Admin\AdminClientesController::buscar');               // GET /admin/clientes/buscar?q=termino - AJAX
        $routes->post('datatable', 'Admin\AdminClientesController::datatable');        // POST /admin/clientes/datatable - AJAX DataTables
        
        // Rutas adicionales para gestión avanzada
        $routes->get('estadisticas', 'Admin\AdminClientesController::estadisticas');   // GET /admin/clientes/estadisticas
        $routes->get('exportar', 'Admin\AdminClientesController::exportar');           // GET /admin/clientes/exportar
        
        // Rutas alternativas (mantener compatibilidad)
        $routes->get('alt_create', 'Admin\AdminAltClientesController::new');
        $routes->post('alt_store', 'Admin\AdminAltClientesController::create');
        $routes->get('alt_edit/(:num)', 'Admin\AdminAltClientesController::edit/$1');
        $routes->post('alt_update/(:num)', 'Admin\AdminAltClientesController::update/$1');
    });

    // ===== MÓDULO DE EMPRESAS =====
    $routes->group('empresas', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\AdminEmpresasController::index');                     // GET /admin/empresas - Listado
        $routes->get('create', 'Admin\AdminEmpresasController::create');              // GET /admin/empresas/create - Formulario crear
        $routes->post('store', 'Admin\AdminEmpresasController::store');               // POST /admin/empresas/store - Procesar crear
        $routes->get('edit/(:num)', 'Admin\AdminEmpresasController::edit/$1');        // GET /admin/empresas/edit/123 - Formulario editar
        $routes->post('update/(:num)', 'Admin\AdminEmpresasController::update/$1');   // POST /admin/empresas/update/123 - Procesar editar
        
        // Rutas AJAX
        $routes->post('datatable', 'Admin\AdminEmpresasController::datatable');       // POST /admin/empresas/datatable - AJAX DataTables
        $routes->post('delete/(:num)', 'Admin\AdminEmpresasController::delete/$1');   // POST /admin/empresas/delete/123 - AJAX SweetAlert
    });

    // ===== MÓDULO DE MANZANAS =====
    $routes->group('manzanas', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\AdminManzanasController::index');                     // GET /admin/manzanas - Listado
        $routes->get('create', 'Admin\AdminManzanasController::create');              // GET /admin/manzanas/create - Formulario crear
        $routes->get('edit/(:num)', 'Admin\AdminManzanasController::edit/$1');        // GET /admin/manzanas/edit/123 - Formulario editar
        
        // Rutas AJAX para operaciones
        $routes->post('obtener', 'Admin\AdminManzanasController::obtenerManzanas');   // POST /admin/manzanas/obtener - AJAX Listar con filtros
        $routes->post('store', 'Admin\AdminManzanasController::store');               // POST /admin/manzanas/store - AJAX Crear nueva
        $routes->post('update/(:num)', 'Admin\AdminManzanasController::update/$1');   // POST /admin/manzanas/update/123 - AJAX Actualizar
        $routes->post('delete/(:num)', 'Admin\AdminManzanasController::delete/$1');   // POST /admin/manzanas/delete/123 - AJAX Soft Delete
        $routes->post('restaurar/(:num)', 'Admin\AdminManzanasController::restaurar/$1'); // POST /admin/manzanas/restaurar/123 - AJAX Restaurar
        
        // Rutas AJAX adicionales
        $routes->post('por-proyecto/(:num)', 'Admin\AdminManzanasController::obtenerPorProyecto/$1'); // POST /admin/manzanas/por-proyecto/123 - AJAX Para selects
        $routes->post('estadisticas', 'Admin\AdminManzanasController::estadisticas');  // POST /admin/manzanas/estadisticas - AJAX Stats
    });

    // ===== MÓDULO DE LOTES =====
    $routes->group('lotes', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\AdminLotesController::index');                         // GET /admin/lotes - Listado
        $routes->get('create', 'Admin\AdminLotesController::create');                   // GET /admin/lotes/create - Formulario crear
        $routes->get('edit/(:num)', 'Admin\AdminLotesController::edit/$1');             // GET /admin/lotes/edit/123 - Formulario editar
        $routes->get('show/(:num)', 'Admin\AdminLotesController::show/$1');             // GET /admin/lotes/show/123 - Ver detalles
        
        // Rutas AJAX para operaciones
        $routes->post('obtener-lotes', 'Admin\AdminLotesController::obtenerLotes');     // POST /admin/lotes/obtener-lotes - AJAX Listar con filtros
        $routes->post('store', 'Admin\AdminLotesController::store');                    // POST /admin/lotes/store - AJAX Crear nuevo
        $routes->post('update/(:num)', 'Admin\AdminLotesController::update/$1');        // POST /admin/lotes/update/123 - AJAX Actualizar
        $routes->post('destroy/(:num)', 'Admin\AdminLotesController::destroy/$1');      // POST /admin/lotes/destroy/123 - AJAX Eliminar físicamente
        $routes->post('cambiar-estado/(:num)', 'Admin\AdminLotesController::cambiarEstado/$1'); // POST /admin/lotes/cambiar-estado/123 - AJAX Cambiar estado
        
        // Rutas AJAX adicionales para filtros en cascada
        $routes->get('obtener-empresas', 'Admin\AdminLotesController::obtenerEmpresas'); // GET /admin/lotes/obtener-empresas
        $routes->get('obtener-categorias', 'Admin\AdminLotesController::obtenerCategorias'); // GET /admin/lotes/obtener-categorias
        $routes->get('obtener-proyectos-por-empresa/(:num)', 'Admin\AdminLotesController::obtenerProyectosPorEmpresa/$1'); // GET /admin/lotes/obtener-proyectos-por-empresa/123
        $routes->get('obtener-divisiones-por-proyecto/(:num)', 'Admin\AdminLotesController::obtenerDivisionesPorProyecto/$1'); // GET /admin/lotes/obtener-divisiones-por-proyecto/123
        $routes->get('obtener-manzanas-por-proyecto/(:num)', 'Admin\AdminLotesController::obtenerManzanasPorProyecto/$1'); // GET /admin/lotes/obtener-manzanas-por-proyecto/123
        $routes->get('estadisticas-proyecto/(:num)', 'Admin\AdminLotesController::estadisticasProyecto/$1'); // GET /admin/lotes/estadisticas-proyecto/123
        $routes->post('regenerar-claves', 'Admin\AdminLotesController::regenerarClaves'); // POST /admin/lotes/regenerar-claves - Regenerar nomenclatura
    });


    // ===== MÓDULO DE PRESUPUESTOS =====
    $routes->group('presupuestos', function($routes) {
        // Rutas principales
        $routes->get('/', 'Admin\AdminPresupuestosController::crear');                           // GET /admin/presupuestos
        $routes->get('crear', 'Admin\AdminPresupuestosController::crear');                       // GET /admin/presupuestos/crear
        $routes->post('generar-tabla', 'Admin\AdminPresupuestosController::generarTablaPresupuesto'); // POST /admin/presupuestos/generar-tabla
        
        // Vista especializada para PDF (sin tabs, todas las tablas visibles)
        $routes->get('tabla-pdf', 'Admin\AdminPresupuestosController::tablaPDF');                // GET /admin/presupuestos/tabla-pdf - Vista para PDF con parámetros
        
        // Funcionalidades de exportación
        $routes->get('exportar-pdf', 'Admin\AdminPresupuestosController::exportarPDF');         // GET /admin/presupuestos/exportar-pdf
        $routes->post('enviar-email', 'Admin\AdminPresupuestosController::enviarEmail');         // POST /admin/presupuestos/enviar-email
    });

    // ===== MÓDULO DE FINANCIAMIENTO =====
    $routes->group('financiamiento', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\AdminFinanciamientoController::index');                     // GET /admin/financiamiento
        $routes->get('create', 'Admin\AdminFinanciamientoController::create');               // GET /admin/financiamiento/create
        $routes->post('store', 'Admin\AdminFinanciamientoController::store');                // POST /admin/financiamiento/store
        $routes->get('show/(:num)', 'Admin\AdminFinanciamientoController::show/$1');         // GET /admin/financiamiento/show/123
        $routes->get('edit/(:num)', 'Admin\AdminFinanciamientoController::edit/$1');         // GET /admin/financiamiento/edit/123
        $routes->post('update/(:num)', 'Admin\AdminFinanciamientoController::update/$1');    // POST /admin/financiamiento/update/123
        $routes->post('delete/(:num)', 'Admin\AdminFinanciamientoController::delete/$1');    // POST /admin/financiamiento/delete/123
        $routes->post('desactivar/(:num)', 'Admin\AdminFinanciamientoController::desactivar/$1'); // POST /admin/financiamiento/desactivar/123
        
        // Rutas AJAX para operaciones avanzadas
        $routes->get('obtenerFinanciamientos', 'Admin\AdminFinanciamientoController::obtenerFinanciamientos'); // GET /admin/financiamiento/obtenerFinanciamientos - AJAX DataTables
        $routes->post('simular', 'Admin\AdminFinanciamientoController::simular');            // POST /admin/financiamiento/simular - AJAX Simulador
        $routes->post('setDefault', 'Admin\AdminFinanciamientoController::setDefault');      // POST /admin/financiamiento/setDefault - AJAX Establecer como predeterminado
        $routes->post('duplicate', 'Admin\AdminFinanciamientoController::duplicate');        // POST /admin/financiamiento/duplicate - AJAX Duplicar
        $routes->get('getByEmpresa/(:num)', 'Admin\AdminFinanciamientoController::getByEmpresa/$1'); // GET /admin/financiamiento/getByEmpresa/123 - AJAX Obtener por empresa
        $routes->get('getProyectosByEmpresa/(:num)', 'Admin\AdminFinanciamientoController::getProyectosByEmpresa/$1'); // GET /admin/financiamiento/getProyectosByEmpresa/123 - AJAX Obtener proyectos
    });


    // ===== MÓDULO DE COMISIONES =====
    $routes->group('comisiones', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\AdminComisionesController::index');                              // GET /admin/comisiones - Dashboard principal
        $routes->get('por-vendedor/(:num)', 'Admin\AdminComisionesController::porVendedor/$1');   // GET /admin/comisiones/por-vendedor/123 - Comisiones por vendedor
        $routes->get('show/(:num)', 'Admin\AdminComisionesController::show/$1');                  // GET /admin/comisiones/show/123 - Ver detalle
        $routes->get('recibo/(:num)', 'Admin\AdminComisionesController::recibo/$1');              // GET /admin/comisiones/recibo/123 - Recibo de comisión
        $routes->get('generar-reporte', 'Admin\AdminComisionesController::generarReporte');       // GET /admin/comisiones/generar-reporte - Generar reporte
        
        // Rutas AJAX para operaciones
        $routes->post('obtener-comisiones', 'Admin\AdminComisionesController::obtenerComisiones'); // POST /admin/comisiones/obtener-comisiones - AJAX DataTables
        $routes->post('procesar-pago', 'Admin\AdminComisionesController::procesarPago');           // POST /admin/comisiones/procesar-pago - AJAX Procesar pago
        $routes->post('generar-comisiones-faltantes', 'Admin\AdminComisionesController::generarComisionesFaltantes'); // POST /admin/comisiones/generar-comisiones-faltantes - AJAX
        $routes->post('anular/(:num)', 'Admin\AdminComisionesController::anularComision/$1');      // POST /admin/comisiones/anular/123 - AJAX Anular
        $routes->get('calcular-venta/(:num)', 'Admin\AdminComisionesController::calcularComisionesVenta/$1'); // GET /admin/comisiones/calcular-venta/123
        $routes->post('obtener-estadisticas', 'Admin\AdminComisionesController::obtenerEstadisticas'); // POST /admin/comisiones/obtener-estadisticas - AJAX
        
        // Rutas para flujo de estados
        $routes->post('aceptar-comision', 'Admin\AdminComisionesController::aceptarComision');      // POST /admin/comisiones/aceptar-comision - AJAX
        $routes->post('procesar-comision', 'Admin\AdminComisionesController::procesarComision');    // POST /admin/comisiones/procesar-comision - AJAX
        $routes->post('pagar-comision', 'Admin\AdminComisionesController::pagarComision');          // POST /admin/comisiones/pagar-comision - AJAX
    });

    // ===== MÓDULO DE ESTADO DE CUENTA =====
    $routes->group('estado-cuenta', function($routes) {
        // Rutas principales del dashboard
        $routes->get('/', 'Admin\AdminEstadoCuentaController::index');                           // GET /admin/estado-cuenta - Dashboard principal
        $routes->get('cliente/(:num)', 'Admin\AdminEstadoCuentaController::cliente/$1');        // GET /admin/estado-cuenta/cliente/123 - Estado específico de cliente
        $routes->get('venta/(:num)', 'Admin\AdminEstadoCuentaController::venta/$1');             // GET /admin/estado-cuenta/venta/123 - Estado específico de venta
        $routes->get('alertas', 'Admin\AdminEstadoCuentaController::alertas');                  // GET /admin/estado-cuenta/alertas - Alertas de vencimiento
        
        // Rutas AJAX para búsquedas y estadísticas
        $routes->get('buscar', 'Admin\AdminEstadoCuentaController::buscar');                     // GET /admin/estado-cuenta/buscar - AJAX Búsqueda
        $routes->get('generar/(:num)', 'Admin\AdminEstadoCuentaController::generar/$1');         // GET /admin/estado-cuenta/generar/123 - Generar estado de cuenta imprimible
        $routes->get('buscarClientes', 'Admin\AdminEstadoCuentaController::buscarClientes');     // GET /admin/estado-cuenta/buscarClientes - AJAX Select2
        $routes->post('exportarMensualidades', 'Admin\AdminEstadoCuentaController::exportarMensualidades'); // POST /admin/estado-cuenta/exportarMensualidades - AJAX Excel/PDF
        $routes->get('widgetResumenFinanciero/(:num)', 'Admin\AdminEstadoCuentaController::widgetResumenFinanciero/$1'); // GET /admin/estado-cuenta/widgetResumenFinanciero/123 - AJAX Widget
        $routes->get('widgetAlertas/(:num)', 'Admin\AdminEstadoCuentaController::widgetAlertas/$1'); // GET /admin/estado-cuenta/widgetAlertas/123 - AJAX Widget
    });

    // ===== MÓDULO DE REESTRUCTURACIÓN DE CARTERA =====
    $routes->group('reestructuracion', function($routes) {
        // Dashboard principal
        $routes->get('/', 'Admin\AdminReestructuracionController::index');                           // GET /admin/reestructuracion - Dashboard principal
        
        // Gestión de reestructuraciones (CRUD estándar)
        $routes->get('view', 'Admin\AdminReestructuracionController::view');                        // GET /admin/reestructuracion/view - Lista todas las reestructuraciones
        $routes->get('create/(:num)', 'Admin\AdminReestructuracionController::create/$1');          // GET /admin/reestructuracion/create/123 - Formulario crear para venta específica
        $routes->post('store', 'Admin\AdminReestructuracionController::store');                     // POST /admin/reestructuracion/store - Guardar nueva reestructuración
        $routes->get('show/(:num)', 'Admin\AdminReestructuracionController::show/$1');              // GET /admin/reestructuracion/show/123 - Ver detalle de reestructuración
        $routes->get('edit/(:num)', 'Admin\AdminReestructuracionController::edit/$1');              // GET /admin/reestructuracion/edit/123 - Formulario editar reestructuración
        $routes->post('update/(:num)', 'Admin\AdminReestructuracionController::update/$1');         // POST /admin/reestructuracion/update/123 - Actualizar reestructuración
        $routes->post('delete/(:num)', 'Admin\AdminReestructuracionController::delete/$1');         // POST /admin/reestructuracion/delete/123 - Eliminar reestructuración
        
        // Gestión de ventas elegibles
        $routes->get('ventas-elegibles', 'Admin\AdminReestructuracionController::ventasElegibles'); // GET /admin/reestructuracion/ventas-elegibles - Ventas en estado jurídico
        
        // Workflow de autorización
        $routes->get('autorizar/(:num)', 'Admin\AdminReestructuracionController::autorizar/$1');    // GET /admin/reestructuracion/autorizar/123 - Autorizar reestructuración
        $routes->get('activar/(:num)', 'Admin\AdminReestructuracionController::activar/$1');        // GET /admin/reestructuracion/activar/123 - Activar reestructuración
        $routes->get('cancelar/(:num)', 'Admin\AdminReestructuracionController::cancelar/$1');      // GET /admin/reestructuracion/cancelar/123 - Cancelar reestructuración
        
        // Rutas AJAX y utilidades
        $routes->get('calcular-pago/(:num)/(:num)/(:num)', 'Admin\AdminReestructuracionController::calcularPago/$1/$2/$3'); // GET /admin/reestructuracion/calcular-pago/capital/tasa/plazo - AJAX
        $routes->post('buscar', 'Admin\AdminReestructuracionController::buscar');                   // POST /admin/reestructuracion/buscar - AJAX búsqueda con filtros
        $routes->get('exportar', 'Admin\AdminReestructuracionController::exportar');                // GET /admin/reestructuracion/exportar - Exportar Excel/PDF
        
        // Reportes y estadísticas
        $routes->get('reportes', 'Admin\AdminReestructuracionController::reportes');                // GET /admin/reestructuracion/reportes - Dashboard de reportes
        $routes->get('estadisticas', 'Admin\AdminReestructuracionController::estadisticas');        // GET /admin/reestructuracion/estadisticas - AJAX estadísticas
        
        // Gestión de pagos de reestructuraciones
        $routes->get('pagos/(:num)', 'Admin\AdminReestructuracionController::pagos/$1');            // GET /admin/reestructuracion/pagos/123 - Gestión de pagos
        $routes->post('aplicar-pago', 'Admin\AdminReestructuracionController::aplicarPago');        // POST /admin/reestructuracion/aplicar-pago - Aplicar pago a mensualidad
        $routes->get('recibo-pago/(:num)', 'Admin\AdminReestructuracionController::reciboPago/$1'); // GET /admin/reestructuracion/recibo-pago/123 - Generar recibo de pago
        
        // Documentos y convenios
        $routes->get('generar-convenio/(:num)', 'Admin\AdminReestructuracionController::generarConvenio/$1'); // GET /admin/reestructuracion/generar-convenio/123 - Generar documento de convenio
        $routes->post('subir-convenio/(:num)', 'Admin\AdminReestructuracionController::subirConvenio/$1'); // POST /admin/reestructuracion/subir-convenio/123 - Subir convenio firmado
        $routes->get('descargar-convenio/(:num)', 'Admin\AdminReestructuracionController::descargarConvenio/$1'); // GET /admin/reestructuracion/descargar-convenio/123 - Descargar convenio
    });

    // ===== MÓDULO DE MENSUALIDADES =====
    $routes->group('mensualidades', function($routes) {
        // Rutas principales de gestión
        $routes->get('/', 'Admin\AdminMensualidadesController::index');                          // GET /admin/mensualidades - Lista con filtros avanzados
        $routes->get('pendientes', 'Admin\AdminMensualidadesController::pendientes');           // GET /admin/mensualidades/pendientes - Mensualidades categorizadas
        $routes->get('aplicarPago/(:num)', 'Admin\AdminMensualidadesController::aplicarPago/$1'); // GET /admin/mensualidades/aplicarPago/123 - Formulario aplicar pago
        $routes->get('detalle/(:num)', 'Admin\AdminMensualidadesController::detalle/$1');        // GET /admin/mensualidades/detalle/123 - Detalle completo mensualidad
        $routes->get('reporteMensual', 'Admin\AdminMensualidadesController::reporteMensual');    // GET /admin/mensualidades/reporteMensual - Reportes con gráficos
        
        // Rutas POST para procesamiento
        $routes->post('procesarPago', 'Admin\AdminMensualidadesController::procesarPago');       // POST /admin/mensualidades/procesarPago - Procesar pago individual
        $routes->post('procesarPagoMasivo', 'Admin\AdminMensualidadesController::procesarPagoMasivo'); // POST /admin/mensualidades/procesarPagoMasivo - Procesar múltiples pagos
        $routes->post('cancelarPago/(:num)', 'Admin\AdminMensualidadesController::cancelarPago/$1'); // POST /admin/mensualidades/cancelarPago/123 - Cancelar pago aplicado
        
        // Rutas para reportes y comprobantes
        $routes->get('comprobante/(:num)', 'Admin\AdminMensualidadesController::comprobante/$1'); // GET /admin/mensualidades/comprobante/123 - Ver comprobante de pago
        $routes->get('exportarReporte', 'Admin\AdminMensualidadesController::exportarReporte');  // GET /admin/mensualidades/exportarReporte - Exportar Excel/PDF
        $routes->post('enviarReportePorEmail', 'Admin\AdminMensualidadesController::enviarReportePorEmail'); // POST /admin/mensualidades/enviarReportePorEmail - Envío por email
        $routes->get('exportarCriticas', 'Admin\AdminMensualidadesController::exportarCriticas'); // GET /admin/mensualidades/exportarCriticas - Exportar críticas
        $routes->get('exportarHistorial/(:num)', 'Admin\AdminMensualidadesController::exportarHistorial/$1'); // GET /admin/mensualidades/exportarHistorial/123 - Historial específico
    });

    // ===== MÓDULO DE COBRANZA =====
    $routes->group('cobranza', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\AdminCobranzaController::index');                          // GET /admin/cobranza - Listado principal
        $routes->get('show/(:num)', 'Admin\AdminCobranzaController::show/$1');              // GET /admin/cobranza/show/123 - Ver detalle
        $routes->get('vencidas', 'Admin\AdminCobranzaController::cuentasVencidas');         // GET /admin/cobranza/vencidas - Cuentas vencidas
        
        // Rutas AJAX para operaciones de cobranza
        $routes->get('obtener-cuentas', 'Admin\AdminCobranzaController::obtenerCuentas');   // GET /admin/cobranza/obtener-cuentas - AJAX Listar con filtros
        $routes->post('generar-plan', 'Admin\AdminCobranzaController::generarPlan');        // POST /admin/cobranza/generar-plan - AJAX Generar plan de pagos
        $routes->post('aplicar-intereses', 'Admin\AdminCobranzaController::aplicarIntereses'); // POST /admin/cobranza/aplicar-intereses - AJAX Aplicar intereses
        $routes->get('resumen-cliente/(:num)', 'Admin\AdminCobranzaController::resumenCliente/$1'); // GET /admin/cobranza/resumen-cliente/123 - AJAX Resumen cliente
    });

    // ===== MÓDULO DE PAGOS INMOBILIARIOS =====
    $routes->group('pagos', function($routes) {
        // Rutas principales
        $routes->get('/', 'Admin\AdminPagosController::index');                             // GET /admin/pagos - Dashboard de pagos
        $routes->get('detalle/(:num)/(:num)', 'Admin\AdminPagosController::detalle/$1/$2'); // GET /admin/pagos/detalle/123/456 - Detalle cliente-lote
        
        // Rutas de procesamiento de apartados y anticipos
        $routes->get('procesar-apartado/(:num)', 'Admin\AdminPagosController::procesarApartado/$1'); // GET /admin/pagos/procesar-apartado/123 - Formulario apartado
        $routes->get('procesar-anticipo/(:num)', 'Admin\AdminPagosController::procesarAnticipo/$1'); // GET /admin/pagos/procesar-anticipo/123 - Formulario anticipo (alias apartado)
        $routes->post('guardar-apartado', 'Admin\AdminPagosController::guardarApartado');    // POST /admin/pagos/guardar-apartado - Guardar apartado
        
        // Rutas de liquidación de enganche
        $routes->get('liquidar-enganche/(:num)', 'Admin\AdminPagosController::liquidarEnganche/$1'); // GET /admin/pagos/liquidar-enganche/123 - Formulario liquidación
        $routes->post('guardar-liquidacion', 'Admin\AdminPagosController::guardarLiquidacion'); // POST /admin/pagos/guardar-liquidacion - Guardar liquidación
        
        // Rutas de mensualidades
        $routes->get('procesar-mensualidad/(:num)', 'Admin\AdminPagosController::procesarMensualidad/$1'); // GET /admin/pagos/procesar-mensualidad/123 - Formulario mensualidad
        $routes->post('guardar-mensualidad', 'Admin\AdminPagosController::guardarMensualidad'); // POST /admin/pagos/guardar-mensualidad - Guardar mensualidad
        
        // Rutas de abonos a capital
        $routes->get('abono-capital/(:num)', 'Admin\AdminPagosController::abonoCapital/$1'); // GET /admin/pagos/abono-capital/123 - Formulario abono capital
        $routes->post('simular-abono', 'Admin\AdminPagosController::simularAbono');          // POST /admin/pagos/simular-abono - AJAX Simular abono
        $routes->post('guardar-abono-capital', 'Admin\AdminPagosController::guardarAbonoCapital'); // POST /admin/pagos/guardar-abono-capital - Guardar abono
        
        // Rutas de refactorizaciones
        $routes->get('refactorizaciones/(:num)', 'Admin\AdminPagosController::refactorizaciones/$1'); // GET /admin/pagos/refactorizaciones/123 - Historial refactorizaciones
        
        // Rutas de estado de cuenta
        $routes->get('estado-cuenta/(:num)', 'Admin\AdminPagosController::estadoCuenta/$1'); // GET /admin/pagos/estado-cuenta/123 - Estado de cuenta
        $routes->get('exportar-estado/(:num)', 'Admin\AdminPagosController::exportarEstadoCuenta/$1'); // GET /admin/pagos/exportar-estado/123 - Exportar PDF
        
        // Rutas AJAX
        $routes->get('buscar-ventas', 'Admin\AdminPagosController::buscarVentas');           // GET /admin/pagos/buscar-ventas - AJAX Buscar ventas
        $routes->get('dashboard-pagos', 'Admin\AdminPagosController::dashboardPagos');       // GET /admin/pagos/dashboard-pagos - AJAX Dashboard datos
    });

    // ===== MÓDULO DE DIVISIONES =====
    $routes->group('divisiones', function($routes) {
        // Rutas principales
        $routes->get('/', 'Admin\AdminDivisionesController::index');                         // GET /admin/divisiones - Lista principal
        $routes->get('create', 'Admin\AdminDivisionesController::create');                  // GET /admin/divisiones/create - Formulario crear
        $routes->get('edit/(:num)', 'Admin\AdminDivisionesController::edit/$1');            // GET /admin/divisiones/edit/123 - Formulario editar
        $routes->get('show/(:num)', 'Admin\AdminDivisionesController::show/$1');            // GET /admin/divisiones/show/123 - Ver detalles
        
        // Rutas AJAX para operaciones
        $routes->post('obtener-divisiones', 'Admin\AdminDivisionesController::obtenerDivisiones');           // POST /admin/divisiones/obtener-divisiones - AJAX Listar con filtros
        $routes->post('store', 'Admin\AdminDivisionesController::store');                                   // POST /admin/divisiones/store - AJAX Crear nuevo
        $routes->post('update/(:num)', 'Admin\AdminDivisionesController::update/$1');                       // POST /admin/divisiones/update/123 - AJAX Actualizar
        $routes->post('delete/(:num)', 'Admin\AdminDivisionesController::delete/$1');                       // POST /admin/divisiones/delete/123 - AJAX Soft Delete
        $routes->post('restaurar/(:num)', 'Admin\AdminDivisionesController::restaurar/$1');                 // POST /admin/divisiones/restaurar/123 - AJAX Restaurar
        
        // Rutas AJAX para filtros en cascada y utilidades
        $routes->get('obtener-proyectos-por-empresa/(:num)', 'Admin\AdminDivisionesController::obtenerProyectosPorEmpresa/$1');    // GET /admin/divisiones/obtener-proyectos-por-empresa/123
        $routes->get('obtener-divisiones-por-proyecto/(:num)', 'Admin\AdminDivisionesController::obtenerDivisionesPorProyecto/$1'); // GET /admin/divisiones/obtener-divisiones-por-proyecto/123
        $routes->post('sugerir-clave', 'Admin\AdminDivisionesController::sugerirClave');                    // POST /admin/divisiones/sugerir-clave - AJAX Sugerir clave
    });

    // ===== MÓDULO DE CATÁLOGOS =====
    $routes->group('catalogos', function($routes) {
        
        // Estados civiles - REMOVIDO: No cambia en el tiempo, se mantiene en BD
        
        // Fuentes de información
        $routes->group('fuentes-informacion', function($routes) {
            $routes->get('/', 'Admin\AdminFuentesInformacionController::index');
            $routes->get('create', 'Admin\AdminFuentesInformacionController::create');
            $routes->get('edit/(:num)', 'Admin\AdminFuentesInformacionController::edit/$1');
            $routes->post('obtener-fuentes', 'Admin\AdminFuentesInformacionController::obtenerFuentes');
            $routes->post('store', 'Admin\AdminFuentesInformacionController::store');
            $routes->post('update/(:num)', 'Admin\AdminFuentesInformacionController::update/$1');
            $routes->post('cambiar-estado/(:num)', 'Admin\AdminFuentesInformacionController::cambiarEstado/$1');
            $routes->get('estadisticas', 'Admin\AdminFuentesInformacionController::estadisticas');
        });

        // Categorías de lotes
        $routes->group('categorias-lotes', function($routes) {
            $routes->get('/', 'Admin\AdminCategoriasLotesController::index');
            $routes->get('create', 'Admin\AdminCategoriasLotesController::create');
            $routes->get('edit/(:num)', 'Admin\AdminCategoriasLotesController::edit/$1');
            $routes->post('obtener-categorias', 'Admin\AdminCategoriasLotesController::obtenerCategorias');
            $routes->post('store', 'Admin\AdminCategoriasLotesController::store');
            $routes->post('update/(:num)', 'Admin\AdminCategoriasLotesController::update/$1');
            $routes->post('cambiar-estado/(:num)', 'Admin\AdminCategoriasLotesController::cambiarEstado/$1');
        });

        // Tipos de lotes
        $routes->group('tipos-lotes', function($routes) {
            // Rutas ESTÁNDAR (REST conventions)
            $routes->get('/', 'Admin\AdminTiposLotesController::index');
            $routes->get('create', 'Admin\AdminTiposLotesController::create');
            $routes->get('edit/(:num)', 'Admin\AdminTiposLotesController::edit/$1');
            $routes->post('store', 'Admin\AdminTiposLotesController::store');
            $routes->post('update/(:num)', 'Admin\AdminTiposLotesController::update/$1');
            $routes->post('toggle-status/(:num)', 'Admin\AdminTiposLotesController::cambiarEstado/$1');
            $routes->post('obtener-tipos', 'Admin\AdminTiposLotesController::obtenerTipos');
            
            // Rutas COMPATIBILIDAD (deprecated - usar rutas estándar)
            $routes->get('crear', 'Admin\AdminTiposLotesController::crear');
            $routes->get('edit/(:num)', 'Admin\AdminTiposLotesController::edit/$1');
            $routes->post('guardar', 'Admin\AdminTiposLotesController::guardar');
            $routes->post('actualizar/(:num)', 'Admin\AdminTiposLotesController::actualizar/$1');
            $routes->post('cambiar-estado/(:num)', 'Admin\AdminTiposLotesController::cambiarEstado/$1');
        });

        // Amenidades
        $routes->group('amenidades', function($routes) {
            $routes->get('/', 'Admin\AdminAmenidadesController::index');
            $routes->get('create', 'Admin\AdminAmenidadesController::create');
            $routes->get('edit/(:num)', 'Admin\AdminAmenidadesController::edit/$1');
            $routes->post('obtener-amenidades', 'Admin\AdminAmenidadesController::obtenerAmenidades');
            $routes->post('obtener-populares', 'Admin\AdminAmenidadesController::obtenerPopulares');
            $routes->post('store', 'Admin\AdminAmenidadesController::store');
            $routes->post('update/(:num)', 'Admin\AdminAmenidadesController::update/$1');
            $routes->post('cambiar-estado/(:num)', 'Admin\AdminAmenidadesController::cambiarEstado/$1');
            $routes->post('duplicate/(:num)', 'Admin\AdminAmenidadesController::duplicate/$1');
            $routes->post('delete/(:num)', 'Admin\AdminAmenidadesController::delete/$1');
        });
    });

    // ===== MÓDULO DE CUENTAS BANCARIAS =====
    $routes->group('cuentas-bancarias', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\\AdminCuentasBancariasController::index');                     // GET /admin/cuentas-bancarias - Listado
        $routes->get('create', 'Admin\\AdminCuentasBancariasController::create');              // GET /admin/cuentas-bancarias/create - Formulario crear
        $routes->post('store', 'Admin\\AdminCuentasBancariasController::store');               // POST /admin/cuentas-bancarias/store - Procesar crear
        $routes->get('edit/(:num)', 'Admin\\AdminCuentasBancariasController::edit/$1');        // GET /admin/cuentas-bancarias/edit/123 - Formulario editar
        $routes->post('update/(:num)', 'Admin\\AdminCuentasBancariasController::update/$1');   // POST /admin/cuentas-bancarias/update/123 - Procesar editar
        
        // Rutas AJAX para operaciones
        $routes->post('obtener-cuentas', 'Admin\\AdminCuentasBancariasController::obtenerCuentas');    // POST /admin/cuentas-bancarias/obtener-cuentas - AJAX DataTables
        $routes->post('cambiar-estado/(:num)', 'Admin\\AdminCuentasBancariasController::cambiarEstado/$1'); // POST /admin/cuentas-bancarias/cambiar-estado/123 - AJAX Cambiar estado
        $routes->post('delete/(:num)', 'Admin\\AdminCuentasBancariasController::delete/$1');            // POST /admin/cuentas-bancarias/delete/123 - AJAX Eliminar cuenta
    });

    // ===== MÓDULO DE PROYECTOS INMOBILIARIOS =====
    $routes->group('proyectos', function($routes) {
        // Rutas principales CRUD (normalizadas)
        $routes->get('/', 'Admin\AdminProyectosController::index');                              // GET /admin/proyectos
        $routes->get('create', 'Admin\AdminProyectosController::create');                       // GET /admin/proyectos/create
        $routes->post('store', 'Admin\AdminProyectosController::store');                        // POST /admin/proyectos/store
        $routes->get('show/(:num)', 'Admin\AdminProyectosController::show/$1');                 // GET /admin/proyectos/show/123
        $routes->get('edit/(:num)', 'Admin\AdminProyectosController::edit/$1');                 // GET /admin/proyectos/edit/123
        $routes->post('update/(:num)', 'Admin\AdminProyectosController::update/$1');            // POST /admin/proyectos/update/123
        $routes->get('delete/(:num)', 'Admin\AdminProyectosController::confirmDelete/$1');      // GET /admin/proyectos/delete/123 - Vista confirmación
        $routes->post('delete/(:num)', 'Admin\AdminProyectosController::delete/$1');            // POST /admin/proyectos/delete/123 - Eliminar
        
        // Gestión de documentos (normalizadas)
        $routes->post('documentos/eliminar/(:num)', 'Admin\AdminProyectosController::eliminarDocumento/$1');   // POST /admin/proyectos/documentos/eliminar/123
        $routes->get('documentos/descargar/(:num)', 'Admin\AdminProyectosController::descargarDocumento/$1');  // GET /admin/proyectos/documentos/descargar/123
        
        // Rutas AJAX para estadísticas y búsqueda
        $routes->post('buscar-proyectos', 'Admin\AdminProyectosController::buscarProyectos');
        $routes->get('estadisticas/(:num)', 'Admin\AdminProyectosController::obtenerEstadisticas/$1');
    });

    //Gestión de usuarios
    $routes->group('usuarios', function($routes) {
        $routes->get('/', 'Admin\AdminUsuariosController::index');
        $routes->get('create', 'Admin\AdminUsuariosController::create');
        $routes->post('store', 'Admin\AdminUsuariosController::store');
        $routes->get('edit/(:num)', 'Admin\AdminUsuariosController::edit/$1');
        $routes->post('update/(:num)', 'Admin\AdminUsuariosController::update/$1');
        $routes->post('datatable', 'Admin\AdminUsuariosController::datatable');
        $routes->post('cambiarEstado/(:num)', 'Admin\AdminUsuariosController::cambiarEstado/$1');
    });

    // ===== MÓDULO DE GESTIÓN DE LEADS Y PROSPECTOS =====
    $routes->group('leads', function($routes) {
        // Dashboard de métricas y registros
        $routes->get('metricas', 'Admin\AdminLeadsController::metricas');                    // GET /admin/leads/metricas - Dashboard de métricas
        $routes->get('/', 'Admin\AdminLeadsController::index');                              // GET /admin/leads - Lista de registros
        $routes->get('conversiones', 'Admin\AdminLeadsController::conversiones');           // GET /admin/leads/conversiones - Vista conversiones
        
        // Gestión de registros individuales
        $routes->get('show/(:num)', 'Admin\AdminLeadsController::show/$1');                  // GET /admin/leads/show/123 - Ver detalle
        $routes->post('reenviar-hubspot/(:num)', 'Admin\AdminLeadsController::reenviarHubSpot/$1'); // POST /admin/leads/reenviar-hubspot/123 - Reenviar a HubSpot
        $routes->post('reenviar-google-drive/(:num)', 'Admin\AdminLeadsController::reenviarGoogleDrive/$1'); // POST /admin/leads/reenviar-google-drive/123 - Reenviar a Google Drive
        $routes->post('cambiar-estado-documento', 'Admin\AdminLeadsController::cambiarEstadoDocumento'); // POST /admin/leads/cambiar-estado-documento - Cambiar estado manual de documento
        
        // FASE 3: Conversión de leads a clientes
        $routes->post('convertir-a-cliente', 'Admin\AdminLeadsController::convertirACliente');     // POST /admin/leads/convertir-a-cliente - Convertir lead
        $routes->get('obtener-convertibles', 'Admin\AdminLeadsController::obtenerLeadsConvertibles'); // GET /admin/leads/obtener-convertibles - Leads listos para conversión
        
        // APIs para DataTables y AJAX
        $routes->get('obtener-registros', 'Admin\AdminLeadsController::obtenerRegistros');   // GET /admin/leads/obtener-registros - AJAX DataTables
        $routes->get('obtener-metricas', 'Admin\AdminLeadsController::obtenerMetricas');     // GET /admin/leads/obtener-metricas - AJAX métricas
        $routes->get('obtener-estadisticas', 'Admin\AdminLeadsController::obtenerEstadisticas'); // GET /admin/leads/obtener-estadisticas - AJAX stats
        
        // Gestión de logs y errores
        $routes->get('logs', 'Admin\AdminLeadsController::logs');                            // GET /admin/leads/logs - Ver logs de API
        $routes->get('errores', 'Admin\AdminLeadsController::errores');                      // GET /admin/leads/errores - Ver errores de sync
        
        // Exportación de datos
        $routes->get('exportar-registros', 'Admin\AdminLeadsController::exportarRegistros'); // GET /admin/leads/exportar-registros - Exportar CSV
        
        // Herramientas de administración
        $routes->post('recalcular-metricas', 'Admin\AdminLeadsController::recalcularMetricas'); // POST /admin/leads/recalcular-metricas - Recalcular métricas mes
        $routes->post('limpiar-logs', 'Admin\AdminLeadsController::limpiarLogs');             // POST /admin/leads/limpiar-logs - Limpiar logs antiguos
    });


    // ===== MÓDULO DE TAREAS (ADMIN) =====
    $routes->group('tareas', function($routes) {
        // Rutas principales CRUD
        $routes->get('/', 'Admin\AdminTareasController::index');                            // GET /admin/tareas - Lista de tareas creadas
        $routes->get('create', 'Admin\AdminTareasController::create');                      // GET /admin/tareas/create - Formulario crear
        $routes->post('store', 'Admin\AdminTareasController::store');                       // POST /admin/tareas/store - Procesar crear
        $routes->get('edit/(:num)', 'Admin\AdminTareasController::edit/$1');                // GET /admin/tareas/edit/123 - Formulario editar
        $routes->post('update/(:num)', 'Admin\AdminTareasController::update/$1');           // POST /admin/tareas/update/123 - Procesar editar
        $routes->get('show/(:num)', 'Admin\AdminTareasController::show/$1');                // GET /admin/tareas/show/123 - Ver detalles
        
        // Rutas AJAX para gestión
        $routes->post('cambiar-estado', 'Admin\AdminTareasController::cambiarEstado');      // POST /admin/tareas/cambiar-estado - AJAX Cambiar estado
        $routes->post('actualizar-progreso', 'Admin\AdminTareasController::actualizarProgreso'); // POST /admin/tareas/actualizar-progreso - AJAX Actualizar progreso
        $routes->post('delete/(:num)', 'Admin\AdminTareasController::delete/$1');           // POST /admin/tareas/delete/123 - AJAX Eliminar
        $routes->post('buscar', 'Admin\AdminTareasController::buscar');                     // POST /admin/tareas/buscar - AJAX Búsqueda
        $routes->get('estadisticas', 'Admin\AdminTareasController::estadisticas');          // GET /admin/tareas/estadisticas - AJAX Stats
        
        // ===== SUBMÓDULO MIS-TAREAS (VISTA PERSONAL DEL STAFF) =====
        $routes->group('mis-tareas', function($routes) {
            $routes->get('/', 'Admin\StaffTareasController::index');                                     // GET /admin/tareas/mis-tareas - Lista de tareas asignadas
            $routes->get('create', 'Admin\StaffTareasController::create');                               // GET /admin/tareas/mis-tareas/create - Crear tarea personal
            $routes->post('store', 'Admin\StaffTareasController::store');                                // POST /admin/tareas/mis-tareas/store - Procesar crear tarea personal
            $routes->get('show/(:num)', 'Admin\StaffTareasController::show/$1');                         // GET /admin/tareas/mis-tareas/show/123 - Ver detalles de tarea
            
            // Rutas AJAX para actualización de tareas
            $routes->post('actualizar-progreso', 'Admin\StaffTareasController::actualizarProgreso');    // POST /admin/tareas/mis-tareas/actualizar-progreso - AJAX Actualizar progreso
            $routes->post('completar', 'Admin\StaffTareasController::completar');                       // POST /admin/tareas/mis-tareas/completar - AJAX Completar tarea
            $routes->post('iniciar', 'Admin\StaffTareasController::iniciar');                           // POST /admin/tareas/mis-tareas/iniciar - AJAX Iniciar tarea
            $routes->post('agregar-comentario', 'Admin\StaffTareasController::agregarComentario');      // POST /admin/tareas/mis-tareas/agregar-comentario - AJAX Agregar comentario
            $routes->post('buscar', 'Admin\StaffTareasController::buscar');                             // POST /admin/tareas/mis-tareas/buscar - AJAX Búsqueda
            $routes->get('estadisticas', 'Admin\StaffTareasController::estadisticas');                  // GET /admin/tareas/mis-tareas/estadisticas - AJAX Stats
            
            // Rutas adicionales para tareas delegadas
            $routes->post('cancelar', 'Admin\StaffTareasController::cancelar');                         // POST /admin/tareas/mis-tareas/cancelar - AJAX Cancelar tarea delegada
            $routes->post('agregar-comentario-admin', 'Admin\StaffTareasController::agregarComentarioAdmin'); // POST /admin/tareas/mis-tareas/agregar-comentario-admin - AJAX Comentario admin
        });
    });

    // ===== PANEL DE DEBUG UNIFICADO - SOLO DESARROLLO =====
    if (ENVIRONMENT === 'development') {
        $routes->group('debug', function($routes) {
            // Panel principal de debug
            $routes->get('/', 'Debug\DebugController::index');                                // GET /admin/debug - Panel principal
            
            // Herramientas de debug del sistema
            $routes->get('conexion', 'Debug\DebugController::conexion');                     // GET /admin/debug/conexion - Test conexión DB
            $routes->get('shield', 'Debug\DebugController::shield');                         // GET /admin/debug/shield - Test Shield
            $routes->get('modelos', 'Debug\DebugController::modelos');                       // GET /admin/debug/modelos - Test modelos
            
            // Google Drive Debug
            $routes->get('google-drive', 'Debug\GoogleDriveDebugController::index');         // GET /admin/debug/google-drive - Panel Google Drive
            $routes->post('google-drive/crear-carpeta', 'Debug\GoogleDriveDebugController::crearCarpeta'); // POST /admin/debug/google-drive/crear-carpeta
            $routes->get('google-drive/test-auth', 'Debug\GoogleDriveDebugController::testAuth'); // GET /admin/debug/google-drive/test-auth
            $routes->get('google-drive/callback', 'Debug\GoogleDriveDebugController::callback'); // GET /admin/debug/google-drive/callback
            
            // Debug de clientes
            $routes->get('clientes', 'Debug\DebugClientesController::index');                // GET /admin/debug/clientes - Panel clientes
            $routes->get('clientes/test-database', 'Debug\DebugClientesController::testDatabase'); // GET /admin/debug/clientes/test-database
            $routes->get('clientes/test-form-data', 'Debug\DebugClientesController::testFormData'); // GET /admin/debug/clientes/test-form-data
            $routes->get('clientes/test-shield', 'Debug\DebugClientesController::testShield'); // GET /admin/debug/clientes/test-shield
            
            // Debug simple
            $routes->get('simple', 'Debug\SimpleDebugController::testDirecto');              // GET /admin/debug/simple - Test directo
            $routes->get('tablas', 'Debug\SimpleDebugController::verificarTablas');          // GET /admin/debug/tablas - Verificar tablas
            $routes->get('limpiar', 'Debug\SimpleDebugController::limpiarPruebas');          // GET /admin/debug/limpiar - Limpiar pruebas
            
            // Debug de presupuestos
            $routes->get('presupuestos', 'Debug\DebugPresupuestosController::index');
            $routes->get('presupuestos/debug-exportar-pdf', 'Debug\DebugPresupuestosController::debugExportarPDF');
            $routes->get('presupuestos/test-pdf-service', 'Debug\DebugPresupuestosController::testPdfService');
            $routes->get('presupuestos/test-presupuesto-simulado', 'Debug\DebugPresupuestosController::testPresupuestoSimulado');
            $routes->get('presupuestos/test-email-config', 'Debug\DebugPresupuestosController::testEmailConfig');
            $routes->get('presupuestos/test-enviar-email', 'Debug\DebugPresupuestosController::testEnviarEmail');
        });
    }
});

// =====================================================================
// RUTAS DE TAREAS ELIMINADAS - AHORA USAN /admin/tareas/mis-tareas
// =====================================================================


// =====================================================================
// RUTAS PÚBLICAS PARA CAMBIO DE EMAIL
// =====================================================================

// Verificación de cambio de email (ruta pública)
$routes->get('verificar-cambio-email', 'EmailChangeController::verificarCambio');          // GET /verificar-cambio-email?token=xxx - Verificar token
$routes->get('cambio-email-info', 'EmailChangeController::informacion');                   // GET /cambio-email-info - Información general

// =====================================================================
// RUTAS DE AUTENTICACIÓN (SHIELD)
// =====================================================================

service('auth')->routes($routes);

// =====================================================================
// RUTAS DE DEBUG - SOLO DESARROLLO (NIVEL RAÍZ)
// =====================================================================

if (ENVIRONMENT === 'development') {
    $routes->group('debug', function($routes) {
        // Panel principal de debug
        $routes->get('/', 'Debug\DebugController::index');                                // GET /debug - Panel principal
        
        // Herramientas de debug del sistema
        $routes->get('conexion', 'Debug\DebugController::conexion');                     // GET /debug/conexion - Test conexión DB
        $routes->get('shield', 'Debug\DebugController::shield');                         // GET /debug/shield - Test Shield
        $routes->get('modelos', 'Debug\DebugController::modelos');                       // GET /debug/modelos - Test modelos
        $routes->get('tablas', 'Debug\SimpleDebugController::verificarTablas');          // GET /debug/tablas - Verificar tablas
        
        // Google Drive Debug
        $routes->get('google-drive', 'Debug\GoogleDriveDebugController::index');         // GET /debug/google-drive - Panel Google Drive
        $routes->post('google-drive/crear-carpeta', 'Debug\GoogleDriveDebugController::crearCarpeta'); // POST /debug/google-drive/crear-carpeta
        $routes->get('google-drive/test-auth', 'Debug\GoogleDriveDebugController::testAuth'); // GET /debug/google-drive/test-auth
        $routes->get('google-drive/callback', 'Debug\GoogleDriveDebugController::callback'); // GET /debug/google-drive/callback
        
        // Debug de clientes
        $routes->get('bienvenida', 'Debug\DebugBienvenidaController::index');           // GET /debug/bienvenida - Debug proceso bienvenida
        $routes->get('bienvenida/debug-flujo', 'Debug\DebugBienvenidaController::debugFlujo'); // GET /debug/bienvenida/debug-flujo - Debug flujo completo
        $routes->get('bienvenida/generar', 'Debug\DebugBienvenidaController::generarMagicLink'); // GET /debug/bienvenida/generar - Generar magic link
        $routes->get('bienvenida/busqueda', 'Debug\DebugBienvenidaController::debugBusqueda'); // GET /debug/bienvenida/busqueda - Debug búsqueda usuarios
        $routes->get('bienvenida/logout', 'Debug\DebugBienvenidaController::logout'); // GET /debug/bienvenida/logout - Logout forzado
        $routes->get('bienvenida/test-auth', 'Debug\DebugBienvenidaController::testAuth'); // GET /debug/bienvenida/test-auth - Test autenticación
        $routes->get('clientes', 'Debug\DebugClientesController::index');                // GET /debug/clientes - Panel clientes
        
        // Debug de configuración financiera
        $routes->get('configuracion-financiera', 'Debug\DebugConfiguracionFinancieraController::index');           // GET /debug/configuracion-financiera - Panel principal
        $routes->get('configuracion-financiera/datos', 'Debug\DebugConfiguracionFinancieraController::datos');     // GET /debug/configuracion-financiera/datos - Ver datos BD
        $routes->get('configuracion-financiera/validaciones', 'Debug\DebugConfiguracionFinancieraController::validaciones'); // GET /debug/configuracion-financiera/validaciones - Test validaciones
        $routes->get('configuracion-financiera/post-data', 'Debug\DebugConfiguracionFinancieraController::postData'); // GET /debug/configuracion-financiera/post-data - Simular POST
        $routes->get('configuracion-financiera/clean-data', 'Debug\DebugConfiguracionFinancieraController::testCleanData'); // GET /debug/configuracion-financiera/clean-data - Test CleanData
        $routes->get('configuracion-financiera/model-update', 'Debug\DebugConfiguracionFinancieraController::modelUpdate'); // GET /debug/configuracion-financiera/model-update - Test Update
        $routes->get('configuracion-financiera/test-foreign-key', 'Debug\DebugConfiguracionFinancieraController::testForeignKey'); // GET /debug/configuracion-financiera/test-foreign-key - Test Foreign Key
        $routes->get('configuracion-financiera/test-filtros-inteligentes', 'Debug\DebugConfiguracionFinancieraController::testFiltrosInteligentes'); // GET /debug/configuracion-financiera/test-filtros-inteligentes - Test Filtros Inteligentes
        
        // Debug de amortización
        $routes->get('amortizacion/generarVenta18', 'Debug\DebugAmortizacionController::generarVenta18');          // GET /debug/amortizacion/generarVenta18 - Generar tabla venta 18
        $routes->get('amortizacion/regenerarVenta18', 'Debug\DebugAmortizacionController::regenerarVenta18');      // GET /debug/amortizacion/regenerarVenta18 - Regenerar tabla venta 18
        $routes->get('amortizacion/verResumenVenta18', 'Debug\DebugAmortizacionController::verResumenVenta18');    // GET /debug/amortizacion/verResumenVenta18 - Ver resumen tabla
        $routes->get('amortizacion/probarSimulacion', 'Debug\DebugAmortizacionController::probarSimulacion');      // GET /debug/amortizacion/probarSimulacion - Probar simulaciones
        $routes->get('amortizacion/debug-venta/(:num)', 'Debug\DebugAmortizacionController::debugVenta/$1');        // GET /debug/amortizacion/debug-venta/22 - Debug completo venta específica
        $routes->get('amortizacion/corregir-existentes', 'Debug\DebugAmortizacionController::corregirVentasExistentes'); // GET /debug/amortizacion/corregir-existentes - Corregir ventas con tabla pero sin cuenta
        $routes->get('clientes/test-database', 'Debug\DebugClientesController::testDatabase'); // GET /debug/clientes/test-database
        $routes->get('clientes/test-form-data', 'Debug\DebugClientesController::testFormData'); // GET /debug/clientes/test-form-data
        $routes->get('clientes/test-shield', 'Debug\DebugClientesController::testShield'); // GET /debug/clientes/test-shield
        $routes->get('cliente/test-show/(:num)', 'Debug\DebugClienteController::testShow/$1'); // GET /debug/cliente/test-show/2 - Test show cliente
        
        // Debug simple
        $routes->get('simple', 'Debug\SimpleDebugController::testDirecto');              // GET /debug/simple - Test directo
        $routes->get('limpiar', 'Debug\SimpleDebugController::limpiarPruebas');          // GET /debug/limpiar - Limpiar pruebas
        
        // Session debug
        $routes->get('session/check', 'Debug\SessionDebugController::checkSession');     // GET /debug/session/check - Check session status
        $routes->get('session/clear', 'Debug\SessionDebugController::clearSession');     // GET /debug/session/clear - Clear session
        
        // Test services
        $routes->get('test-services', 'Debug\TestServicesController::index');            // GET /debug/test-services - Test services instantiation
        
        // Debug de rollback - SOLO DESARROLLO
        $routes->get('rollback', 'Debug\DebugRollbackController::index');                                // GET /debug/rollback - Menú principal de rollback
        $routes->get('rollback/estado-sistema', 'Debug\DebugRollbackController::estadoSistema');          // GET /debug/rollback/estado-sistema - Estado actual del sistema
        $routes->get('rollback/rollback-completo', 'Debug\DebugRollbackController::rollbackCompleto');    // GET /debug/rollback/rollback-completo - Rollback completo
        $routes->get('rollback/rollback-venta/(:num)', 'Debug\DebugRollbackController::rollbackVenta/$1'); // GET /debug/rollback/rollback-venta/123 - Rollback venta específica
        $routes->get('rollback/rollback-venta', 'Debug\DebugRollbackController::rollbackVentaByQuery'); // GET /debug/rollback/rollback-venta?venta_id=123 - Rollback venta por query
        $routes->get('rollback/ultimo-pago/(:num)', 'Debug\DebugRollbackController::rollbackUltimoPago/$1'); // GET /debug/rollback/ultimo-pago/40 - Rollback último pago
        
        // Rutas de ingresos eliminadas - tabla eliminada del sistema
    });
}

// =====================================================================
// MANEJADOR DE 404 PERSONALIZADO
// =====================================================================

// COMENTADO TEMPORALMENTE PARA DESARROLLO: Capturar todas las rutas no encontradas que empiecen con 'admin'
// $routes->get('admin/(:any)', function() {
//     if (auth()->loggedIn() && isAdmin()) {
//         return redirect()->to('/admin/dashboard')->with('warning', 'La página solicitada no existe. Redirigido al dashboard.');
//     } else {
//         return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
//     }
// });
