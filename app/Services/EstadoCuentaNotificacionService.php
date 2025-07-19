<?php

namespace App\Services;

use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\ClienteModel;
use App\Models\UserModel;
use CodeIgniter\Email\Email;

/**
 * Servicio para envío de notificaciones del módulo Estado de Cuenta
 * Aplica metodología DRY reutilizando configuraciones existentes
 */
class EstadoCuentaNotificacionService
{
    private Email $email;
    private VentaModel $ventaModel;
    private TablaAmortizacionModel $tablaAmortizacionModel;
    private ClienteModel $clienteModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->ventaModel = new VentaModel();
        $this->tablaAmortizacionModel = new TablaAmortizacionModel();
        $this->clienteModel = new ClienteModel();
        $this->userModel = new UserModel();
    }

    /**
     * Enviar notificación de pago aplicado al cliente
     */
    public function notificarPagoAplicado(int $pagoId, int $clienteId): array
    {
        try {
            $cliente = $this->clienteModel->find($clienteId);
            if (!$cliente) {
                throw new \Exception('Cliente no encontrado');
            }

            // Obtener datos del pago usando helper existente
            $datosPago = $this->obtenerDatosPago($pagoId);

            $subject = '✅ Pago Aplicado - Folio ' . $datosPago['folio_pago'];
            $mensaje = $this->generarTemplateEmailPagoAplicado($datosPago, $cliente);

            return $this->enviarEmail($cliente->email, $subject, $mensaje);

        } catch (\Exception $e) {
            log_message('error', 'Error enviando notificación pago aplicado: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar recordatorio de vencimientos próximos
     */
    public function enviarRecordatoriosVencimiento(int $diasAnticipacion = 7): array
    {
        try {
            // Usar helper existente para obtener alertas
            $alertasVencimiento = generar_alertas_vencimiento_global($diasAnticipacion);
            
            $resultados = [
                'enviados' => 0,
                'errores' => 0,
                'detalles' => []
            ];

            foreach ($alertasVencimiento as $clienteId => $alertas) {
                $cliente = $this->clienteModel->find($clienteId);
                if (!$cliente) continue;

                $resultado = $this->enviarRecordatorioCliente($cliente, $alertas, $diasAnticipacion);
                
                if ($resultado['success']) {
                    $resultados['enviados']++;
                } else {
                    $resultados['errores']++;
                }
                
                $resultados['detalles'][] = [
                    'cliente_id' => $clienteId,
                    'email' => $cliente->email,
                    'resultado' => $resultado
                ];
            }

            return [
                'success' => true,
                'resumen' => $resultados
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error enviando recordatorios vencimiento: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar recordatorio a un cliente específico
     */
    private function enviarRecordatorioCliente(object $cliente, array $alertas, int $diasAnticipacion): array
    {
        try {
            $subject = '📅 Recordatorio de Pagos - Próximos Vencimientos';
            $mensaje = $this->generarTemplateEmailRecordatorio($cliente, $alertas, $diasAnticipacion);

            return $this->enviarEmail($cliente->email, $subject, $mensaje);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar alertas de pagos vencidos al equipo administrativo
     */
    public function enviarAlertasPagosVencidos(): array
    {
        try {
            // Obtener mensualidades críticas usando helper existente
            $mensualidadesCriticas = $this->tablaAmortizacionModel->getMensualidadesCriticas();
            
            if (empty($mensualidadesCriticas)) {
                return [
                    'success' => true,
                    'mensaje' => 'No hay pagos vencidos críticos'
                ];
            }

            // Obtener emails del equipo administrativo
            $emailsAdmin = $this->obtenerEmailsAdministradores();
            
            $subject = '🚨 Alerta: Pagos Vencidos Críticos (' . count($mensualidadesCriticas) . ')';
            $mensaje = $this->generarTemplateEmailAlertasAdmin($mensualidadesCriticas);

            $resultados = [];
            foreach ($emailsAdmin as $email) {
                $resultados[] = $this->enviarEmail($email, $subject, $mensaje);
            }

            return [
                'success' => true,
                'enviados' => count(array_filter($resultados, fn($r) => $r['success'])),
                'total' => count($resultados)
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error enviando alertas admin: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar estado de cuenta por email al cliente
     */
    public function enviarEstadoCuentaPorEmail(int $clienteId, ?int $ventaId = null): array
    {
        try {
            $cliente = $this->clienteModel->find($clienteId);
            if (!$cliente) {
                throw new \Exception('Cliente no encontrado');
            }

            // Generar PDF usando el servicio existente
            $pdfService = new EstadoCuentaPDFService();
            
            if ($ventaId) {
                $resultadoPDF = $pdfService->generarEstadoCuentaVenta($ventaId);
                $subject = '📊 Tu Estado de Cuenta - Propiedad';
            } else {
                $resultadoPDF = $pdfService->generarEstadoCuentaCliente($clienteId);
                $subject = '📊 Tu Estado de Cuenta General';
            }

            if (!$resultadoPDF['success']) {
                throw new \Exception('Error generando PDF: ' . $resultadoPDF['error']);
            }

            $mensaje = $this->generarTemplateEmailEstadoCuenta($cliente);
            
            // Configurar email con adjunto
            $this->email->setTo($cliente->email);
            $this->email->setSubject($subject);
            $this->email->setMessage($mensaje);
            $this->email->attach('', $resultadoPDF['pdf_content'], $resultadoPDF['filename'], 'application/pdf');

            if ($this->email->send()) {
                return [
                    'success' => true,
                    'mensaje' => 'Estado de cuenta enviado exitosamente'
                ];
            } else {
                throw new \Exception('Error enviando email: ' . $this->email->printDebugger());
            }

        } catch (\Exception $e) {
            log_message('error', 'Error enviando estado cuenta por email: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener datos del pago para notificaciones
     */
    private function obtenerDatosPago(int $pagoId): array
    {
        $pagoModel = new \App\Models\PagoVentaModel();
        $pago = $pagoModel->find($pagoId);
        
        if (!$pago) {
            throw new \Exception('Pago no encontrado');
        }

        $venta = $this->ventaModel->find($pago->venta_id);
        $mensualidad = $this->tablaAmortizacionModel->find($pago->tabla_amortizacion_id);

        return [
            'folio_pago' => $pago->folio_pago,
            'monto_pago' => $pago->monto_pago,
            'fecha_pago' => $pago->fecha_pago,
            'forma_pago' => $pago->forma_pago,
            'lote_clave' => $venta->lote_clave ?? 'N/A',
            'folio_venta' => $venta->folio_venta,
            'numero_mensualidad' => $mensualidad->numero_pago ?? 'N/A'
        ];
    }

    /**
     * Obtener emails de administradores
     */
    private function obtenerEmailsAdministradores(): array
    {
        // Usar configuración existente de grupos Shield
        $emails = [];
        
        try {
            $usuarios = $this->userModel->whereIn('id', function($builder) {
                return $builder->select('user_id')
                    ->from('auth_groups_users')
                    ->where('group', 'admin');
            })->findAll();

            foreach ($usuarios as $usuario) {
                if (!empty($usuario->email)) {
                    $emails[] = $usuario->email;
                }
            }
        } catch (\Exception $e) {
            log_message('warning', 'Error obteniendo emails admin: ' . $e->getMessage());
            // Fallback a email de configuración
            $config = config('App');
            if (!empty($config->adminEmail)) {
                $emails[] = $config->adminEmail;
            }
        }

        return $emails;
    }

    /**
     * Enviar email base
     */
    private function enviarEmail(string $destinatario, string $asunto, string $mensaje): array
    {
        try {
            $this->email->clear();
            $this->email->setTo($destinatario);
            $this->email->setSubject($asunto);
            $this->email->setMessage($mensaje);

            if ($this->email->send()) {
                return [
                    'success' => true,
                    'mensaje' => 'Email enviado exitosamente'
                ];
            } else {
                throw new \Exception($this->email->printDebugger());
            }

        } catch (\Exception $e) {
            log_message('error', 'Error enviando email a ' . $destinatario . ': ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Template para notificación de pago aplicado
     */
    private function generarTemplateEmailPagoAplicado(array $datosPago, object $cliente): string
    {
        return view('emails/pago_aplicado', [
            'cliente' => $cliente,
            'pago' => $datosPago
        ]);
    }

    /**
     * Template para recordatorio de vencimientos
     */
    private function generarTemplateEmailRecordatorio(object $cliente, array $alertas, int $diasAnticipacion): string
    {
        return view('emails/recordatorio_vencimientos', [
            'cliente' => $cliente,
            'alertas' => $alertas,
            'dias_anticipacion' => $diasAnticipacion
        ]);
    }

    /**
     * Template para alertas administrativas
     */
    private function generarTemplateEmailAlertasAdmin(array $mensualidadesCriticas): string
    {
        return view('emails/alertas_admin', [
            'mensualidades_criticas' => $mensualidadesCriticas,
            'fecha_reporte' => date('d/m/Y H:i')
        ]);
    }

    /**
     * Template para estado de cuenta
     */
    private function generarTemplateEmailEstadoCuenta(object $cliente): string
    {
        return view('emails/estado_cuenta', [
            'cliente' => $cliente,
            'fecha_generacion' => date('d/m/Y H:i')
        ]);
    }

    /**
     * Programar notificaciones automáticas (para usar con cron jobs)
     */
    public function ejecutarNotificacionesAutomaticas(): array
    {
        $resultados = [
            'recordatorios_7_dias' => $this->enviarRecordatoriosVencimiento(7),
            'recordatorios_3_dias' => $this->enviarRecordatoriosVencimiento(3),
            'alertas_vencidos' => $this->enviarAlertasPagosVencidos()
        ];

        log_message('info', 'Notificaciones automáticas ejecutadas: ' . json_encode($resultados));
        
        return $resultados;
    }
}

/**
 * Helper function para generar alertas de vencimiento globales
 */
if (!function_exists('generar_alertas_vencimiento_global')) {
    function generar_alertas_vencimiento_global(int $diasAnticipacion = 7): array
    {
        $tablaAmortizacionModel = new \App\Models\TablaAmortizacionModel();
        
        $alertas = $tablaAmortizacionModel
            ->select('tabla_amortizacion.*, ventas.cliente_id, ventas.lote_clave, ventas.folio_venta')
            ->join('ventas', 'ventas.id = tabla_amortizacion.venta_id')
            ->where('tabla_amortizacion.estatus', 'pendiente')
            ->where('DATE(tabla_amortizacion.fecha_vencimiento) <=', date('Y-m-d', strtotime('+' . $diasAnticipacion . ' days')))
            ->where('DATE(tabla_amortizacion.fecha_vencimiento) >=', date('Y-m-d'))
            ->orderBy('tabla_amortizacion.fecha_vencimiento', 'ASC')
            ->findAll();

        // Agrupar por cliente
        $alertasPorCliente = [];
        foreach ($alertas as $alerta) {
            $clienteId = $alerta->cliente_id;
            if (!isset($alertasPorCliente[$clienteId])) {
                $alertasPorCliente[$clienteId] = [];
            }
            $alertasPorCliente[$clienteId][] = $alerta;
        }

        return $alertasPorCliente;
    }
}