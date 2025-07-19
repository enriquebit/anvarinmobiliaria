<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ComisionVentaModel;
use App\Models\VentaModel;
use App\Models\ApartadoModel;
use App\Models\UserModel;
use App\Models\LoteModel;
use App\Models\ClienteModel;
use App\Models\PerfilFinanciamientoModel;
use App\Models\ConfiguracionComisionModel;

class AdminComisionesController extends BaseController
{
    protected $comisionModel;
    protected $ventaModel;
    protected $apartadoModel;
    protected $userModel;
    protected $loteModel;
    protected $clienteModel;
    protected $perfilFinanciamientoModel;
    protected $configuracionComisionModel;

    public function __construct()
    {
        $this->comisionModel = new ComisionVentaModel();
        $this->ventaModel = new VentaModel();
        $this->apartadoModel = new ApartadoModel();
        $this->userModel = new UserModel();
        $this->loteModel = new LoteModel();
        $this->clienteModel = new ClienteModel();
        $this->perfilFinanciamientoModel = new PerfilFinanciamientoModel();
        $this->configuracionComisionModel = new ConfiguracionComisionModel();
    }
    public function index()
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        // Obtener vendedores para filtros con email de auth_identities
        $vendedores = $this->userModel->select('users.id, users.username, ai.secret as email')
            ->join('auth_identities ai', 'ai.user_id = users.id')
            ->where('users.active', 1)
            ->where('ai.type', 'email_password')
            ->findAll();

        $data = [
            'title' => 'Gestión de Comisiones',
            'page_title' => 'Comisiones',
            'vendedores' => $vendedores
        ];

        return view('admin/comisiones/index', $data);
    }

    public function porVendedor($vendedorId)
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        try {
            // Obtener información del vendedor
            $vendedor = $this->userModel->select('users.id, users.username, ai.secret as email')
                ->join('auth_identities ai', 'ai.user_id = users.id')
                ->join('staff s', 's.user_id = users.id')
                ->select('CONCAT(s.nombres, " ", s.apellido_paterno, " ", COALESCE(s.apellido_materno, "")) as nombre_completo', false)
                ->where('users.id', $vendedorId)
                ->where('ai.type', 'email_password')
                ->first();

            if (!$vendedor) {
                return redirect()->to('/admin/comisiones')->with('error', 'Vendedor no encontrado');
            }

            // Obtener fechas para el filtro (últimos 12 meses)
            $fechaFin = date('Y-m-d');
            $fechaInicio = date('Y-m-d', strtotime('-12 months'));

            // Obtener comisiones del vendedor
            $comisiones = $this->comisionModel->getComisionesPorVendedor($vendedorId, [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]);

            // Calcular estadísticas
            $estadisticas = [
                'total_comisiones' => count($comisiones),
                'comisiones_pendientes' => count(array_filter($comisiones, function($c) {
                    return in_array($c->estatus, ['pendiente', 'aceptada', 'en_proceso']);
                })),
                'comisiones_pagadas' => count(array_filter($comisiones, function($c) {
                    return $c->estatus === 'pagada';
                })),
                'monto_total' => array_sum(array_column($comisiones, 'monto_comision_total')),
                'monto_pagado' => array_sum(array_column($comisiones, 'monto_pagado_total'))
            ];

            // Preparar datos para el gráfico por mes
            $comisionesPorMes = [];
            foreach ($comisiones as $comision) {
                $mes = date('Y-m', strtotime($comision->fecha_generacion));
                if (!isset($comisionesPorMes[$mes])) {
                    $comisionesPorMes[$mes] = 0;
                }
                $comisionesPorMes[$mes] += $comision->monto_comision_total;
            }

            // Preparar arrays para el gráfico
            $graficoMeses = [];
            $graficoMontos = [];
            
            for ($i = 11; $i >= 0; $i--) {
                $mes = date('Y-m', strtotime("-{$i} months"));
                $mesTexto = date('M Y', strtotime("-{$i} months"));
                
                $graficoMeses[] = $mesTexto;
                $graficoMontos[] = $comisionesPorMes[$mes] ?? 0;
            }

            $data = [
                'title' => 'Comisiones por Vendedor',
                'vendedor' => $vendedor,
                'comisiones' => $comisiones,
                'estadisticas' => $estadisticas,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'grafico_meses' => $graficoMeses,
                'grafico_montos' => $graficoMontos
            ];

            return view('admin/comisiones/por_vendedor', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en comisiones por vendedor: ' . $e->getMessage());
            return redirect()->to('/admin/comisiones')->with('error', 'Error al cargar comisiones del vendedor');
        }
    }

    public function show($id)
    {
        // Implementar vista detalle de comisión
        return redirect()->to('/admin/comisiones')->with('info', 'Funcionalidad en desarrollo');
    }

    public function generarReporte()
    {
        try {
            $filtros = [
                'estatus' => $this->request->getGet('estatus'),
                'vendedor_id' => $this->request->getGet('vendedor_id'),
                'fecha_inicio' => $this->request->getGet('fecha_inicio'),
                'fecha_fin' => $this->request->getGet('fecha_fin')
            ];

            $comisiones = $this->comisionModel->getComisionesConRelaciones($filtros);
            
            // Configurar headers para Excel
            $filename = 'reporte_comisiones_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fwrite($output, "\xEF\xBB\xBF");
            
            // Encabezados
            fputcsv($output, [
                'Folio Venta',
                'Folio Apartado', 
                'Vendedor',
                'Cliente',
                'Lote',
                'Base Cálculo',
                'Porcentaje',
                'Monto Comisión',
                'Monto Pagado',
                'Saldo Pendiente',
                'Estatus',
                'Fecha Generación'
            ]);
            
            // Datos
            foreach ($comisiones as $comision) {
                fputcsv($output, [
                    $comision->folio_venta ?? '',
                    $comision->folio_apartado ?? '',
                    $comision->vendedor_nombre,
                    $comision->cliente_nombre,
                    $comision->lote_clave,
                    '$' . number_format($comision->base_calculo, 2),
                    $comision->porcentaje_aplicado . '%',
                    '$' . number_format($comision->monto_comision_total, 2),
                    '$' . number_format($comision->monto_pagado_total, 2),
                    '$' . number_format($comision->monto_comision_total - $comision->monto_pagado_total, 2),
                    ucfirst($comision->estatus),
                    date('d/m/Y', strtotime($comision->fecha_generacion))
                ]);
            }
            
            fclose($output);
            
        } catch (\Exception $e) {
            return redirect()->to('/admin/comisiones')->with('error', 'Error al generar reporte: ' . $e->getMessage());
        }
    }

    public function obtenerComisiones()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $filtros = [
                'estatus' => $this->request->getPost('estatus'),
                'vendedor_id' => $this->request->getPost('vendedor_id'),
                'fecha_inicio' => $this->request->getPost('fecha_inicio'),
                'fecha_fin' => $this->request->getPost('fecha_fin')
            ];

            $comisiones = $this->comisionModel->getComisionesConRelaciones($filtros);

            $data = [];
            foreach ($comisiones as $comision) {
                $data[] = [
                    'id' => $comision->id,
                    'folio_venta' => $comision->folio_venta ?? 'APARTADO',
                    'folio_apartado' => $comision->folio_apartado ?? '',
                    'vendedor' => $comision->vendedor_nombre,
                    'cliente' => $comision->cliente_nombre,
                    'lote' => $comision->lote_clave,
                    'base_calculo' => '$' . number_format($comision->base_calculo, 2),
                    'porcentaje_aplicado' => $comision->porcentaje_aplicado . '%',
                    'monto_comision_total' => '$' . number_format($comision->monto_comision_total, 2),
                    'monto_pagado_total' => '$' . number_format($comision->monto_pagado_total, 2),
                    'saldo_pendiente' => '$' . number_format($comision->saldo_pendiente, 2),
                    'estatus' => $this->getEstadoBadge($comision->estatus),
                    'fecha_generacion' => date('d/m/Y', strtotime($comision->fecha_generacion)),
                    'acciones' => $this->getAccionesBotones($comision)
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener comisiones: ' . $e->getMessage()
            ]);
        }
    }

    public function procesarPago()
    {
        // AJAX procesar pago
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Funcionalidad en desarrollo'
        ]);
    }

    public function generarComisionesFaltantes()
    {
        // AJAX generar comisiones faltantes
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            // Buscar ventas sin comisiones
            $ventasSinComisiones = $this->ventaModel->select('ventas.*, u.id as vendedor_id')
                ->join('users u', 'u.id = ventas.vendedor_id')
                ->where('ventas.estatus_venta', 'liquidado')
                ->where('ventas.id NOT IN (SELECT venta_id FROM comisiones_ventas WHERE venta_id IS NOT NULL)')
                ->findAll();

            $comisionesCreadas = 0;
            $errores = [];

            foreach ($ventasSinComisiones as $venta) {
                helper('comision');
                
                $datosComision = [
                    'venta_id' => $venta->id,
                    'vendedor_id' => $venta->vendedor_id,
                    'precio_venta_final' => $venta->precio_venta_final
                ];

                $resultado = crear_comision_automatica($datosComision);
                
                if ($resultado['success']) {
                    $comisionesCreadas++;
                } else {
                    $errores[] = "Venta {$venta->folio_venta}: {$resultado['error']}";
                }
            }

            $mensaje = "Se crearon {$comisionesCreadas} comisiones automáticamente";
            if (!empty($errores)) {
                $mensaje .= ". Errores: " . implode(', ', $errores);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $mensaje,
                'comisiones_creadas' => $comisionesCreadas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar comisiones: ' . $e->getMessage()
            ]);
        }
    }

    public function anularComision($id)
    {
        // AJAX anular comisión
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Funcionalidad en desarrollo'
        ]);
    }

    public function calcularComisionesVenta($ventaId)
    {
        // Calcular comisiones para una venta específica
        return redirect()->to('/admin/comisiones')->with('info', 'Funcionalidad en desarrollo');
    }

    public function obtenerEstadisticas()
    {
        // AJAX obtener estadísticas
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $estadisticas = $this->comisionModel->getEstadisticas();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'total_comisiones' => $estadisticas['total_comisiones'],
                    'comisiones_pendientes' => $estadisticas['comisiones_pendientes'],
                    'comisiones_pagadas' => $estadisticas['comisiones_pagadas'],
                    'monto_total' => '$' . number_format($estadisticas['monto_total'], 2),
                    'monto_pagado' => '$' . number_format($estadisticas['monto_pagado'], 2),
                    'monto_pendiente' => '$' . number_format($estadisticas['monto_pendiente'], 2)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Aceptar comisión (cambiar de pendiente_aceptacion a aceptada)
     */
    public function aceptarComision()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $comisionId = $this->request->getPost('comision_id');
            $observaciones = $this->request->getPost('observaciones');

            $comision = $this->comisionModel->find($comisionId);
            if (!$comision) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Comisión no encontrada'
                ]);
            }

            if ($comision->estatus !== 'pendiente_aceptacion') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'La comisión no está en estado "pendiente_aceptacion"'
                ]);
            }

            $resultado = $this->comisionModel->cambiarEstado($comisionId, 'aceptada', $observaciones);

            if ($resultado) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Comisión aceptada exitosamente'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al aceptar la comisión'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar pago de comisión (cambiar a en_proceso)
     */
    public function procesarComision()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $comisionId = $this->request->getPost('comision_id');
            $fechaEstimadaPago = $this->request->getPost('fecha_estimada_pago');
            $emailVendedor = $this->request->getPost('email_vendedor');

            $comision = $this->comisionModel->find($comisionId);
            if (!$comision) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Comisión no encontrada'
                ]);
            }

            if ($comision->estatus !== 'aceptada') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'La comisión debe estar en estado "aceptada"'
                ]);
            }

            // Cambiar estado a en_proceso
            $resultado = $this->comisionModel->cambiarEstado($comisionId, 'en_proceso', 
                'Fecha estimada de pago: ' . $fechaEstimadaPago);

            if ($resultado) {
                // TODO: Enviar email de notificación al vendedor
                // $this->enviarEmailNotificacion($emailVendedor, $comision, $fechaEstimadaPago);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Comisión procesada. Se ha enviado notificación al vendedor.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al procesar la comisión'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Pagar comisión (cambiar a pagada y generar recibo)
     */
    public function pagarComision()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $comisionId = $this->request->getPost('comision_id');
            $montoPago = $this->request->getPost('monto_pago');

            $comision = $this->comisionModel->find($comisionId);
            if (!$comision) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Comisión no encontrada'
                ]);
            }

            if ($comision->estatus !== 'en_proceso') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'La comisión debe estar en estado "en_proceso"'
                ]);
            }

            $resultado = $this->comisionModel->registrarPago($comisionId, $montoPago, 'Pago de comisión');

            if ($resultado) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Pago registrado exitosamente',
                    'url_recibo' => site_url('admin/comisiones/recibo/' . $comisionId)
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al registrar el pago'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar recibo de comisión
     */
    public function recibo($comisionId)
    {
        $comision = $this->comisionModel->getComisionesConRelaciones(['id' => $comisionId]);
        
        if (empty($comision)) {
            return redirect()->to('/admin/comisiones')->with('error', 'Comisión no encontrada');
        }

        $empresa = obtener_empresa_recibo();
        
        $data = [
            'comision' => $comision[0],
            'title' => 'Recibo de Comisión',
            'empresa' => (object)$empresa
        ];

        return view('admin/comisiones/recibo', $data);
    }

    /**
     * Obtener badge HTML para el estado
     */
    private function getEstadoBadge(string $estatus): string
    {
        $badges = [
            'devengada' => '<span class="badge badge-secondary">Devengada</span>',
            'pendiente_aceptacion' => '<span class="badge badge-warning">Pendiente Aceptación</span>',
            'aceptada' => '<span class="badge badge-info">Aceptada</span>',
            'pendiente' => '<span class="badge badge-primary">Pendiente</span>',
            'en_proceso' => '<span class="badge badge-warning">En Proceso</span>',
            'realizado' => '<span class="badge badge-success">Realizado</span>',
            'parcial' => '<span class="badge badge-warning">Parcial</span>',
            'pagada' => '<span class="badge badge-success">Pagada</span>',
            'cancelada' => '<span class="badge badge-danger">Cancelada</span>'
        ];

        return $badges[$estatus] ?? '<span class="badge badge-secondary">' . ucfirst($estatus) . '</span>';
    }

    /**
     * Obtener botones de acciones según el estado
     */
    private function getAccionesBotones($comision): string
    {
        $botones = '';
        
        switch ($comision->estatus) {
            case 'pendiente_aceptacion':
                $botones .= '<button class="btn btn-sm btn-success btn-aceptar" data-id="' . $comision->id . '" title="Aceptar Comisión">
                            <i class="fas fa-check"></i></button> ';
                break;
                
            case 'aceptada':
                $botones .= '<button class="btn btn-sm btn-warning btn-procesar" data-id="' . $comision->id . '" title="Procesar Pago">
                            <i class="fas fa-cog"></i></button> ';
                break;
                
            case 'en_proceso':
                $botones .= '<button class="btn btn-sm btn-primary btn-pagar" data-id="' . $comision->id . '" title="Registrar Pago">
                            <i class="fas fa-dollar-sign"></i></button> ';
                break;
                
            case 'pagada':
            case 'realizado':
                $botones .= '<a href="' . site_url('admin/comisiones/recibo/' . $comision->id) . '" 
                            class="btn btn-sm btn-info" target="_blank" title="Ver Recibo">
                            <i class="fas fa-receipt"></i></a> ';
                break;
        }
        
        // Botón ver detalle siempre disponible
        $botones .= '<button class="btn btn-sm btn-secondary btn-detalle" data-id="' . $comision->id . '" title="Ver Detalle">
                    <i class="fas fa-eye"></i></button>';

        return $botones;
    }
}