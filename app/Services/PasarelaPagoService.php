<?php

namespace App\Services;

use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\VentaModel;
use App\Models\ClienteModel;

/**
 * Servicio para integración con pasarelas de pago
 * Implementa patrón Strategy para múltiples proveedores
 */
class PasarelaPagoService
{
    private TablaAmortizacionModel $tablaAmortizacionModel;
    private PagoVentaModel $pagoVentaModel;
    private VentaModel $ventaModel;
    private ClienteModel $clienteModel;
    private array $config;

    public function __construct()
    {
        $this->tablaAmortizacionModel = new TablaAmortizacionModel();
        $this->pagoVentaModel = new PagoVentaModel();
        $this->ventaModel = new VentaModel();
        $this->clienteModel = new ClienteModel();
        
        // Cargar configuración de pasarelas
        $this->config = config('PasarelajPago')->toArray();
    }

    /**
     * Iniciar proceso de pago en línea
     */
    public function iniciarPago(array $datosPago): array
    {
        try {
            // Validar datos del pago
            $validacion = $this->validarDatosPago($datosPago);
            if (!$validacion['success']) {
                return $validacion;
            }

            // Obtener información de la mensualidad
            $mensualidad = $this->tablaAmortizacionModel->find($datosPago['mensualidad_id']);
            if (!$mensualidad) {
                throw new \Exception('Mensualidad no encontrada');
            }

            $venta = $this->ventaModel->find($mensualidad->venta_id);
            $cliente = $this->clienteModel->find($venta->cliente_id);

            // Calcular monto total incluyendo mora
            $montoTotal = $mensualidad->monto_total + ($mensualidad->interes_moratorio ?? 0);

            // Preparar datos para la pasarela
            $datosPasarela = [
                'monto' => $montoTotal,
                'concepto' => 'Mensualidad #' . $mensualidad->numero_pago . ' - ' . ($venta->lote_clave ?? 'Propiedad'),
                'referencia_interna' => $this->generarReferenciaInterna($mensualidad->id),
                'cliente' => [
                    'nombre' => $cliente->nombres . ' ' . $cliente->apellido_paterno,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono
                ],
                'urls' => [
                    'success' => site_url('/cliente/pagos/exito'),
                    'error' => site_url('/cliente/pagos/error'),
                    'pending' => site_url('/cliente/pagos/pendiente')
                ],
                'metadata' => [
                    'mensualidad_id' => $mensualidad->id,
                    'venta_id' => $venta->id,
                    'cliente_id' => $cliente->id
                ]
            ];

            // Seleccionar pasarela según configuración
            $pasarela = $this->seleccionarPasarela($montoTotal);
            
            return $this->procesarConPasarela($pasarela, $datosPasarela);

        } catch (\Exception $e) {
            log_message('error', 'Error iniciando pago en línea: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Procesar respuesta de la pasarela de pago
     */
    public function procesarRespuestaPasarela(string $pasarela, array $datos): array
    {
        try {
            switch ($pasarela) {
                case 'stripe':
                    return $this->procesarRespuestaStripe($datos);
                case 'paypal':
                    return $this->procesarRespuestaPayPal($datos);
                case 'mercadopago':
                    return $this->procesarRespuestaMercadoPago($datos);
                case 'openpay':
                    return $this->procesarRespuestaOpenPay($datos);
                default:
                    throw new \Exception('Pasarela no soportada: ' . $pasarela);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error procesando respuesta de pasarela: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Confirmar pago y aplicar a la cuenta
     */
    public function confirmarPago(array $datosPago): array
    {
        try {
            $db = \Config\Database::connect();
            $db->transStart();

            // Crear registro de pago
            $pagoData = [
                'venta_id' => $datosPago['venta_id'],
                'tabla_amortizacion_id' => $datosPago['mensualidad_id'],
                'folio_pago' => $this->generarFolioPago(),
                'monto_pago' => $datosPago['monto'],
                'monto_capital' => $datosPago['monto_capital'] ?? 0,
                'monto_interes' => $datosPago['monto_interes'] ?? 0,
                'monto_mora' => $datosPago['monto_mora'] ?? 0,
                'forma_pago' => 'tarjeta',
                'referencia_pago' => $datosPago['referencia_pasarela'],
                'fecha_pago' => date('Y-m-d H:i:s'),
                'estatus_pago' => 'aplicado',
                'observaciones' => 'Pago procesado via ' . ($datosPago['pasarela'] ?? 'pasarela en línea'),
                'metadata_pago' => json_encode($datosPago['metadata'] ?? [])
            ];

            $pagoId = $this->pagoVentaModel->insert($pagoData);
            if (!$pagoId) {
                throw new \Exception('Error insertando pago');
            }

            // Actualizar estado de la mensualidad usando Entity
            $mensualidad = $this->tablaAmortizacionModel->find($datosPago['mensualidad_id']);
            $resultadoAplicacion = $mensualidad->aplicarPago(
                $datosPago['monto'],
                'tarjeta',
                $datosPago['referencia_pasarela']
            );

            if (!$resultadoAplicacion['success']) {
                throw new \Exception($resultadoAplicacion['error']);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de base de datos');
            }

            // Enviar notificación de pago aplicado
            $notificacionService = new EstadoCuentaNotificacionService();
            $notificacionService->notificarPagoAplicado($pagoId, $datosPago['cliente_id']);

            return [
                'success' => true,
                'pago_id' => $pagoId,
                'folio_pago' => $pagoData['folio_pago'],
                'mensaje' => 'Pago procesado exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error confirmando pago: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar datos del pago
     */
    private function validarDatosPago(array $datos): array
    {
        $errores = [];

        if (empty($datos['mensualidad_id'])) {
            $errores[] = 'ID de mensualidad requerido';
        }

        if (empty($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = 'Monto inválido';
        }

        if (!empty($errores)) {
            return [
                'success' => false,
                'error' => 'Datos inválidos: ' . implode(', ', $errores)
            ];
        }

        return ['success' => true];
    }

    /**
     * Seleccionar pasarela según monto y configuración
     */
    private function seleccionarPasarela(float $monto): string
    {
        // Lógica para seleccionar la mejor pasarela según el monto
        if ($monto >= 10000 && $this->config['stripe']['activo']) {
            return 'stripe';
        } elseif ($monto >= 5000 && $this->config['openpay']['activo']) {
            return 'openpay';
        } elseif ($this->config['mercadopago']['activo']) {
            return 'mercadopago';
        } else {
            return 'paypal'; // Fallback
        }
    }

    /**
     * Procesar con Stripe
     */
    private function procesarConStripe(array $datos): array
    {
        if (!$this->config['stripe']['activo']) {
            throw new \Exception('Stripe no está configurado');
        }

        try {
            \Stripe\Stripe::setApiKey($this->config['stripe']['secret_key']);

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'mxn',
                        'product_data' => [
                            'name' => $datos['concepto'],
                        ],
                        'unit_amount' => $datos['monto'] * 100, // Stripe usa centavos
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $datos['urls']['success'] . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $datos['urls']['error'],
                'metadata' => $datos['metadata']
            ]);

            return [
                'success' => true,
                'pasarela' => 'stripe',
                'url_pago' => $session->url,
                'session_id' => $session->id
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error con Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Procesar con OpenPay
     */
    private function procesarConOpenPay(array $datos): array
    {
        if (!$this->config['openpay']['activo']) {
            throw new \Exception('OpenPay no está configurado');
        }

        // Implementación de OpenPay
        // Retornar URL de pago y datos necesarios
        return [
            'success' => true,
            'pasarela' => 'openpay',
            'url_pago' => 'https://sandbox-dashboard.openpay.mx/...',
            'transaction_id' => uniqid('openpay_')
        ];
    }

    /**
     * Procesar con MercadoPago
     */
    private function procesarConMercadoPago(array $datos): array
    {
        if (!$this->config['mercadopago']['activo']) {
            throw new \Exception('MercadoPago no está configurado');
        }

        // Implementación de MercadoPago
        return [
            'success' => true,
            'pasarela' => 'mercadopago',
            'url_pago' => 'https://www.mercadopago.com.mx/...',
            'preference_id' => uniqid('mp_')
        ];
    }

    /**
     * Procesar según la pasarela seleccionada
     */
    private function procesarConPasarela(string $pasarela, array $datos): array
    {
        switch ($pasarela) {
            case 'stripe':
                return $this->procesarConStripe($datos);
            case 'openpay':
                return $this->procesarConOpenPay($datos);
            case 'mercadopago':
                return $this->procesarConMercadoPago($datos);
            default:
                throw new \Exception('Pasarela no implementada: ' . $pasarela);
        }
    }

    /**
     * Procesar respuesta de Stripe
     */
    private function procesarRespuestaStripe(array $datos): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->config['stripe']['secret_key']);
            
            $session = \Stripe\Checkout\Session::retrieve($datos['session_id']);
            
            if ($session->payment_status === 'paid') {
                return [
                    'success' => true,
                    'estado' => 'aprobado',
                    'referencia' => $session->payment_intent,
                    'metadata' => $session->metadata->toArray()
                ];
            } else {
                return [
                    'success' => false,
                    'estado' => 'rechazado',
                    'error' => 'Pago no completado'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Procesar respuestas de otras pasarelas
     */
    private function procesarRespuestaPayPal(array $datos): array
    {
        // Implementar validación PayPal
        return ['success' => true, 'estado' => 'aprobado'];
    }

    private function procesarRespuestaMercadoPago(array $datos): array
    {
        // Implementar validación MercadoPago
        return ['success' => true, 'estado' => 'aprobado'];
    }

    private function procesarRespuestaOpenPay(array $datos): array
    {
        // Implementar validación OpenPay
        return ['success' => true, 'estado' => 'aprobado'];
    }

    /**
     * Generar referencia interna única
     */
    private function generarReferenciaInterna(int $mensualidadId): string
    {
        return 'PAY' . date('Ymd') . str_pad($mensualidadId, 6, '0', STR_PAD_LEFT) . mt_rand(100, 999);
    }

    /**
     * Generar folio de pago único
     */
    private function generarFolioPago(): string
    {
        // Reutilizar lógica del helper existente
        helper('folio');
        return generar_folio_pago();
    }

    /**
     * Obtener estado de un pago por referencia
     */
    public function consultarEstadoPago(string $referencia, string $pasarela): array
    {
        try {
            switch ($pasarela) {
                case 'stripe':
                    return $this->consultarEstatusStripe($referencia);
                case 'openpay':
                    return $this->consultarEstatusOpenPay($referencia);
                default:
                    throw new \Exception('Consulta no implementada para: ' . $pasarela);
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function consultarEstatusStripe(string $referencia): array
    {
        \Stripe\Stripe::setApiKey($this->config['stripe']['secret_key']);
        
        $intent = \Stripe\PaymentIntent::retrieve($referencia);
        
        return [
            'success' => true,
            'estado' => $intent->status,
            'monto' => $intent->amount / 100
        ];
    }

    private function consultarEstatusOpenPay(string $referencia): array
    {
        // Implementar consulta OpenPay
        return ['success' => true, 'estado' => 'completed'];
    }
}