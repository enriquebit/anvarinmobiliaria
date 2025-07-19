<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\ClienteModel;

class DashboardController extends BaseController
{
    protected $ventaModel;
    protected $tablaModel;
    protected $pagoModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
        $this->tablaModel = new TablaAmortizacionModel();
        $this->pagoModel = new PagoVentaModel();
        $this->clienteModel = new ClienteModel();
        
        // Cargar helpers necesarios para estado de cuenta
        helper(['estado_cuenta', 'format']);
    }

    public function index()
    {
        // ✅ NO NECESITAMOS VERIFICAR ROLES AQUÍ
        // El ClienteFilter ya se encargó de verificar que sea cliente
        
        try {
            // Obtener ID del cliente autenticado
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            $data = [
                'titulo' => 'Mi Panel Personal',
                'userName' => userName(),
                'userRole' => userRole(),
                'estado_cuenta_disponible' => false,
                'resumen_financiero' => null,
                'alertas_importantes' => [],
                'mensualidades_proximas' => [],
                'pagos_recientes' => [],
                'estadisticas_dashboard' => []
            ];

            if ($clienteId) {
                // Obtener resumen financiero simplificado para dashboard
                $resumenFinanciero = $this->ventaModel->getResumenFinanciero($clienteId);
                
                if ($resumenFinanciero['total_propiedades'] > 0) {
                    $data['estado_cuenta_disponible'] = true;
                    $data['resumen_financiero'] = $this->formatearResumenParaDashboard($resumenFinanciero);
                    
                    // Obtener alertas importantes (solo críticas y urgentes)
                    $alertas = generar_alertas_vencimiento($clienteId);
                    $data['alertas_importantes'] = array_merge(
                        $alertas['criticas'] ?? [],
                        array_slice($alertas['urgentes'] ?? [], 0, 3) // Solo las 3 más urgentes
                    );
                    
                    // Obtener próximas 5 mensualidades
                    $data['mensualidades_proximas'] = $this->ventaModel->getProximasMensualidadesCliente($clienteId, 15, 5);
                    
                    // Obtener últimos 3 pagos
                    $data['pagos_recientes'] = $this->pagoModel->buscarPagos([
                        'cliente_id' => $clienteId,
                        'estatus_pago' => 'aplicado',
                        'orden_por' => 'pv.fecha_pago',
                        'direccion' => 'DESC',
                        'limite' => 3
                    ]);
                    
                    // Calcular estadísticas para widgets del dashboard
                    $data['estadisticas_dashboard'] = $this->calcularEstadisticasDashboard($clienteId);
                }
            }
            
            return view('cliente/dashboard', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en dashboard cliente: ' . $e->getMessage());
            
            // En caso de error, mostrar dashboard básico
            $data = [
                'titulo' => 'Mi Panel Personal',
                'userName' => userName(),
                'userRole' => userRole(),
                'estado_cuenta_disponible' => false,
                'error_carga' => 'Error al cargar información financiera'
            ];
            
            return view('cliente/dashboard', $data);
        }
    }

    /**
     * Widget de resumen financiero para dashboard
     */
    public function widgetResumenFinanciero()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return $this->response->setJSON(['error' => 'Cliente no autenticado']);
            }

            $resumenFinanciero = $this->ventaModel->getResumenFinanciero($clienteId);
            $resumenFormateado = $this->formatearResumenParaDashboard($resumenFinanciero);

            return $this->response->setJSON([
                'success' => true,
                'resumen' => $resumenFormateado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error al cargar resumen financiero']);
        }
    }

    /**
     * Widget de alertas de vencimiento para dashboard
     */
    public function widgetAlertas()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return $this->response->setJSON(['error' => 'Cliente no autenticado']);
            }

            $alertas = generar_alertas_vencimiento($clienteId);
            
            // Solo alertas críticas y urgentes para el widget
            $alertasImportantes = array_merge(
                $alertas['criticas'] ?? [],
                array_slice($alertas['urgentes'] ?? [], 0, 3)
            );

            return $this->response->setJSON([
                'success' => true,
                'alertas' => $alertasImportantes,
                'total_criticas' => count($alertas['criticas'] ?? []),
                'total_urgentes' => count($alertas['urgentes'] ?? [])
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error al cargar alertas']);
        }
    }

    /**
     * Widget de próximas mensualidades para dashboard
     */
    public function widgetProximasMensualidades()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return $this->response->setJSON(['error' => 'Cliente no autenticado']);
            }

            $proximasMensualidades = $this->ventaModel->getProximasMensualidadesCliente($clienteId, 30, 5);

            return $this->response->setJSON([
                'success' => true,
                'mensualidades' => $proximasMensualidades
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error al cargar próximas mensualidades']);
        }
    }

    /**
     * Widget de pagos recientes para dashboard
     */
    public function widgetPagosRecientes()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return $this->response->setJSON(['error' => 'Cliente no autenticado']);
            }

            $pagosRecientes = $this->pagoModel->buscarPagos([
                'cliente_id' => $clienteId,
                'estatus_pago' => 'aplicado',
                'orden_por' => 'pv.fecha_pago',
                'direccion' => 'DESC',
                'limite' => 5
            ]);

            return $this->response->setJSON([
                'success' => true,
                'pagos' => $pagosRecientes
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error al cargar pagos recientes']);
        }
    }

    // ==========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==========================================

    /**
     * Obtiene el ID del cliente autenticado
     */
    private function obtenerClienteIdAutenticado(): ?int
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }

        $cliente = $this->clienteModel
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        return $cliente ? $cliente->id : null;
    }

    /**
     * Formatea el resumen financiero para mostrar en dashboard
     */
    private function formatearResumenParaDashboard(array $resumen): array
    {
        return [
            'total_propiedades' => $resumen['total_propiedades'],
            'monto_total_invertido' => '$' . number_format($resumen['monto_total_ventas'], 0),
            'saldo_pendiente' => '$' . number_format($resumen['saldo_total_pendiente'], 0),
            'total_pagado' => '$' . number_format($resumen['total_pagado'], 0),
            'porcentaje_liquidacion' => number_format($resumen['porcentaje_liquidacion_promedio'], 1) . '%',
            'propiedades_con_atrasos' => $resumen['propiedades_con_atrasos'],
            
            // Indicadores visuales para dashboard
            'estado_general' => $this->determinarEstadoGeneral($resumen),
            'color_indicador' => $this->determinarColorIndicador($resumen),
            'mensaje_estado' => $this->generarMensajeEstado($resumen)
        ];
    }

    /**
     * Calcula estadísticas específicas para widgets del dashboard
     */
    private function calcularEstadisticasDashboard(int $clienteId): array
    {
        // Mensualidades pendientes
        $mensualidadesPendientes = $this->tablaModel->buscarMensualidades([
            'cliente_id' => $clienteId,
            'estatus' => ['pendiente', 'vencida', 'parcial']
        ]);

        // Mensualidades vencidas
        $mensualidadesVencidas = array_filter($mensualidadesPendientes, function($m) {
            return $m->estatus === 'vencida';
        });

        // Próxima mensualidad
        $proximaMensualidad = null;
        $mensualidadesOrdenadas = $mensualidadesPendientes;
        usort($mensualidadesOrdenadas, function($a, $b) {
            return strtotime($a->fecha_vencimiento) - strtotime($b->fecha_vencimiento);
        });
        
        if (!empty($mensualidadesOrdenadas)) {
            $proximaMensualidad = $mensualidadesOrdenadas[0];
        }

        return [
            'mensualidades_pendientes' => count($mensualidadesPendientes),
            'mensualidades_vencidas' => count($mensualidadesVencidas),
            'monto_vencido' => array_sum(array_map(function($m) { 
                return $m->getSaldoTotalPendiente(); 
            }, $mensualidadesVencidas)),
            'proxima_mensualidad' => $proximaMensualidad ? [
                'numero_pago' => $proximaMensualidad->numero_pago,
                'fecha_vencimiento' => date('d/m/Y', strtotime($proximaMensualidad->fecha_vencimiento)),
                'monto' => '$' . number_format($proximaMensualidad->monto_total, 0),
                'dias_para_vencer' => max(0, (new \DateTime($proximaMensualidad->fecha_vencimiento))->diff(new \DateTime())->days)
            ] : null
        ];
    }

    /**
     * Determina el estado general del cliente basado en su resumen financiero
     */
    private function determinarEstadoGeneral(array $resumen): string
    {
        if ($resumen['propiedades_con_atrasos'] > 0) {
            return 'con_atrasos';
        }
        
        if ($resumen['porcentaje_liquidacion_promedio'] >= 80) {
            return 'excelente';
        }
        
        if ($resumen['porcentaje_liquidacion_promedio'] >= 50) {
            return 'bueno';
        }
        
        return 'regular';
    }

    /**
     * Determina el color del indicador basado en el estado
     */
    private function determinarColorIndicador(array $resumen): string
    {
        $estado = $this->determinarEstadoGeneral($resumen);
        
        switch ($estado) {
            case 'excelente':
                return 'success';
            case 'bueno':
                return 'info';
            case 'regular':
                return 'warning';
            case 'con_atrasos':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Genera mensaje descriptivo del estado
     */
    private function generarMensajeEstado(array $resumen): string
    {
        $estado = $this->determinarEstadoGeneral($resumen);
        
        switch ($estado) {
            case 'excelente':
                return 'Excelente historial de pagos';
            case 'bueno':
                return 'Buen cumplimiento de pagos';
            case 'regular':
                return 'Cumplimiento regular';
            case 'con_atrasos':
                return 'Tiene pagos pendientes';
            default:
                return 'Estado en evaluación';
        }
    }
}