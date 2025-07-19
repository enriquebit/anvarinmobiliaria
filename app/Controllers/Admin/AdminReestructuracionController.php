<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReestructuracionModel;
use App\Models\ReestructuracionDetalleModel;
use App\Models\ReestructuracionHistorialModel;
use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\ClienteModel;

class AdminReestructuracionController extends BaseController
{
    protected $reestructuracionModel;
    protected $reestructuracionDetalleModel;
    protected $historialModel;
    protected $ventaModel;
    protected $tablaAmortizacionModel;
    protected $clienteModel;
    protected $db;

    public function __construct()
    {
        $this->reestructuracionModel = new ReestructuracionModel();
        $this->reestructuracionDetalleModel = new ReestructuracionDetalleModel();
        $this->historialModel = new ReestructuracionHistorialModel();
        $this->ventaModel = new VentaModel();
        $this->tablaAmortizacionModel = new TablaAmortizacionModel();
        $this->clienteModel = new ClienteModel();
        $this->db = \Config\Database::connect();
        
        helper(['reestructuracion', 'format', 'amortizacion']);
    }

    /**
     * Dashboard principal de reestructuraciones
     */
    public function index()
    {
        try {
            // Obtener estadísticas generales
            $estadisticas = $this->reestructuracionModel->getEstadisticasReestructuraciones();
            
            // Obtener reestructuraciones pendientes de autorización
            $pendientes = $this->reestructuracionModel->getReestructuracionesPendientes();
            
            // Obtener reestructuraciones activas
            $activas = $this->reestructuracionModel->getReestructuracionesActivas();
            
            // Obtener actividad reciente
            $actividadReciente = $this->historialModel->getActividadReciente(10);
            
            // Obtener estadísticas de actividad
            $estadisticasActividad = $this->historialModel->getEstadisticasActividad();

            $data = [
                'title' => 'Reestructuración de Cartera',
                'subtitle' => 'Gestión de convenios y reestructuraciones de cuentas en estado jurídico',
                'estadisticas' => $estadisticas,
                'pendientes_autorizacion' => $pendientes,
                'reestructuraciones_activas' => $activas,
                'actividad_reciente' => $actividadReciente,
                'estadisticas_actividad' => $estadisticasActividad,
                'fecha_actualizacion' => date('d/m/Y H:i:s')
            ];

            return view('admin/reestructuracion/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en dashboard reestructuración: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Lista todas las reestructuraciones con filtros (método view estándar)
     */
    public function view()
    {
        try {
            $filtros = $this->request->getGet();
            
            // Buscar reestructuraciones
            $reestructuraciones = $this->reestructuracionModel->buscarReestructuraciones($filtros);
            
            // Obtener estadísticas de la búsqueda
            $estadisticasBusqueda = $this->calcularEstadisticasBusqueda($reestructuraciones);
            
            // Opciones para filtros
            $opcionesFiltros = [
                'estados' => [
                    'propuesta' => 'Propuesta',
                    'autorizada' => 'Autorizada',
                    'firmada' => 'Firmada',
                    'activa' => 'Activa',
                    'cancelada' => 'Cancelada'
                ]
            ];

            $data = [
                'title' => 'Gestión de Reestructuraciones',
                'reestructuraciones' => $reestructuraciones,
                'filtros_aplicados' => $filtros,
                'estadisticas_busqueda' => $estadisticasBusqueda,
                'opciones_filtros' => $opcionesFiltros
            ];

            return view('admin/reestructuracion/view', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en view reestructuraciones: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar lista: ' . $e->getMessage());
        }
    }

    /**
     * Muestra las ventas que pueden ser reestructuradas
     */
    public function ventasElegibles()
    {
        try {
            // Obtener ventas en estado jurídico
            $ventasJuridico = $this->ventaModel
                ->select('ventas.*, c.nombres, c.apellido_paterno, c.apellido_materno, c.telefono, l.clave as clave_lote')
                ->join('clientes c', 'c.id = ventas.cliente_id')
                ->join('lotes l', 'l.id = ventas.lote_id')
                ->where('ventas.estatus_venta', 'juridico')
                ->where('ventas.tipo_venta', 'financiado')
                ->orderBy('ventas.fecha_venta', 'DESC')
                ->findAll();

            // Calcular datos adicionales para cada venta
            foreach ($ventasJuridico as $venta) {
                // Obtener saldo pendiente
                $saldoPendiente = $this->calcularSaldoPendienteVenta($venta->id);
                $venta->saldo_pendiente = $saldoPendiente;
                
                // Verificar si ya tiene reestructuraciones
                $reestructuracionesExistentes = $this->reestructuracionModel->getReestructuracionesByVenta($venta->id);
                $venta->reestructuraciones_existentes = count($reestructuracionesExistentes);
                $venta->puede_reestructurar = count($reestructuracionesExistentes) === 0 || 
                    end($reestructuracionesExistentes)->estatus === 'cancelada';
            }

            $data = [
                'title' => 'Ventas Elegibles para Reestructuración',
                'ventas_juridico' => $ventasJuridico,
                'total_ventas' => count($ventasJuridico),
                'fecha_actualizacion' => date('d/m/Y H:i:s')
            ];

            return view('admin/reestructuracion/ventas_elegibles', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en ventas elegibles: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar ventas elegibles: ' . $e->getMessage());
        }
    }

    /**
     * Muestra formulario para crear nueva reestructuración
     */
    public function create($ventaId = null)
    {
        try {
            // Validar que se proporcione una venta
            if (!$ventaId) {
                return redirect()->to('/admin/reestructuracion/ventas-elegibles')
                                ->with('error', 'Debe seleccionar una venta para reestructurar');
            }

            // Obtener datos de la venta
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta) {
                return redirect()->back()->with('error', 'Venta no encontrada');
            }

            // Validar que la venta esté en estado jurídico
            if ($venta->estatus_venta !== 'juridico') {
                return redirect()->back()->with('error', 'Solo se pueden reestructurar ventas en estado jurídico');
            }

            // Obtener datos del cliente
            $cliente = $this->clienteModel->find($venta->cliente_id);
            
            // Calcular saldo pendiente actual
            $saldoPendienteData = $this->calcularSaldoPendienteDetallado($ventaId);
            
            // Obtener mensualidades vencidas
            $mensualidadesVencidas = $this->tablaAmortizacionModel->getMensualidadesVencidas($venta->cliente_id);

            $data = [
                'title' => 'Nueva Reestructuración',
                'venta' => $venta,
                'cliente' => $cliente,
                'saldo_pendiente_data' => $saldoPendienteData,
                'mensualidades_vencidas' => $mensualidadesVencidas
            ];

            return view('admin/reestructuracion/create', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en create reestructuración: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar formulario: ' . $e->getMessage());
        }
    }

    /**
     * Procesa la creación de una nueva reestructuración
     */
    public function store()
    {
        try {
            $rules = [
                'venta_id' => 'required|is_natural_no_zero',
                'motivo' => 'required|max_length[1000]',
                'fecha_reestructuracion' => 'required|valid_date',
                'nuevo_saldo_capital' => 'required|decimal|greater_than[0]',
                'nuevo_plazo_meses' => 'required|is_natural_no_zero|greater_than[0]',
                'nueva_tasa_interes' => 'required|decimal|greater_than_equal_to[0]',
                'enganche_convenio' => 'permit_empty|decimal|greater_than_equal_to[0]',
                'fecha_primer_pago' => 'required|valid_date',
                'quita_aplicada' => 'permit_empty|decimal|greater_than_equal_to[0]',
                'descuento_intereses' => 'permit_empty|decimal|greater_than_equal_to[0]',
                'descuento_moratorios' => 'permit_empty|decimal|greater_than_equal_to[0]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                                ->withInput()
                                ->with('errors', $this->validator->getErrors());
            }

            $postData = $this->request->getPost();
            
            // Obtener datos de la venta original
            $venta = $this->ventaModel->find($postData['venta_id']);
            if (!$venta) {
                return redirect()->back()->with('error', 'Venta no encontrada');
            }

            // Calcular saldo pendiente original
            $saldoPendienteData = $this->calcularSaldoPendienteDetallado($postData['venta_id']);
            
            // Calcular pago mensual
            $capital = $postData['nuevo_saldo_capital'];
            $tasaAnual = $postData['nueva_tasa_interes'];
            $plazoMeses = $postData['nuevo_plazo_meses'];
            
            $pagoMensual = $this->calcularPagoMensual($capital, $tasaAnual, $plazoMeses);

            // Preparar datos para inserción
            $dataReestructuracion = [
                'venta_id' => $postData['venta_id'],
                'motivo' => $postData['motivo'],
                'fecha_reestructuracion' => $postData['fecha_reestructuracion'],
                'fecha_vencimiento_original' => $saldoPendienteData['fecha_vencimiento_original'],
                'saldo_pendiente_original' => $saldoPendienteData['saldo_total'],
                'saldo_capital_original' => $saldoPendienteData['saldo_capital'],
                'saldo_interes_original' => $saldoPendienteData['saldo_interes'],
                'saldo_moratorio_original' => $saldoPendienteData['saldo_moratorio'],
                'quita_aplicada' => $postData['quita_aplicada'] ?? 0,
                'descuento_intereses' => $postData['descuento_intereses'] ?? 0,
                'descuento_moratorios' => $postData['descuento_moratorios'] ?? 0,
                'nuevo_saldo_capital' => $capital,
                'nuevo_plazo_meses' => $plazoMeses,
                'nueva_tasa_interes' => $tasaAnual,
                'nuevo_pago_mensual' => $pagoMensual,
                'enganche_convenio' => $postData['enganche_convenio'] ?? 0,
                'fecha_primer_pago' => $postData['fecha_primer_pago'],
                'estatus' => 'propuesta',
                'registrado_por' => session()->get('user_id'),
                'observaciones' => $postData['observaciones'] ?? null
            ];

            // Insertar reestructuración
            $reestructuracionId = $this->reestructuracionModel->insert($dataReestructuracion);
            
            if (!$reestructuracionId) {
                return redirect()->back()
                                ->withInput()
                                ->with('error', 'Error al crear la reestructuración');
            }

            // Generar tabla de amortización
            $this->reestructuracionDetalleModel->generarTablaAmortizacion(
                $reestructuracionId,
                $capital,
                $tasaAnual,
                $plazoMeses,
                $postData['fecha_primer_pago']
            );

            // Registrar en historial
            $this->historialModel->registrarAccion(
                $reestructuracionId,
                'crear',
                'Reestructuración creada por ' . session()->get('username'),
                null,
                $dataReestructuracion
            );

            return redirect()->to('/admin/reestructuracion/show/' . $reestructuracionId)
                            ->with('success', 'Reestructuración creada exitosamente');

        } catch (\Exception $e) {
            log_message('error', 'Error al crear reestructuración: ' . $e->getMessage());
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al crear reestructuración: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle de una reestructuración
     */
    public function show($id)
    {
        try {
            // Obtener reestructuración con datos relacionados
            $reestructuracion = $this->reestructuracionModel->getDetalleCompleto($id);
            
            if (!$reestructuracion) {
                return redirect()->back()->with('error', 'Reestructuración no encontrada');
            }

            // Obtener tabla de amortización
            $tablaAmortizacion = $this->reestructuracionDetalleModel->getTablaAmortizacion($id);
            
            // Obtener progreso
            $progreso = $this->reestructuracionDetalleModel->getProgresoReestructuracion($id);
            
            // Obtener historial
            $historial = $this->historialModel->getHistorialReestructuracion($id);

            $data = [
                'title' => 'Detalle de Reestructuración',
                'reestructuracion' => $reestructuracion,
                'tabla_amortizacion' => $tablaAmortizacion,
                'progreso' => $progreso,
                'historial' => $historial
            ];

            return view('admin/reestructuracion/show', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en show reestructuración: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar detalle: ' . $e->getMessage());
        }
    }

    /**
     * Muestra formulario para editar una reestructuración
     */
    public function edit($id)
    {
        try {
            $reestructuracion = $this->reestructuracionModel->find($id);
            
            if (!$reestructuracion) {
                return redirect()->back()->with('error', 'Reestructuración no encontrada');
            }

            // Solo permitir edición si está en estado propuesta
            if ($reestructuracion->estatus !== 'propuesta') {
                return redirect()->back()->with('error', 'Solo se pueden editar reestructuraciones en estado propuesta');
            }

            // Obtener datos relacionados
            $venta = $this->ventaModel->find($reestructuracion->venta_id);
            $cliente = $this->clienteModel->find($venta->cliente_id);
            $saldoPendienteData = $this->calcularSaldoPendienteDetallado($reestructuracion->venta_id);

            $data = [
                'title' => 'Editar Reestructuración',
                'reestructuracion' => $reestructuracion,
                'venta' => $venta,
                'cliente' => $cliente,
                'saldo_pendiente_data' => $saldoPendienteData
            ];

            return view('admin/reestructuracion/edit', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en edit reestructuración: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar formulario de edición: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza una reestructuración
     */
    public function update($id)
    {
        try {
            $reestructuracion = $this->reestructuracionModel->find($id);
            
            if (!$reestructuracion) {
                return redirect()->back()->with('error', 'Reestructuración no encontrada');
            }

            // Solo permitir actualización si está en estado propuesta
            if ($reestructuracion->estatus !== 'propuesta') {
                return redirect()->back()->with('error', 'Solo se pueden actualizar reestructuraciones en estado propuesta');
            }

            $rules = [
                'motivo' => 'required|max_length[1000]',
                'fecha_reestructuracion' => 'required|valid_date',
                'nuevo_saldo_capital' => 'required|decimal|greater_than[0]',
                'nuevo_plazo_meses' => 'required|is_natural_no_zero|greater_than[0]',
                'nueva_tasa_interes' => 'required|decimal|greater_than_equal_to[0]',
                'fecha_primer_pago' => 'required|valid_date',
                'quita_aplicada' => 'permit_empty|decimal|greater_than_equal_to[0]',
                'descuento_intereses' => 'permit_empty|decimal|greater_than_equal_to[0]',
                'descuento_moratorios' => 'permit_empty|decimal|greater_than_equal_to[0]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                                ->withInput()
                                ->with('errors', $this->validator->getErrors());
            }

            $postData = $this->request->getPost();
            
            // Recalcular pago mensual
            $capital = $postData['nuevo_saldo_capital'];
            $tasaAnual = $postData['nueva_tasa_interes'];
            $plazoMeses = $postData['nuevo_plazo_meses'];
            $pagoMensual = $this->calcularPagoMensual($capital, $tasaAnual, $plazoMeses);

            $updateData = [
                'motivo' => $postData['motivo'],
                'fecha_reestructuracion' => $postData['fecha_reestructuracion'],
                'nuevo_saldo_capital' => $capital,
                'nuevo_plazo_meses' => $plazoMeses,
                'nueva_tasa_interes' => $tasaAnual,
                'nuevo_pago_mensual' => $pagoMensual,
                'fecha_primer_pago' => $postData['fecha_primer_pago'],
                'quita_aplicada' => $postData['quita_aplicada'] ?? 0,
                'descuento_intereses' => $postData['descuento_intereses'] ?? 0,
                'descuento_moratorios' => $postData['descuento_moratorios'] ?? 0,
                'enganche_convenio' => $postData['enganche_convenio'] ?? 0,
                'observaciones' => $postData['observaciones'] ?? null
            ];

            $updated = $this->reestructuracionModel->update($id, $updateData);

            if (!$updated) {
                return redirect()->back()
                                ->withInput()
                                ->with('error', 'Error al actualizar la reestructuración');
            }

            // Regenerar tabla de amortización
            $this->reestructuracionDetalleModel->generarTablaAmortizacion(
                $id,
                $capital,
                $tasaAnual,
                $plazoMeses,
                $postData['fecha_primer_pago']
            );

            return redirect()->to('/admin/reestructuracion/show/' . $id)
                            ->with('success', 'Reestructuración actualizada exitosamente');

        } catch (\Exception $e) {
            log_message('error', 'Error al actualizar reestructuración: ' . $e->getMessage());
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al actualizar reestructuración: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una reestructuración (solo si está en estado propuesta)
     */
    public function delete($id)
    {
        try {
            $reestructuracion = $this->reestructuracionModel->find($id);
            
            if (!$reestructuracion) {
                return redirect()->back()->with('error', 'Reestructuración no encontrada');
            }

            // Solo permitir eliminación si está en estado propuesta
            if ($reestructuracion->estatus !== 'propuesta') {
                return redirect()->back()->with('error', 'Solo se pueden eliminar reestructuraciones en estado propuesta');
            }

            // Eliminar detalles y historial
            $this->reestructuracionDetalleModel->where('reestructuracion_id', $id)->delete();
            $this->historialModel->where('reestructuracion_id', $id)->delete();
            
            // Eliminar reestructuración
            $deleted = $this->reestructuracionModel->delete($id);

            if ($deleted) {
                return redirect()->to('/admin/reestructuracion/view')
                                ->with('success', 'Reestructuración eliminada exitosamente');
            } else {
                return redirect()->back()->with('error', 'Error al eliminar la reestructuración');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al eliminar reestructuración: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar reestructuración: ' . $e->getMessage());
        }
    }

    /**
     * Autoriza una reestructuración
     */
    public function autorizar($id)
    {
        try {
            $reestructuracion = $this->reestructuracionModel->find($id);
            
            if (!$reestructuracion) {
                return redirect()->back()->with('error', 'Reestructuración no encontrada');
            }

            if ($reestructuracion->estatus !== 'propuesta') {
                return redirect()->back()->with('error', 'Solo se pueden autorizar reestructuraciones en estado propuesta');
            }

            // Autorizar
            $autorizado = $this->reestructuracionModel->autorizar(
                $id,
                session()->get('user_id'),
                session()->get('username')
            );

            if ($autorizado) {
                // Registrar en historial
                $this->historialModel->registrarAccion(
                    $id,
                    'autorizar',
                    'Reestructuración autorizada por ' . session()->get('username')
                );

                return redirect()->back()->with('success', 'Reestructuración autorizada exitosamente');
            } else {
                return redirect()->back()->with('error', 'Error al autorizar la reestructuración');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al autorizar reestructuración: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al autorizar: ' . $e->getMessage());
        }
    }

    /**
     * Activa una reestructuración (después de firma)
     */
    public function activar($id)
    {
        try {
            $reestructuracion = $this->reestructuracionModel->find($id);
            
            if (!$reestructuracion) {
                return redirect()->back()->with('error', 'Reestructuración no encontrada');
            }

            if ($reestructuracion->estatus !== 'autorizada') {
                return redirect()->back()->with('error', 'Solo se pueden activar reestructuraciones autorizadas');
            }

            // Activar
            $activado = $this->reestructuracionModel->activar($id);

            if ($activado) {
                // Registrar en historial
                $this->historialModel->registrarAccion(
                    $id,
                    'activar',
                    'Reestructuración activada por ' . session()->get('username')
                );

                return redirect()->back()->with('success', 'Reestructuración activada exitosamente');
            } else {
                return redirect()->back()->with('error', 'Error al activar la reestructuración');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al activar reestructuración: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al activar: ' . $e->getMessage());
        }
    }

    // ==========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==========================================

    /**
     * Calcula el saldo pendiente de una venta
     */
    private function calcularSaldoPendienteVenta(int $ventaId): float
    {
        $saldo = $this->db->table('tabla_amortizacion ta')
                         ->select('SUM(ta.saldo_pendiente) as saldo_total')
                         ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id')
                         ->where('pv.venta_id', $ventaId)
                         ->where('ta.estatus !=', 'pagada')
                         ->get()
                         ->getRow();

        return $saldo ? $saldo->saldo_total : 0.0;
    }

    /**
     * Calcula el saldo pendiente detallado
     */
    private function calcularSaldoPendienteDetallado(int $ventaId): array
    {
        $query = $this->db->table('tabla_amortizacion ta')
                         ->select('
                             SUM(ta.saldo_pendiente) as saldo_total,
                             SUM(ta.capital) as saldo_capital,
                             SUM(ta.interes) as saldo_interes,
                             SUM(ta.interes_moratorio) as saldo_moratorio,
                             MIN(ta.fecha_vencimiento) as fecha_vencimiento_original,
                             COUNT(*) as mensualidades_pendientes
                         ')
                         ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id')
                         ->where('pv.venta_id', $ventaId)
                         ->where('ta.estatus !=', 'pagada')
                         ->get()
                         ->getRow();

        return [
            'saldo_total' => $query->saldo_total ?? 0,
            'saldo_capital' => $query->saldo_capital ?? 0,
            'saldo_interes' => $query->saldo_interes ?? 0,
            'saldo_moratorio' => $query->saldo_moratorio ?? 0,
            'fecha_vencimiento_original' => $query->fecha_vencimiento_original,
            'mensualidades_pendientes' => $query->mensualidades_pendientes ?? 0
        ];
    }

    /**
     * Calcula el pago mensual
     */
    private function calcularPagoMensual(float $capital, float $tasaAnual, int $plazoMeses): float
    {
        if ($tasaAnual == 0) {
            return $capital / $plazoMeses;
        }

        $tasaMensual = $tasaAnual / 100 / 12;
        $pago = $capital * ($tasaMensual * pow(1 + $tasaMensual, $plazoMeses)) / (pow(1 + $tasaMensual, $plazoMeses) - 1);
        
        return round($pago, 2);
    }

    /**
     * Calcula estadísticas de búsqueda
     */
    private function calcularEstadisticasBusqueda(array $reestructuraciones): array
    {
        $estadisticas = [
            'total_reestructuraciones' => count($reestructuraciones),
            'total_saldo_original' => 0,
            'total_nuevo_saldo' => 0,
            'total_quitas' => 0,
            'por_estatus' => []
        ];

        foreach ($reestructuraciones as $reestructuracion) {
            $estadisticas['total_saldo_original'] += $reestructuracion->saldo_pendiente_original;
            $estadisticas['total_nuevo_saldo'] += $reestructuracion->nuevo_saldo_capital;
            $estadisticas['total_quitas'] += $reestructuracion->quita_aplicada;

            $estatus = $reestructuracion->estatus;
            if (!isset($estadisticas['por_estatus'][$estatus])) {
                $estadisticas['por_estatus'][$estatus] = 0;
            }
            $estadisticas['por_estatus'][$estatus]++;
        }

        return $estadisticas;
    }
}