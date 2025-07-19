<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuración de Pasarelas de Pago
 * Configuración centralizada para integración con servicios de pago
 */
class PasarelaPago extends BaseConfig
{
    /**
     * Configuración de Stripe
     */
    public array $stripe = [
        'activo' => false,
        'entorno' => 'sandbox', // sandbox | production
        'public_key' => '',
        'secret_key' => '',
        'webhook_secret' => '',
        'currency' => 'mxn',
        'comision' => 3.6, // Porcentaje
        'comision_fija' => 3.00, // Monto fijo en pesos
    ];

    /**
     * Configuración de OpenPay
     */
    public array $openpay = [
        'activo' => false,
        'entorno' => 'sandbox', // sandbox | production
        'merchant_id' => '',
        'private_key' => '',
        'public_key' => '',
        'currency' => 'MXN',
        'comision' => 2.9,
        'comision_fija' => 3.00,
    ];

    /**
     * Configuración de MercadoPago
     */
    public array $mercadopago = [
        'activo' => false,
        'entorno' => 'sandbox', // sandbox | production
        'public_key' => '',
        'access_token' => '',
        'client_id' => '',
        'client_secret' => '',
        'currency' => 'MXN',
        'comision' => 5.4,
        'comision_fija' => 2.50,
    ];

    /**
     * Configuración de PayPal
     */
    public array $paypal = [
        'activo' => false,
        'entorno' => 'sandbox', // sandbox | production
        'client_id' => '',
        'client_secret' => '',
        'currency' => 'MXN',
        'comision' => 5.4,
        'comision_fija' => 4.00,
    ];

    /**
     * Configuración de Banorte
     */
    public array $banorte = [
        'activo' => false,
        'entorno' => 'sandbox',
        'merchant_id' => '',
        'terminal_id' => '',
        'control_key' => '',
        'user' => '',
        'password' => '',
        'currency' => 'MXN',
        'comision' => 3.5,
        'comision_fija' => 3.50,
    ];

    /**
     * Configuración general
     */
    public array $general = [
        // Pasarela por defecto
        'pasarela_default' => 'stripe',
        
        // Montos mínimos por pasarela
        'montos_minimos' => [
            'stripe' => 10.00,
            'openpay' => 10.00,
            'mercadopago' => 5.00,
            'paypal' => 20.00,
            'banorte' => 50.00,
        ],
        
        // Montos máximos por pasarela
        'montos_maximos' => [
            'stripe' => 50000.00,
            'openpay' => 100000.00,
            'mercadopago' => 75000.00,
            'paypal' => 25000.00,
            'banorte' => 200000.00,
        ],
        
        // Timeout para conexiones (segundos)
        'timeout' => 30,
        
        // Reintentos en caso de error
        'max_reintentos' => 3,
        
        // URLs de notificación
        'webhook_urls' => [
            'stripe' => '/webhooks/stripe',
            'openpay' => '/webhooks/openpay',
            'mercadopago' => '/webhooks/mercadopago',
            'paypal' => '/webhooks/paypal',
            'banorte' => '/webhooks/banorte',
        ],
        
        // Campos adicionales requeridos por cliente
        'campos_cliente' => [
            'nombre' => true,
            'email' => true,
            'telefono' => true,
            'direccion' => false,
            'rfc' => false,
        ],
        
        // Métodos de pago habilitados por pasarela
        'metodos_pago' => [
            'stripe' => ['card', 'oxxo', 'spei'],
            'openpay' => ['card', 'store', 'bank_account'],
            'mercadopago' => ['credit_card', 'debit_card', 'ticket', 'bank_transfer'],
            'paypal' => ['paypal', 'card'],
            'banorte' => ['card'],
        ],
        
        // Configuración de logs
        'logging' => [
            'activo' => true,
            'nivel' => 'info', // debug | info | warning | error
            'archivo' => 'pagos_online.log',
        ],
        
        // Configuración de seguridad
        'seguridad' => [
            'validar_ip_webhook' => true,
            'ips_permitidas' => [
                // IPs de las pasarelas (agregar según necesidad)
                'stripe' => [],
                'openpay' => [],
                'mercadopago' => [],
            ],
            'firmar_requests' => true,
            'ssl_verify' => true,
        ],
    ];

    /**
     * Configuración de notificaciones
     */
    public array $notificaciones = [
        // Email automático al cliente
        'email_cliente' => true,
        
        // Email a administradores
        'email_admin' => true,
        
        // SMS al cliente (requiere servicio SMS)
        'sms_cliente' => false,
        
        // WhatsApp al cliente (requiere API WhatsApp)
        'whatsapp_cliente' => false,
        
        // Templates de notificación
        'templates' => [
            'pago_exitoso' => 'emails/pago_aplicado',
            'pago_fallido' => 'emails/pago_fallido',
            'pago_pendiente' => 'emails/pago_pendiente',
        ],
    ];

    /**
     * Configuración de desarrollo/testing
     */
    public array $testing = [
        // Habilitar modo de pruebas
        'modo_pruebas' => true,
        
        // Tarjetas de prueba
        'tarjetas_prueba' => [
            'stripe' => [
                'aprobada' => '4242424242424242',
                'declinada' => '4000000000000002',
                'requiere_3ds' => '4000002500003155',
            ],
            'openpay' => [
                'aprobada' => '4111111111111111',
                'declinada' => '4000000000000127',
            ],
        ],
        
        // Simular respuestas
        'simular_respuestas' => false,
        'respuesta_simulada' => 'aprobado', // aprobado | rechazado | pendiente
        
        // Logs detallados en desarrollo
        'debug_logs' => true,
    ];

    /**
     * Constructor - Cargar configuración desde variables de entorno
     */
    public function __construct()
    {
        parent::__construct();
        
        // Cargar configuración desde .env si existe
        $this->cargarConfiguracionEntorno();
    }

    /**
     * Cargar configuración desde variables de entorno
     */
    private function cargarConfiguracionEntorno(): void
    {
        // Stripe
        if (env('STRIPE_PUBLIC_KEY')) {
            $this->stripe['activo'] = true;
            $this->stripe['public_key'] = env('STRIPE_PUBLIC_KEY');
            $this->stripe['secret_key'] = env('STRIPE_SECRET_KEY');
            $this->stripe['webhook_secret'] = env('STRIPE_WEBHOOK_SECRET');
            $this->stripe['entorno'] = env('STRIPE_ENVIRONMENT', 'sandbox');
        }

        // OpenPay
        if (env('OPENPAY_MERCHANT_ID')) {
            $this->openpay['activo'] = true;
            $this->openpay['merchant_id'] = env('OPENPAY_MERCHANT_ID');
            $this->openpay['private_key'] = env('OPENPAY_PRIVATE_KEY');
            $this->openpay['public_key'] = env('OPENPAY_PUBLIC_KEY');
            $this->openpay['entorno'] = env('OPENPAY_ENVIRONMENT', 'sandbox');
        }

        // MercadoPago
        if (env('MERCADOPAGO_ACCESS_TOKEN')) {
            $this->mercadopago['activo'] = true;
            $this->mercadopago['access_token'] = env('MERCADOPAGO_ACCESS_TOKEN');
            $this->mercadopago['public_key'] = env('MERCADOPAGO_PUBLIC_KEY');
            $this->mercadopago['entorno'] = env('MERCADOPAGO_ENVIRONMENT', 'sandbox');
        }

        // PayPal
        if (env('PAYPAL_CLIENT_ID')) {
            $this->paypal['activo'] = true;
            $this->paypal['client_id'] = env('PAYPAL_CLIENT_ID');
            $this->paypal['client_secret'] = env('PAYPAL_CLIENT_SECRET');
            $this->paypal['entorno'] = env('PAYPAL_ENVIRONMENT', 'sandbox');
        }

        // Configuración general
        if (env('PAGOS_ENTORNO')) {
            $this->testing['modo_pruebas'] = env('PAGOS_ENTORNO') === 'development';
        }
    }

    /**
     * Obtener configuración de una pasarela específica
     */
    public function getPasarela(string $nombre): array
    {
        return $this->{$nombre} ?? [];
    }

    /**
     * Verificar si una pasarela está activa
     */
    public function isActiva(string $pasarela): bool
    {
        return $this->getPasarela($pasarela)['activo'] ?? false;
    }

    /**
     * Obtener pasarelas activas
     */
    public function getPasarelasActivas(): array
    {
        $activas = [];
        $pasarelas = ['stripe', 'openpay', 'mercadopago', 'paypal', 'banorte'];
        
        foreach ($pasarelas as $pasarela) {
            if ($this->isActiva($pasarela)) {
                $activas[] = $pasarela;
            }
        }
        
        return $activas;
    }

    /**
     * Validar monto para una pasarela
     */
    public function validarMonto(string $pasarela, float $monto): bool
    {
        $minimo = $this->general['montos_minimos'][$pasarela] ?? 0;
        $maximo = $this->general['montos_maximos'][$pasarela] ?? PHP_FLOAT_MAX;
        
        return $monto >= $minimo && $monto <= $maximo;
    }
}