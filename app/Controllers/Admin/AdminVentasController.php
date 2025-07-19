<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VentaModel;
use App\Models\ApartadoModel;
use App\Models\ClienteModel;
use App\Models\LoteModel;
use App\Models\UserModel;
use App\Models\ProyectoModel;
use App\Models\PerfilFinanciamientoModel;
use App\Models\StaffModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Entities\Venta;

class AdminVentasController extends BaseController
{
    protected $ventaModel;
    protected $apartadoModel;
    protected $clienteModel;
    protected $loteModel;
    protected $userModel;
    protected $proyectoModel;
    protected $perfilFinanciamientoModel;
    protected $staffModel;
    protected $tablaModel;
    protected $pagoModel;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
        $this->apartadoModel = new ApartadoModel();
        $this->clienteModel = new ClienteModel();
        $this->loteModel = new LoteModel();
        $this->userModel = new UserModel();
        $this->proyectoModel = new ProyectoModel();
        $this->perfilFinanciamientoModel = new PerfilFinanciamientoModel();
        $this->staffModel = new StaffModel();
        $this->tablaModel = new TablaAmortizacionModel();
        $this->pagoModel = new PagoVentaModel();
        
        // Cargar helpers necesarios para estado de cuenta
        helper(['estado_cuenta', 'amortizacion', 'recibo']);
    }

    public function index()
    {
        // Filtros desde URL
        $proyectoId = $this->request->getGet('proyecto_id');
        
        $lotes = $this->loteModel->getLotesDisponibles($proyectoId ? (int)$proyectoId : null);
        
        $data = [
            'title' => 'Catálogo de Lotes Disponibles',
            'lotes_disponibles' => $lotes,
            'proyectos' => $this->proyectoModel->findAll(),
            'estadisticas_lotes' => (object)[
                'total_lotes' => count($lotes),
                'lotes_disponibles' => count($lotes),
                'lotes_vendidos' => 0,
                'precio_promedio' => 0
            ]
        ];

        return view('admin/ventas/index', $data);
    }

    public function registradas()
    {
        $estatus = $this->request->getGet('estatus');
        $vendedorId = $this->request->getGet('vendedor_id');
        
        $ventas = $this->ventaModel->getVentasConRelaciones(50);
        
        $data = [
            'title' => 'Ventas Registradas',
            'ventas' => $ventas,
            'vendedores' => $this->staffModel->findAll(),
            'filtros' => [
                'estatus' => $estatus,
                'vendedor_id' => $vendedorId
            ]
        ];

        return view('admin/ventas/registradas', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Nueva Venta',
            'clientes' => $this->clienteModel->findAll(),
            'lotes' => $this->loteModel->getLotesDisponibles(),
            'vendedores' => $this->userModel->getVendedores(),
            'planes_financiamiento' => $this->perfilFinanciamientoModel->getConfiguracionesActivas(),
            'apartados' => $this->apartadoModel->getApartadosVigentes()
        ];

        return view('admin/ventas/create', $data);
    }

    public function store()
    {
        
        $rules = [
            'lote_id' => 'required|integer',
            'cliente_id' => 'required|integer',
            'vendedor_id' => 'required|integer',
            'perfil_financiamiento_id' => 'required|integer',
            'fecha_venta' => 'required|valid_date',
            'precio_lista' => 'required|decimal|greater_than[0]',
            'precio_venta_final' => 'required|decimal|greater_than[0]',
            'tipo_venta' => 'required|in_list[contado,financiado]'
        ];

        if (!$this->validate($rules)) {
            $erroresValidacion = $this->validator->getErrors();
            
            
            return redirect()->back()->withInput()->with('errors', $erroresValidacion);
        }

        // Verificar disponibilidad del lote
        $loteId = $this->request->getPost('lote_id');
        if ($this->ventaModel->loteTieneVentaActiva($loteId)) {
            return redirect()->back()->withInput()->with('error', 'El lote seleccionado no está disponible');
        }
        

        // Obtener user_id del staff seleccionado (mismo patrón que apartados)
        $staffId = $this->request->getPost('vendedor_id');
        $staff = $this->staffModel->find($staffId);
        $userId = $staff ? $staff->user_id : auth()->id(); // Fallback al usuario actual
        
        $data = [
            'folio_venta' => $this->request->getPost('folio_venta') ?: Venta::generarFolio(),
            'lote_id' => $this->request->getPost('lote_id'),
            'cliente_id' => $this->request->getPost('cliente_id'),
            'vendedor_id' => $userId, // Usar el user_id del staff, no el staff.id
            'perfil_financiamiento_id' => $this->request->getPost('perfil_financiamiento_id'),
            'apartado_id' => $this->request->getPost('apartado_id') ?: null,
            'fecha_venta' => $this->request->getPost('fecha_venta'),
            'precio_lista' => $this->request->getPost('precio_lista'),
            'descuento_aplicado' => $this->request->getPost('descuento_aplicado') ?: 0,
            'motivo_descuento' => $this->request->getPost('motivo_descuento'),
            'precio_venta_final' => $this->request->getPost('precio_venta_final'),
            'tipo_venta' => $this->request->getPost('tipo_venta'),
            'observaciones' => $this->request->getPost('observaciones')
        ];

        // Debug datos para insert
        
        
        if ($this->ventaModel->save($data)) {
            $ventaId = $this->ventaModel->getInsertID();
            
            
            // Si viene de un apartado, convertirlo
            if (!empty($data['apartado_id'])) {
                $this->apartadoModel->convertirEnVenta($data['apartado_id'], $ventaId);
            }
            
            // Determinar monto y tipo de ingreso según modalidad de venta
            $esCeroEnganche = $this->request->getPost('promocion_cero_enganche_aplicada') == '1';
            $tipoVenta = $data['tipo_venta']; // 'contado' o 'financiado'
            
            if ($tipoVenta === 'contado') {
                // Venta de contado: registrar precio total
                $montoIngreso = $data['precio_venta_final'];
                $tipoIngreso = 'otros'; // Pago completo
                
                // DEBUG: Verificar datos del formulario
                $referenciaPago = $this->request->getPost('referencia_pago');
                log_message('info', 'DEBUG VENTA - Referencia del formulario: ' . var_export($referenciaPago, true));
                
                // Registrar ingreso automáticamente
                $datosIngreso = [
                    'tipo_ingreso' => $tipoIngreso,
                    'monto' => $montoIngreso,
                    'metodo_pago' => $this->request->getPost('forma_pago') ?: 'efectivo',
                    'referencia' => $referenciaPago,
                    'cliente_id' => $data['cliente_id'],
                    'venta_id' => $ventaId,
                    'user_id' => auth()->id()
                ];
                
                log_message('info', 'DEBUG VENTA - Datos ingreso enviados: ' . json_encode($datosIngreso));
                $resultadoIngreso = crear_ingreso_automatico($datosIngreso);
                
            } elseif ($esCeroEnganche) {
                // CERO ENGANCHE: Registrar 2 pagos
                $referenciaPago = $this->request->getPost('referencia_pago');
                $metodoPago = $this->request->getPost('forma_pago') ?: 'efectivo';
                
                // 1. Registrar enganche de $0 (para procesos subsecuentes)
                $datosEnganche = [
                    'tipo_ingreso' => 'enganche',
                    'monto' => 0,
                    'metodo_pago' => $metodoPago,
                    'referencia' => $referenciaPago . '_ENG',
                    'cliente_id' => $data['cliente_id'],
                    'venta_id' => $ventaId,
                    'user_id' => auth()->id()
                ];
                
                log_message('info', 'DEBUG VENTA - Registrando enganche $0 para cero enganche: ' . json_encode($datosEnganche));
                $resultadoEnganche = crear_ingreso_automatico($datosEnganche);
                
                // 2. Registrar primera mensualidad
                $montoMensualidad = $this->request->getPost('monto_mensualidad_comision');
                $datosMensualidad = [
                    'tipo_ingreso' => 'mensualidad',
                    'monto' => $montoMensualidad,
                    'metodo_pago' => $metodoPago,
                    'referencia' => $referenciaPago,
                    'cliente_id' => $data['cliente_id'],
                    'venta_id' => $ventaId,
                    'user_id' => auth()->id()
                ];
                
                log_message('info', 'DEBUG VENTA - Registrando primera mensualidad: ' . json_encode($datosMensualidad));
                $resultadoIngreso = crear_ingreso_automatico($datosMensualidad);
                
                // Guardar el ID del ingreso de mensualidad para conectar con tabla amortización
                $ingresoMensualidadId = $resultadoIngreso['success'] ? $resultadoIngreso['ingreso_id'] : null;
                
            } else {
                $ingresoMensualidadId = null;
                // Financiado con enganche: registrar monto del enganche
                $montoEnganche = $this->request->getPost('monto_enganche');
                if (empty($montoEnganche) || $montoEnganche == 0) {
                    // Si no hay monto_enganche, usar total_a_pagar
                    $montoIngreso = $this->request->getPost('total_a_pagar') ?: 0;
                } else {
                    $montoIngreso = $montoEnganche;
                }
                $tipoIngreso = 'enganche';
                
                // DEBUG: Verificar datos del formulario
                $referenciaPago = $this->request->getPost('referencia_pago');
                log_message('info', 'DEBUG VENTA - Referencia del formulario: ' . var_export($referenciaPago, true));
                
                // Registrar ingreso automáticamente
                $datosIngreso = [
                    'tipo_ingreso' => $tipoIngreso,
                    'monto' => $montoIngreso,
                    'metodo_pago' => $this->request->getPost('forma_pago') ?: 'efectivo',
                    'referencia' => $referenciaPago,
                    'cliente_id' => $data['cliente_id'],
                    'venta_id' => $ventaId,
                    'user_id' => auth()->id()
                ];
                
                log_message('info', 'DEBUG VENTA - Datos ingreso enviados: ' . json_encode($datosIngreso));
                $resultadoIngreso = crear_ingreso_automatico($datosIngreso);
            }
            
            // Registrar comisión automáticamente SOLO si NO es apartado y el enganche fue liquidado
            $resultadoComision = ['success' => true, 'message' => 'Comisión no aplica para apartados'];
            
            // Para todos los tipos de venta excepto apartado, registrar comisión
            if ($tipoVenta !== 'apartado') {
                // Solo crear comisión cuando es venta real (enganche liquidado)
                $datosComision = [
                    'venta_id' => $ventaId,
                    'vendedor_id' => $userId, // Usar el mismo user_id mapeado
                    'precio_venta_final' => $data['precio_venta_final']
                ];
                
                $resultadoComision = crear_comision_automatica($datosComision);
            } else {
            }
            
            // BLOQUEAR LOTE: Cambiar estado de "Disponible" (0) o "Apartado" (1) a "Vendido" (2)
            try {
                $this->loteModel->cambiarEstado($data['lote_id'], \App\Entities\Lote::ESTADO_VENDIDO);
            } catch (\Exception $e) {
                log_message('error', "Error cambiando estado de lote en venta {$data['folio_venta']}: " . $e->getMessage());
            }
            
            // ACTUALIZAR ETAPA DEL CLIENTE: Cambiar a "cerrado"
            try {
                $resultadoEtapa = actualizar_etapa_cliente($data['cliente_id']);
                if ($resultadoEtapa['success']) {
                } else {
                }
            } catch (\Exception $e) {
                log_message('error', "Error actualizando etapa de cliente en venta {$data['folio_venta']}: " . $e->getMessage());
            }
            
            // GENERAR TABLA DE AMORTIZACIÓN PARA VENTAS FINANCIADAS
            $resultadoAmortizacion = ['success' => true, 'message' => 'Tabla de amortización no requerida para venta de contado'];
            
            if ($data['tipo_venta'] === 'financiado') {
                try {
                    // Obtener configuración financiera
                    $configuracionFinanciera = $this->perfilFinanciamientoModel->find($data['perfil_financiamiento_id']);
                    
                    if ($configuracionFinanciera) {
                        // Preparar configuración para generación de tabla
                        // Determinar número de pagos y tasa según criterios
                        $numeroPagos = 0;
                        $tasaInteres = $configuracionFinanciera->porcentaje_interes_anual;
                        
                        // 1. Cero Enganche - puede ser MSI o MCI
                        if ($configuracionFinanciera->promocion_cero_enganche) {
                            // Si es cero enganche con MSI
                            if ($configuracionFinanciera->tipo_financiamiento === 'msi' && $configuracionFinanciera->meses_sin_intereses > 0) {
                                $numeroPagos = $configuracionFinanciera->meses_sin_intereses;
                                $tasaInteres = 0; // Sin intereses
                            } 
                            // Si es cero enganche con MCI
                            else if ($configuracionFinanciera->tipo_financiamiento === 'mci' && $configuracionFinanciera->meses_con_intereses > 0) {
                                $numeroPagos = $configuracionFinanciera->meses_con_intereses;
                                // Mantiene la tasa de interés configurada
                            }
                        }
                        // 2. MSI - Meses Sin Intereses (sin ser cero enganche)
                        else if ($configuracionFinanciera->tipo_financiamiento === 'msi' && $configuracionFinanciera->meses_sin_intereses > 0) {
                            $numeroPagos = $configuracionFinanciera->meses_sin_intereses;
                            $tasaInteres = 0; // Sin intereses
                        }
                        // 3. MCI - Meses Con Intereses (tradicional)
                        else if ($configuracionFinanciera->tipo_financiamiento === 'mci' && $configuracionFinanciera->meses_con_intereses > 0) {
                            $numeroPagos = $configuracionFinanciera->meses_con_intereses;
                            // Mantiene la tasa de interés configurada
                        }
                        
                        // Validar que tengamos un número de pagos válido
                        if ($numeroPagos <= 0) {
                            throw new \Exception('El plan de financiamiento no tiene meses configurados');
                        }
                        
                        // Calcular monto a financiar según tipo de plan
                        $montoFinanciar = $data['precio_venta_final'];
                        if (!$esCeroEnganche) {
                            // Solo para planes normales, restar el enganche
                            $montoFinanciar = $data['precio_venta_final'] - ($this->request->getPost('monto_enganche') ?: 0);
                        }
                        
                        $configParaAmortizacion = [
                            'monto_financiar' => $montoFinanciar,
                            'tasa_interes_anual' => $tasaInteres,
                            'numero_pagos' => $numeroPagos,
                            'fecha_primer_pago' => date('Y-m-d', strtotime($data['fecha_venta'] . ' +1 month'))
                        ];
                        
                        // Generar tabla de amortización
                        $resultadoAmortizacion = generar_tabla_amortizacion($ventaId, $configParaAmortizacion);
                        
                        if ($resultadoAmortizacion['success']) {
                            log_message('info', "Tabla de amortización generada para venta {$data['folio_venta']}: {$resultadoAmortizacion['mensualidades_generadas']} mensualidades");
                            
                            // Crear registros de pagos vacíos para cada mensualidad
                            $this->crearRegistrosPagosVenta($ventaId, $resultadoAmortizacion['ids_insertados']);
                            
                            // Para planes de cero enganche, marcar la primera mensualidad como pagada
                            if ($esCeroEnganche && !empty($resultadoAmortizacion['ids_insertados']) && isset($ingresoMensualidadId)) {
                                $this->marcarPrimeraMensualidadPagada($ventaId, $resultadoAmortizacion['ids_insertados'][0], $ingresoMensualidadId);
                            }
                            
                        } else {
                            log_message('error', "Error generando tabla de amortización para venta {$data['folio_venta']}: " . $resultadoAmortizacion['error']);
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', "Excepción generando tabla de amortización para venta {$data['folio_venta']}: " . $e->getMessage());
                    $resultadoAmortizacion = ['success' => false, 'error' => $e->getMessage()];
                }
            }
            
            if ($resultadoIngreso['success'] && $resultadoComision['success']) {
                // Generar URL del recibo para apertura automática
                $reciboUrl = site_url('/admin/ventas/recibo/' . $ventaId);
                
                return redirect()->to('/admin/ventas/registradas')
                    ->with('success', "Venta {$data['folio_venta']} registrada exitosamente con ingreso y comisión")
                    ->with('venta_id', $ventaId)
                    ->with('folio_venta', $data['folio_venta'])
                    ->with('recibo_url', $reciboUrl)
                    ->with('open_recibo', true); // Flag para JavaScript
            } elseif ($resultadoIngreso['success']) {
                log_message('warning', "Venta {$data['folio_venta']} creada con ingreso pero error en comisión: " . $resultadoComision['error']);
                
                // Generar URL del recibo aún con warning
                $reciboUrl = site_url('/admin/ventas/recibo/' . $ventaId);
                
                return redirect()->to('/admin/ventas/registradas')
                    ->with('warning', "Venta {$data['folio_venta']} registrada con ingreso, pero error al registrar comisión")
                    ->with('recibo_url', $reciboUrl)
                    ->with('open_recibo', true);
            } else {
                log_message('warning', "Venta {$data['folio_venta']} creada pero error en ingreso: " . $resultadoIngreso['error']);
                return redirect()->to('/admin/ventas/registradas')->with('warning', "Venta {$data['folio_venta']} registrada, pero error al registrar ingreso y comisión");
            }
        } else {
            // Error al insertar venta
            $errorInfo = $this->ventaModel->errors();
            log_message('error', 'Error al insertar venta: ' . json_encode($errorInfo));
        }

        return redirect()->back()->withInput()->with('error', 'Error al registrar la venta');
    }

    public function show($id)
    {
        $venta = $this->ventaModel->find($id);
        
        if (!$venta) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Datos básicos
        $data = [
            'title' => 'Detalle de Venta',
            'venta' => $venta,
            'cliente' => $this->clienteModel->find($venta->cliente_id),
            'lote' => $this->loteModel->find($venta->lote_id),
            'vendedor' => $this->userModel->find($venta->vendedor_id),
            'plan' => $this->perfilFinanciamientoModel->find($venta->perfil_financiamiento_id)
        ];

        // Información de estado de cuenta si es venta financiada
        $data['estado_cuenta_disponible'] = false;
        $data['tabla_amortizacion_existe'] = false;
        $data['resumen_financiero'] = null;
        $data['mensualidades_resumen'] = null;

        if ($venta->tipo_venta === 'financiado') {
            // Verificar si existe tabla de amortización
            $tablaAmortizacion = $this->tablaModel->getByVenta($id);
            
            if (!empty($tablaAmortizacion)) {
                $data['tabla_amortizacion_existe'] = true;
                $data['estado_cuenta_disponible'] = true;
                
                // Obtener resumen financiero usando Entity method
                $data['resumen_financiero'] = $venta->getResumenFinanciero();
                
                // Obtener resumen de mensualidades usando helper
                $resumenAmortizacion = obtener_resumen_amortizacion($id);
                if ($resumenAmortizacion['success']) {
                    $data['mensualidades_resumen'] = $resumenAmortizacion['resumen'];
                }
                
                // Obtener próximas 3 mensualidades pendientes
                $data['proximas_mensualidades'] = $venta->getMensualidadesPendientes(3);
                
                // Verificar si hay mensualidades vencidas
                $mensualidadesVencidas = $this->tablaModel->getMensualidadesVencidas($venta->cliente_id, $id);
                $data['tiene_vencidas'] = count($mensualidadesVencidas) > 0;
                $data['total_vencidas'] = count($mensualidadesVencidas);
                $data['monto_vencido'] = array_sum(array_map(function($m) { 
                    return $m->getSaldoTotalPendiente(); 
                }, $mensualidadesVencidas));
            }
        }

        return view('admin/ventas/show', $data);
    }

    public function edit($id)
    {
        $venta = $this->ventaModel->find($id);
        
        if (!$venta) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$venta->estaActiva()) {
            return redirect()->back()->with('error', 'No se puede editar una venta que no está activa');
        }

        // Obtener datos específicos de la venta
        $cliente = $this->clienteModel->find($venta->cliente_id);
        $lote = $this->loteModel->find($venta->lote_id);
        
        $data = [
            'title' => 'Editar Venta',
            'venta' => $venta,
            'cliente' => $cliente,
            'lote' => $lote,
            'clientes' => $this->clienteModel->findAll(),
            'lotes' => $this->loteModel->getLotesDisponibles(),
            'vendedores' => $this->userModel->getVendedores(),
            'planes_financiamiento' => $this->perfilFinanciamientoModel->getConfiguracionesActivas()
        ];

        return view('admin/ventas/edit', $data);
    }

    public function update($id)
    {
        $venta = $this->ventaModel->find($id);
        
        if (!$venta) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'precio_lista' => 'required|decimal|greater_than[0]',
            'precio_venta_final' => 'required|decimal|greater_than[0]',
            'descuento_aplicado' => 'permit_empty|decimal|greater_than_equal_to[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'precio_lista' => $this->request->getPost('precio_lista'),
            'descuento_aplicado' => $this->request->getPost('descuento_aplicado') ?: 0,
            'motivo_descuento' => $this->request->getPost('motivo_descuento'),
            'precio_venta_final' => $this->request->getPost('precio_venta_final'),
            'observaciones' => $this->request->getPost('observaciones')
        ];

        if ($this->ventaModel->update($id, $data)) {
            return redirect()->to('/admin/ventas/' . $id)->with('success', 'Venta actualizada exitosamente');
        }

        return redirect()->back()->withInput()->with('error', 'Error al actualizar la venta');
    }

    public function cancelar($id)
    {
        $venta = $this->ventaModel->find($id);
        
        if (!$venta) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$venta->puedeCancelarse()) {
            return redirect()->back()->with('error', 'Esta venta no puede cancelarse');
        }

        if ($this->request->getMethod() === 'post') {
            $motivo = $this->request->getPost('motivo_cancelacion');
            
            if (empty($motivo)) {
                return redirect()->back()->with('error', 'El motivo de cancelación es obligatorio');
            }

            if ($this->ventaModel->actualizarEstatus($id, 'cancelada', $motivo)) {
                return redirect()->to('/admin/ventas')->with('success', 'Venta cancelada exitosamente');
            }

            return redirect()->back()->with('error', 'Error al cancelar la venta');
        }

        $data = [
            'title' => 'Cancelar Venta',
            'venta' => $venta
        ];

        return view('admin/ventas/cancelar', $data);
    }

    public function reportes()
    {
        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaFin = $this->request->getGet('fecha_fin');
        $vendedorId = $this->request->getGet('vendedor_id');

        $filtros = [];
        
        if ($fechaInicio) {
            $filtros['fecha_inicio'] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $filtros['fecha_fin'] = $fechaFin;
        }
        
        if ($vendedorId) {
            $filtros['vendedor_id'] = $vendedorId;
        }

        $data = [
            'title' => 'Reportes de Ventas',
            'ventas' => $this->ventaModel->getVentasConRelaciones($filtros),
            'estadisticas' => $this->ventaModel->getEstadisticasVentas($fechaInicio, $fechaFin),
            'vendedores' => $this->userModel->getVendedores(),
            'filtros' => $filtros
        ];

        return view('admin/ventas/reportes', $data);
    }

    public function historial()
    {
        $data = [
            'title' => 'Historial de Ventas',
            'ventas' => $this->ventaModel->getVentasConRelaciones([], 50),
            'estadisticas' => $this->ventaModel->getEstadisticasVentas()
        ];

        return view('admin/ventas/historial', $data);
    }

    /**
     * 🔄 REGENERADO: Configurar nueva venta para un lote específico
     */
    public function configurar($loteId)
    {
        
        // 1. OBTENER LOTE CON INFORMACIÓN BÁSICA
        $lote = $this->loteModel->select('
            lotes.*, 
            empresas.id as empresa_id,
            empresas.nombre as empresa_nombre,
            proyectos.nombre as proyecto_nombre,
            manzanas.nombre as manzana_nombre,
            tipos_lotes.nombre as tipo_nombre
        ')
        ->join('manzanas', 'lotes.manzanas_id = manzanas.id', 'left')
        ->join('proyectos', 'manzanas.proyectos_id = proyectos.id', 'left')
        ->join('empresas', 'proyectos.empresas_id = empresas.id', 'left')
        ->join('tipos_lotes', 'lotes.tipos_lotes_id = tipos_lotes.id', 'left')
        ->where('lotes.id', $loteId)
        ->first();

        if (!$lote) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 2. CARGAR MODELOS
        $perfilModel = new \App\Models\PerfilFinanciamientoModel();
        $clienteModel = new \App\Models\ClienteModel();
        $staffModel = new \App\Models\StaffModel();

        // 3. OBTENER PLANES DE FINANCIAMIENTO FILTRADOS POR CARACTERÍSTICAS DEL LOTE
        $tipoTerreno = $this->determinarTipoTerreno($lote);
        $planesFinanciamiento = $perfilModel->getPlanesParaLote($lote, $tipoTerreno);
        
        // DEBUG CONTROLLER: Verificar datos antes de enviar a vista
        echo "<!-- DEBUG CONTROLLER configurar() -->\n";
        echo "<!-- Lote ID: {$lote->id}, Empresa ID: {$lote->empresa_id} -->\n";
        echo "<!-- Área del lote: {$lote->area} m² -->\n";
        echo "<!-- Precio del lote: $" . number_format($lote->precio_total, 2) . " -->\n";
        echo "<!-- Tipo de terreno: {$tipoTerreno} -->\n";
        echo "<!-- Total planes encontrados FILTRADOS: " . count($planesFinanciamiento) . " -->\n";
        
        foreach($planesFinanciamiento as $i => $plan) {
            echo "<!-- Plan[$i]: -->\n";
            echo "<!--   ID: {$plan->id} -->\n";
            echo "<!--   Nombre: {$plan->nombre} -->\n";
            echo "<!--   MSI: {$plan->meses_sin_intereses} -->\n";
            echo "<!--   MCI: {$plan->meses_con_intereses} -->\n";
            echo "<!--   Tipo Comisión: {$plan->tipo_comision} -->\n";
            echo "<!--   % Comisión: {$plan->porcentaje_comision} -->\n";
        }
        echo "<!-- FIN DEBUG CONTROLLER -->\n";

        // 4. PREPARAR DATOS LIMPIOS PARA LA VISTA
        $data = [
            'title' => 'Configurar Venta - Lote ' . $lote->clave,
            'lote' => $lote,
            'planes_financiamiento' => $planesFinanciamiento,
            'clientes' => $clienteModel->findAll(),
            'vendedores' => $staffModel->findAll()
        ];


        return view('admin/ventas/configurar', $data);
    }

    /**
     * AJAX: Filtrar configuraciones por lote
     */
    public function filtrarConfiguraciones()
    {
        
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso no permitido']);
        }

        $loteId = $this->request->getPost('lote_id');
        
        if (!$loteId) {
            return $this->response->setJSON(['error' => 'Lote ID requerido']);
        }

        $perfilFinanciamientoModel = new PerfilFinanciamientoModel();
        $configuraciones = $perfilFinanciamientoModel->getConfiguracionesParaLote($loteId, true);

        log_message('debug', "[AJAX_FILTRO] Lote {$loteId} - Configuraciones encontradas: " . count($configuraciones));

        return $this->response->setJSON([
            'success' => true,
            'configuraciones' => $configuraciones,
            'total' => count($configuraciones)
        ]);
    }

    /**
     * Imprimir tabla de amortización en nueva pestaña (vista minimalista)
     */
    public function imprimirAmortizacion()
    {
        // Si es una petición GET, mostrar página informativa
        if ($this->request->getMethod() === 'get') {
            return redirect()->to('/admin/ventas')->with('info', 'Para imprimir una tabla de amortización, debe configurar primero una venta desde el catálogo de lotes.');
        }
        
        // Validar datos recibidos por POST
        $datosPost = $this->request->getPost();
        
        if (!$datosPost || !isset($datosPost['pagos']) || !isset($datosPost['datos'])) {
            return redirect()->to('/admin/ventas')->with('error', 'Datos de amortización faltantes. Configure una venta primero.');
        }
        
        // Decodificar datos JSON
        $pagos = json_decode($datosPost['pagos'], true);
        $datos = json_decode($datosPost['datos'], true);
        
        if (!$pagos || !$datos) {
            return redirect()->to('/admin/ventas')->with('error', 'Datos de amortización inválidos. Verifique la configuración de la venta.');
        }
        
        // Preparar datos para la vista de impresión
        $datosVista = [
            'pagos' => $pagos,
            'datos' => $datos
        ];
        
        // Renderizar vista de impresión minimalista
        return view('admin/ventas/imprimir_amortizacion', $datosVista);
    }

    /**
     * Determinar tipo de terreno basado en el lote
     */
    private function determinarTipoTerreno($lote): string
    {
        $tipoTerreno = 'habitacional'; // Default
        
        if (!empty($lote->tipo_nombre)) {
            $tipoLoteNombre = strtolower($lote->tipo_nombre);
            if (strpos($tipoLoteNombre, 'comercial') !== false) {
                $tipoTerreno = 'comercial';
            }
        }
        
        if (!empty($lote->categoria_nombre) && strtolower($lote->categoria_nombre) === 'comercial') {
            $tipoTerreno = 'comercial';
        }
        
        return $tipoTerreno;
    }

    public function generarFolio()
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['folio' => Venta::generarFolio()]);
        }
        
        return redirect()->back();
    }

    /**
     * AJAX: Filtrar financiamientos en tiempo real
     */
    public function filtrarFinanciamientos()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $debugMode = ENVIRONMENT === 'development' || $this->request->getPost('debug') === '1';
        
        $criterios = [
            'tipo_terreno' => $this->request->getPost('tipo_terreno'),
            'area_m2' => $this->request->getPost('area_m2'),
            'precio_lote' => $this->request->getPost('precio_lote'),
            'empresa_id' => $this->request->getPost('empresa_id'),
            'proyecto_id' => $this->request->getPost('proyecto_id')
        ];

        // Remover criterios vacíos
        $criterios = array_filter($criterios, function($value) {
            return $value !== null && $value !== '';
        });

        if ($debugMode) {
            log_message('debug', "[AJAX_FILTRO] Criterios recibidos: " . json_encode($criterios));
        }

        $configuraciones = $this->perfilFinanciamientoModel->getConfiguracionesFiltradas($criterios, $debugMode);

        $resultado = [
            'success' => true,
            'total' => count($configuraciones),
            'configuraciones' => [],
            'debug_info' => $debugMode ? [
                'criterios' => $criterios,
                'total_encontradas' => count($configuraciones)
            ] : null
        ];

        foreach ($configuraciones as $config) {
            $resultado['configuraciones'][] = [
                'id' => $config->id,
                'nombre' => $config->nombre,
                'empresa_nombre' => $config->empresa_nombre,
                'proyecto_nombre' => $config->proyecto_nombre ?? 'Global',
                'descripcion_resumida' => $config->descripcion_resumida,
                'motivo_compatibilidad' => $config->motivo_compatibilidad,
                'es_default' => $config->es_default,
                'apartado_minimo' => $config->apartado_minimo,
                'enganche_minimo' => $config->enganche_minimo,
                'plazo_liquidar_enganche' => $config->plazo_liquidar_enganche,
                'meses_sin_intereses' => $config->meses_sin_intereses,
                'meses_con_intereses' => $config->meses_con_intereses,
                'porcentaje_interes_anual' => $config->porcentaje_interes_anual,
                'promocion_cero_enganche' => $config->promocion_cero_enganche,
                'tipo_comision' => $config->tipo_comision,
                'porcentaje_comision' => $config->porcentaje_comision,
                'comision_fija' => $config->comision_fija,
                'mensualidades_comision' => $config->mensualidades_comision,
                'dias_anticipo' => $config->dias_anticipo
            ];
        }

        return $this->response->setJSON($resultado);
    }

    public function datatables()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $proyectoId = $this->request->getGet('proyecto_id');
        $lotes = $this->loteModel->getLotesDisponibles($proyectoId ? (int)$proyectoId : null);

        $data = [];
        $index = 1;
        foreach ($lotes as $lote) {
            // Columna Dimensiones con botón desplegable
            $dimensiones_display = '<div class="d-flex align-items-center justify-content-between">';
            $dimensiones_display .= '<div class="dimension-main">';
            $dimensiones_display .= '<strong class="text-primary" style="font-size: 0.95rem;">' . number_format($lote->area, 2) . ' m²</strong>';
            $dimensiones_display .= '</div>';
            $dimensiones_display .= '<button class="btn btn-sm btn-outline-info btn-toggle-details ml-2" title="Ver más detalles">';
            $dimensiones_display .= '<i class="fas fa-plus"></i>';
            $dimensiones_display .= '</button>';
            $dimensiones_display .= '</div>';

            // Detalles completos para child row
            $detalles_terreno = [
                'area_total' => number_format($lote->area, 2) . ' m²',
                'frente' => !empty($lote->frente) ? number_format($lote->frente, 2) . ' m' : 'N/A',
                'fondo' => !empty($lote->fondo) ? number_format($lote->fondo, 2) . ' m' : 'N/A',
                'lateral_izquierdo' => !empty($lote->lateral_izquierdo) ? number_format($lote->lateral_izquierdo, 2) . ' m' : 'N/A',
                'lateral_derecho' => !empty($lote->lateral_derecho) ? number_format($lote->lateral_derecho, 2) . ' m' : 'N/A',
                'construccion' => !empty($lote->construccion) && $lote->construccion > 0 ? number_format($lote->construccion, 2) . ' m²' : 'N/A',
                'descripcion' => !empty($lote->descripcion) ? esc($lote->descripcion) : 'Sin descripción adicional',
                'coordenadas' => (!empty($lote->latitud) && !empty($lote->longitud)) ? 
                    'Lat: ' . $lote->latitud . ', Lng: ' . $lote->longitud : 'No disponibles'
            ];

            $data[] = [
                'DT_RowId' => 'lote_' . $lote->id,
                'indice' => $index,
                'clave' => esc($lote->clave),
                'empresa' => esc($lote->empresa_nombre ?? 'N/A'),
                'proyecto' => esc($lote->proyecto_nombre ?? 'N/A'),
                'tipo' => esc($lote->tipo_nombre ?? 'Lote'),
                'division' => esc($lote->division_nombre ?? 'N/A'),
                'manzana' => esc($lote->manzana_nombre ?? $lote->manzana_clave ?? 'N/A'),
                'categoria' => esc($lote->categoria_nombre ?? 'Sin categoría'),
                'numero_lote' => esc($lote->numero ?? 'N/A'),
                'dimensiones' => $dimensiones_display,
                'precio_m2' => '$' . number_format($lote->precio_m2, 0),
                'total' => '$' . number_format($lote->precio_total, 0),
                'accion' => '<a href="' . site_url('/admin/ventas/configurar/' . $lote->id) . '" class="btn btn-success btn-sm"><i class="fas fa-shopping-cart"></i> Comprar</a>',
                'detalles_terreno' => $detalles_terreno // Datos para child row
            ];
            $index++;
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Generar recibo de pago de venta
     */
    public function recibo($id)
    {
        helper(['recibo', 'format']);
        
        $venta = $this->ventaModel->find($id);
        
        if (!$venta) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Obtener datos relacionados
        $cliente = $this->clienteModel->find($venta->cliente_id);
        $lote = $this->loteModel->find($venta->lote_id);
        $configuracion = $this->perfilFinanciamientoModel->find($venta->perfil_financiamiento_id);
        
        // Obtener vendedor con datos de staff (nombres completos)
        $db = \Config\Database::connect();
        $vendedorQuery = $db->table('users u')
                           ->select('u.id, u.username, s.nombres, s.apellido_paterno, s.apellido_materno')
                           ->join('staff s', 's.user_id = u.id', 'left')
                           ->where('u.id', $venta->vendedor_id)
                           ->get()
                           ->getRow();
        
        // Crear objeto vendedor con nombres completos
        $vendedor = (object)[
            'id' => $vendedorQuery->id ?? $venta->vendedor_id,
            'username' => $vendedorQuery->username ?? 'Usuario',
            'nombres' => $vendedorQuery->nombres ?? '',
            'apellido_paterno' => $vendedorQuery->apellido_paterno ?? '',
            'apellido_materno' => $vendedorQuery->apellido_materno ?? '',
            'nombre_completo' => trim(($vendedorQuery->nombres ?? '') . ' ' . 
                                    ($vendedorQuery->apellido_paterno ?? '') . ' ' . 
                                    ($vendedorQuery->apellido_materno ?? '')) ?: ($vendedorQuery->username ?? 'Usuario')
        ];
        
        // Obtener ingreso relacionado
        $db = \Config\Database::connect();
        $ingreso = $db->table('ingresos')
                     ->where('venta_id', $id)
                     ->orderBy('id', 'DESC')
                     ->get()
                     ->getRow();
        
        // Preparar datos del recibo usando helper
        $datosRecibo = generar_datos_recibo([
            'tipo' => 'VENTA',
            'folio' => $ingreso->folio ?? $venta->folio_venta,
            'monto' => $ingreso->monto ?? $venta->precio_venta_final,
            'fecha' => $venta->fecha_venta,
            'referencia' => $ingreso->referencia ?? '',
            'metodo_pago' => ucfirst($ingreso->metodo_pago ?? 'efectivo'),
            'venta' => $venta,
            'cliente' => $cliente,
            'lote' => $lote,
            'vendedor' => $vendedor,
            'configuracion' => $configuracion,
            'ingreso' => $ingreso
        ]);
        
        return view('admin/shared/recibo', $datosRecibo);
    }

    /**
     * Ver estado de cuenta de una venta específica
     */
    public function estadoCuenta(int $ventaId)
    {
        try {
            $venta = $this->ventaModel->find($ventaId);
            
            if (!$venta) {
                return redirect()->back()->with('error', 'Venta no encontrada');
            }

            // Redirigir al controller de estado de cuenta
            return redirect()->to('/admin/estado-cuenta/venta/' . $ventaId);

        } catch (\Exception $e) {
            log_message('error', 'Error accediendo a estado de cuenta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al acceder al estado de cuenta');
        }
    }

    /**
     * Generar tabla de amortización para venta existente
     */
    public function generarAmortizacion(int $ventaId)
    {
        try {
            $venta = $this->ventaModel->find($ventaId);
            
            if (!$venta) {
                return redirect()->back()->with('error', 'Venta no encontrada');
            }

            // Verificar si ya tiene tabla de amortización
            $tablaExistente = $this->tablaModel->getByVenta($ventaId);
            if (!empty($tablaExistente)) {
                return redirect()->back()->with('warning', 'Esta venta ya tiene una tabla de amortización generada');
            }

            // Solo para ventas financiadas
            if ($venta->tipo_venta !== 'financiado') {
                return redirect()->back()->with('error', 'Solo se puede generar tabla de amortización para ventas financiadas');
            }

            // Obtener configuración financiera
            $configuracionFinanciera = $this->perfilFinanciamientoModel->find($venta->perfil_financiamiento_id);
            
            if (!$configuracionFinanciera) {
                return redirect()->back()->with('error', 'No se encontró la configuración financiera');
            }

            // Preparar configuración para generación
            // Determinar número de pagos y tasa según criterios
            $numeroPagos = 0;
            $tasaInteres = $configuracionFinanciera->porcentaje_interes_anual;
            
            // 1. Cero Enganche - puede ser MSI o MCI
            if ($configuracionFinanciera->promocion_cero_enganche) {
                // Si es cero enganche con MSI
                if ($configuracionFinanciera->tipo_financiamiento === 'msi' && $configuracionFinanciera->meses_sin_intereses > 0) {
                    $numeroPagos = $configuracionFinanciera->meses_sin_intereses;
                    $tasaInteres = 0; // Sin intereses
                } 
                // Si es cero enganche con MCI
                else if ($configuracionFinanciera->tipo_financiamiento === 'mci' && $configuracionFinanciera->meses_con_intereses > 0) {
                    $numeroPagos = $configuracionFinanciera->meses_con_intereses;
                    // Mantiene la tasa de interés configurada
                }
            }
            // 2. MSI - Meses Sin Intereses (sin ser cero enganche)
            else if ($configuracionFinanciera->tipo_financiamiento === 'msi' && $configuracionFinanciera->meses_sin_intereses > 0) {
                $numeroPagos = $configuracionFinanciera->meses_sin_intereses;
                $tasaInteres = 0; // Sin intereses
            }
            // 3. MCI - Meses Con Intereses (tradicional)
            else if ($configuracionFinanciera->tipo_financiamiento === 'mci' && $configuracionFinanciera->meses_con_intereses > 0) {
                $numeroPagos = $configuracionFinanciera->meses_con_intereses;
                // Mantiene la tasa de interés configurada
            }
            
            // Validar que tengamos un número de pagos válido
            if ($numeroPagos <= 0) {
                return redirect()->back()->with('error', 'El plan de financiamiento no tiene meses configurados');
            }
            
            $configParaAmortizacion = [
                'monto_financiar' => $venta->precio_venta_final - ($venta->monto_enganche ?: 0),
                'tasa_interes_anual' => $tasaInteres,
                'numero_pagos' => $numeroPagos,
                'fecha_primer_pago' => date('Y-m-d', strtotime($venta->fecha_venta . ' +1 month'))
            ];

            // Generar tabla
            $resultado = generar_tabla_amortizacion($ventaId, $configParaAmortizacion);
            
            if ($resultado['success']) {
                // Crear registros de pagos
                $this->crearRegistrosPagosVenta($ventaId, $resultado['ids_insertados']);
                
                return redirect()->back()->with('success', 
                    "Tabla de amortización generada: {$resultado['mensualidades_generadas']} mensualidades por ${$resultado['pago_mensual']} cada una"
                );
            } else {
                return redirect()->back()->with('error', 'Error generando tabla: ' . $resultado['error']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error generando amortización manual: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar tabla de amortización');
        }
    }

    /**
     * Aplicar pago rápido desde vista de venta
     */
    public function aplicarPagoRapido(int $ventaId)
    {
        try {
            // Validar datos básicos
            $rules = [
                'monto_pago' => 'required|decimal|greater_than[0]',
                'forma_pago' => 'required|in_list[efectivo,transferencia,cheque,tarjeta,deposito]',
                'concepto_pago' => 'required'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            $venta = $this->ventaModel->find($ventaId);
            if (!$venta) {
                return redirect()->back()->with('error', 'Venta no encontrada');
            }

            // Preparar datos del pago
            $datosParaPago = [
                'venta_id' => $ventaId,
                'tabla_amortizacion_id' => $this->request->getPost('tabla_amortizacion_id'), // Opcional
                'monto_pago' => (float)$this->request->getPost('monto_pago'),
                'forma_pago' => $this->request->getPost('forma_pago'),
                'concepto_pago' => $this->request->getPost('concepto_pago'),
                'descripcion_concepto' => $this->request->getPost('descripcion_concepto') ?? '',
                'fecha_pago' => $this->request->getPost('fecha_pago') ?: date('Y-m-d'),
                'registrado_por' => auth()->id(),
                'referencia_pago' => $this->request->getPost('referencia_pago') ?? null,
                'cuenta_bancaria_id' => $this->request->getPost('cuenta_bancaria_id') ?? null
            ];

            // Procesar el pago usando el model
            $resultadoPago = $this->pagoModel->procesarPago($datosParaPago);
            
            if (!$resultadoPago['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $resultadoPago['error']);
            }

            // Generar recibo si el pago fue exitoso
            generar_recibo_mensualidad($resultadoPago['pago_id']);
            
            $mensaje = 'Pago aplicado exitosamente. Folio: ' . $resultadoPago['folio_generado'];
            
            return redirect()->back()
                ->with('success', $mensaje)
                ->with('pago_id', $resultadoPago['pago_id'])
                ->with('mostrar_recibo', true);

        } catch (\Exception $e) {
            log_message('error', 'Error aplicando pago rápido: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar pago: ' . $e->getMessage());
        }
    }

    // ==========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==========================================

    /**
     * Crea registros de pagos_ventas para cada mensualidad de la tabla de amortización
     */
    private function crearRegistrosPagosVenta(int $ventaId, array $idsTablaAmortizacion): void
    {
        try {
            log_message('debug', "crearRegistrosPagosVenta - Venta: {$ventaId}, IDs recibidos: " . json_encode($idsTablaAmortizacion));
            
            $exitosos = 0;
            $fallidos = 0;
            $numeroSecuencial = 1;
            
            foreach ($idsTablaAmortizacion as $tablaAmortizacionId) {
                $dataPago = [
                    'venta_id' => $ventaId,
                    'tabla_amortizacion_id' => $tablaAmortizacionId,
                    'folio_pago' => 'PENDIENTE-' . str_pad($ventaId, 6, '0', STR_PAD_LEFT) . '-' . str_pad($numeroSecuencial, 3, '0', STR_PAD_LEFT),
                    'monto_pago' => 0.00,
                    'forma_pago' => 'efectivo', // Valor por defecto requerido
                    'concepto_pago' => 'mensualidad', // Enum correcto
                    'descripcion_concepto' => 'Mensualidad pendiente de pago',
                    'fecha_pago' => date('Y-m-d H:i:s'), // Fecha requerida
                    'estatus_pago' => 'pendiente',
                    'referencia_pago' => null,
                    'numero_mensualidad' => null, // Se actualizará desde la tabla de amortización
                    'registrado_por' => auth()->id(),
                    'cuenta_bancaria_id' => null,
                    'motivo_cancelacion' => null,
                    'usuario_cancela_id' => null, // Campo corregido
                    'fecha_cancelacion' => null
                ];

                $insertResult = $this->pagoModel->insert($dataPago);
                
                if (!$insertResult) {
                    $fallidos++;
                    $errors = $this->pagoModel->errors();
                    log_message('error', "Error insertando pago para tabla_amortizacion_id {$tablaAmortizacionId}: " . json_encode($errors));
                    log_message('debug', "Datos del pago que falló: " . json_encode($dataPago));
                } else {
                    $exitosos++;
                    log_message('debug', "Pago insertado exitosamente para tabla_amortizacion_id {$tablaAmortizacionId}, ID: {$insertResult}");
                }
                
                $numeroSecuencial++;
            }

            log_message('info', "Registros de pagos - Venta {$ventaId}: {$exitosos} exitosos, {$fallidos} fallidos de " . count($idsTablaAmortizacion) . " intentados");

        } catch (\Exception $e) {
            log_message('error', "Error creando registros de pagos para venta {$ventaId}: " . $e->getMessage());
        }
    }
    
    /**
     * Marca la primera mensualidad como pagada para planes de cero enganche
     */
    private function marcarPrimeraMensualidadPagada(int $ventaId, int $primerTablaAmortizacionId, ?int $ingresoMensualidadId = null): void
    {
        try {
            // Obtener la venta para datos adicionales
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta) {
                throw new \Exception("Venta {$ventaId} no encontrada");
            }
            
            // Obtener la primera mensualidad de la tabla de amortización
            $primeraMensualidad = $this->tablaModel->find($primerTablaAmortizacionId);
            if (!$primeraMensualidad) {
                throw new \Exception("Primera mensualidad no encontrada en tabla de amortización");
            }
            
            // Actualizar la tabla de amortización - marcar como pagada
            // Nota: saldo_pendiente es columna generada, no se puede actualizar directamente
            $this->tablaModel->update($primerTablaAmortizacionId, [
                'estatus' => 'pagada',
                'fecha_ultimo_pago' => date('Y-m-d'),
                'monto_pagado' => $primeraMensualidad->monto_total,
                'numero_pagos_aplicados' => 1
            ]);
            
            // Actualizar el registro de pago correspondiente
            // Hacer múltiples intentos para encontrar el registro (resolver problemas de timing)
            $pagoMensualidad = null;
            $intentos = 0;
            $maxIntentos = 3;
            
            while ($intentos < $maxIntentos && !$pagoMensualidad) {
                $pagoMensualidad = $this->pagoModel->where('venta_id', $ventaId)
                                                    ->where('tabla_amortizacion_id', $primerTablaAmortizacionId)
                                                    ->first();
                
                if (!$pagoMensualidad) {
                    $intentos++;
                    log_message('debug', "Intento {$intentos}/{$maxIntentos} - Buscando registro de pago para venta {$ventaId}, tabla_amortizacion_id {$primerTablaAmortizacionId}");
                    
                    // Breve pausa para permitir que se complete la inserción
                    if ($intentos < $maxIntentos) {
                        usleep(100000); // 100ms
                    }
                }
            }
            
            if ($pagoMensualidad) {
                $updateData = [
                    'folio_pago' => 'CERO-ENG-' . str_pad($ventaId, 6, '0', STR_PAD_LEFT) . '-001',
                    'monto_pago' => $primeraMensualidad->monto_total,
                    'forma_pago' => 'promocional',
                    'concepto_pago' => 'mensualidad',
                    'descripcion_concepto' => 'Primera mensualidad - Plan Cero Enganche',
                    'fecha_pago' => date('Y-m-d H:i:s'),
                    'estatus_pago' => 'aplicado',
                    'referencia_pago' => 'CERO-ENGANCHE-AUTO',
                    'numero_mensualidad' => 1
                ];
                
                // Conectar con el ingreso registrado si existe
                if ($ingresoMensualidadId) {
                    $updateData['ingreso_id'] = $ingresoMensualidadId;
                }
                
                $updateResult = $this->pagoModel->update($pagoMensualidad->id, $updateData);
                
                if ($updateResult) {
                    log_message('info', "Pago actualizado exitosamente para mensualidad {$primerTablaAmortizacionId}: estatus_pago = aplicado (intento {$intentos})");
                } else {
                    log_message('error', "Error actualizando pago para mensualidad {$primerTablaAmortizacionId}: " . json_encode($this->pagoModel->errors()));
                    log_message('debug', "Datos de actualización: " . json_encode($updateData));
                }
            } else {
                log_message('warning', "No se encontró registro de pago para la primera mensualidad de la venta {$ventaId} después de {$maxIntentos} intentos");
                
                // Listar todos los pagos de la venta para debugging
                $todosPagos = $this->pagoModel->where('venta_id', $ventaId)->findAll();
                log_message('debug', "Registros de pagos existentes para venta {$ventaId}: " . count($todosPagos));
                foreach ($todosPagos as $pago) {
                    log_message('debug', "Pago ID: {$pago->id}, tabla_amortizacion_id: {$pago->tabla_amortizacion_id}, estatus: {$pago->estatus_pago}");
                }
            }
            
            // Verificar que ambas tablas estén sincronizadas
            $mensualidadActualizada = $this->tablaModel->find($primerTablaAmortizacionId);
            $pagoActualizado = $this->pagoModel->where('venta_id', $ventaId)
                                               ->where('tabla_amortizacion_id', $primerTablaAmortizacionId)
                                               ->first();
            
            if ($mensualidadActualizada && $pagoActualizado) {
                log_message('info', "SYNC CHECK - Venta {$ventaId}: tabla_amortizacion.estatus = {$mensualidadActualizada->estatus}, pagos_ventas.estatus_pago = {$pagoActualizado->estatus_pago}");
                
                // Verificar que el estado esté correctamente sincronizado
                if ($mensualidadActualizada->estatus === 'pagada' && $pagoActualizado->estatus_pago === 'aplicado') {
                    log_message('info', "SYNC SUCCESS - Primera mensualidad marcada como pagada para venta cero enganche {$ventaId}");
                } else {
                    log_message('error', "SYNC FAILED - Inconsistencia de estados para venta {$ventaId}: tabla_amortizacion.estatus = {$mensualidadActualizada->estatus}, pagos_ventas.estatus_pago = {$pagoActualizado->estatus_pago}");
                }
            } else {
                log_message('error', "SYNC FAILED - No se pudieron verificar los registros actualizados para venta {$ventaId}");
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error marcando primera mensualidad como pagada para venta {$ventaId}: " . $e->getMessage());
        }
    }
}