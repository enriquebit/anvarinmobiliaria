<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistroApiLogsModel extends Model
{
    protected $table            = 'registro_api_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'registro_cliente_id',
        'servicio',
        'operacion',
        'endpoint',
        'metodo',
        'request_headers',
        'request_payload',
        'response_status',
        'response_headers',
        'response_payload',
        'error_message',
        'error_code',
        'error_details',
        'duracion_ms',
        'memoria_utilizada',
        'ip_address',
        'user_agent',
        'session_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'fecha_log';
    protected $updatedField  = null; // Los logs no se actualizan

    // Validation
    protected $validationRules = [
        'servicio' => 'required|in_list[hubspot,google_drive,webhook,system]',
        'operacion' => 'required|max_length[100]',
        'endpoint' => 'permit_empty|max_length[255]',
        'metodo' => 'permit_empty|max_length[10]',
        'response_status' => 'permit_empty|integer',
        'duracion_ms' => 'permit_empty|integer',
        'memoria_utilizada' => 'permit_empty|integer'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['beforeInsert'];
    protected $afterInsert    = ['afterInsert'];

    /**
     * Callback: Antes de insertar
     */
    protected function beforeInsert(array $data): array
    {
        // Asegurar que los JSON sean válidos
        if (isset($data['data']['request_headers']) && is_array($data['data']['request_headers'])) {
            $data['data']['request_headers'] = json_encode($data['data']['request_headers']);
        }
        
        if (isset($data['data']['request_payload']) && is_array($data['data']['request_payload'])) {
            $data['data']['request_payload'] = json_encode($data['data']['request_payload']);
        }
        
        if (isset($data['data']['response_headers']) && is_array($data['data']['response_headers'])) {
            $data['data']['response_headers'] = json_encode($data['data']['response_headers']);
        }
        
        if (isset($data['data']['response_payload']) && is_array($data['data']['response_payload'])) {
            $data['data']['response_payload'] = json_encode($data['data']['response_payload']);
        }
        
        if (isset($data['data']['error_details']) && is_array($data['data']['error_details'])) {
            $data['data']['error_details'] = json_encode($data['data']['error_details']);
        }

        // Agregar información de memoria si no está presente
        if (!isset($data['data']['memoria_utilizada'])) {
            $data['data']['memoria_utilizada'] = memory_get_usage(true);
        }

        return $data;
    }

    /**
     * Callback: Después de insertar
     */
    protected function afterInsert(array $data): array
    {
        // Solo log en desarrollo para evitar recursión
        if (ENVIRONMENT === 'development' && isset($data['data']['servicio'])) {
            error_log('[API_LOG] Registrado: ' . $data['data']['servicio'] . ' - ' . ($data['data']['operacion'] ?? 'unknown'));
        }

        return $data;
    }

    // ===============================================
    // MÉTODOS PERSONALIZADOS
    // ===============================================

    /**
     * Registrar log de operación exitosa
     */
    public function registrarExito(string $servicio, string $operacion, array $datos = []): int
    {
        $logData = [
            'servicio' => $servicio,
            'operacion' => $operacion,
            'response_status' => 200,
            'registro_cliente_id' => $datos['registro_cliente_id'] ?? null,
            'endpoint' => $datos['endpoint'] ?? null,
            'metodo' => $datos['metodo'] ?? 'POST',
            'request_payload' => $datos['request'] ?? null,
            'response_payload' => $datos['response'] ?? null,
            'duracion_ms' => $datos['duracion_ms'] ?? null,
            'ip_address' => $datos['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $datos['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
            'session_id' => $datos['session_id'] ?? session_id()
        ];

        return $this->insert($logData);
    }

    /**
     * Registrar log de error
     */
    public function registrarError(string $servicio, string $operacion, string $error, array $datos = []): int
    {
        $logData = [
            'servicio' => $servicio,
            'operacion' => $operacion,
            'response_status' => $datos['status_code'] ?? 500,
            'error_message' => $error,
            'error_code' => $datos['error_code'] ?? null,
            'error_details' => $datos['error_details'] ?? null,
            'registro_cliente_id' => $datos['registro_cliente_id'] ?? null,
            'endpoint' => $datos['endpoint'] ?? null,
            'metodo' => $datos['metodo'] ?? 'POST',
            'request_payload' => $datos['request'] ?? null,
            'response_payload' => $datos['response'] ?? null,
            'duracion_ms' => $datos['duracion_ms'] ?? null,
            'ip_address' => $datos['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $datos['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
            'session_id' => $datos['session_id'] ?? session_id()
        ];

        return $this->insert($logData);
    }

    /**
     * Obtener logs por servicio
     */
    public function obtenerLogsPorServicio(string $servicio, int $limite = 100): array
    {
        return $this->where('servicio', $servicio)
                   ->orderBy('fecha_log', 'DESC')
                   ->limit($limite)
                   ->findAll();
    }

    /**
     * Obtener logs de errores
     */
    public function obtenerLogsErrores(int $limite = 100, string $servicio = null): array
    {
        $builder = $this->builder();
        $builder->where('error_message IS NOT NULL');
        
        if ($servicio) {
            $builder->where('servicio', $servicio);
        }
        
        return $builder->orderBy('fecha_log', 'DESC')
                      ->limit($limite)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Obtener logs por registro de cliente
     */
    public function obtenerLogsPorRegistro(int $registroClienteId): array
    {
        return $this->where('registro_cliente_id', $registroClienteId)
                   ->orderBy('fecha_log', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener estadísticas de logs
     */
    public function obtenerEstadisticas(string $fechaInicio = null, string $fechaFin = null): array
    {
        $builder = $this->builder();
        
        if ($fechaInicio) {
            $builder->where('fecha_log >=', $fechaInicio);
        }
        
        if ($fechaFin) {
            $builder->where('fecha_log <=', $fechaFin . ' 23:59:59');
        }

        // Estadísticas generales
        $generales = $builder->select('
            COUNT(*) as total_logs,
            COUNT(CASE WHEN error_message IS NOT NULL THEN 1 END) as total_errores,
            COUNT(CASE WHEN error_message IS NULL THEN 1 END) as total_exitosos,
            AVG(duracion_ms) as duracion_promedio,
            MAX(duracion_ms) as duracion_maxima,
            AVG(memoria_utilizada) as memoria_promedio,
            MAX(memoria_utilizada) as memoria_maxima
        ')->get()->getRowArray();

        // Por servicio
        $builder = $this->builder();
        if ($fechaInicio) {
            $builder->where('fecha_log >=', $fechaInicio);
        }
        if ($fechaFin) {
            $builder->where('fecha_log <=', $fechaFin . ' 23:59:59');
        }

        $porServicio = $builder->select('
            servicio,
            COUNT(*) as total,
            COUNT(CASE WHEN error_message IS NOT NULL THEN 1 END) as errores,
            AVG(duracion_ms) as duracion_promedio
        ')
        ->groupBy('servicio')
        ->orderBy('total', 'DESC')
        ->get()->getResultArray();

        // Por código de estado
        $builder = $this->builder();
        if ($fechaInicio) {
            $builder->where('fecha_log >=', $fechaInicio);
        }
        if ($fechaFin) {
            $builder->where('fecha_log <=', $fechaFin . ' 23:59:59');
        }

        $porCodigo = $builder->select('
            response_status,
            COUNT(*) as cantidad
        ')
        ->where('response_status IS NOT NULL')
        ->groupBy('response_status')
        ->orderBy('cantidad', 'DESC')
        ->get()->getResultArray();

        return [
            'generales' => $generales,
            'por_servicio' => $porServicio,
            'por_codigo_estado' => $porCodigo,
            'periodo' => [
                'inicio' => $fechaInicio ?: 'Desde el inicio',
                'fin' => $fechaFin ?: 'Hasta ahora'
            ]
        ];
    }

    /**
     * Obtener logs de rendimiento lento
     */
    public function obtenerLogsLentos(int $umbralMs = 5000, int $limite = 50): array
    {
        return $this->where('duracion_ms >', $umbralMs)
                   ->orderBy('duracion_ms', 'DESC')
                   ->limit($limite)
                   ->findAll();
    }

    /**
     * Obtener errores frecuentes
     */
    public function obtenerErroresFrecuentes(int $limite = 20): array
    {
        $builder = $this->builder();
        
        return $builder->select('
            servicio,
            error_code,
            error_message,
            COUNT(*) as frecuencia,
            MAX(fecha_log) as ultimo_error
        ')
        ->where('error_message IS NOT NULL')
        ->groupBy(['servicio', 'error_code', 'error_message'])
        ->orderBy('frecuencia', 'DESC')
        ->limit($limite)
        ->get()->getResultArray();
    }

    /**
     * Limpiar logs antiguos
     */
    public function limpiarLogsAntiguos(int $diasAntiguedad = 30): int
    {
        $fechaCorte = date('Y-m-d H:i:s', strtotime("-{$diasAntiguedad} days"));
        
        $logsEliminados = $this->where('fecha_log <', $fechaCorte)->delete();

        error_log('[API_LOGS] Limpieza completada: ' . $logsEliminados . ' logs eliminados');

        return $logsEliminados;
    }

    /**
     * Obtener logs por IP (para detectar posibles ataques)
     */
    public function obtenerLogsPorIP(string $ip, int $limite = 100): array
    {
        return $this->where('ip_address', $ip)
                   ->orderBy('fecha_log', 'DESC')
                   ->limit($limite)
                   ->findAll();
    }

    /**
     * Obtener actividad reciente
     */
    public function obtenerActividadReciente(int $minutos = 60, int $limite = 100): array
    {
        $fechaCorte = date('Y-m-d H:i:s', strtotime("-{$minutos} minutes"));
        
        return $this->where('fecha_log >=', $fechaCorte)
                   ->orderBy('fecha_log', 'DESC')
                   ->limit($limite)
                   ->findAll();
    }

    /**
     * Obtener resumen de actividad por hora
     */
    public function obtenerResumenPorHora(string $fecha = null): array
    {
        $fecha = $fecha ?: date('Y-m-d');
        
        $builder = $this->builder();
        
        return $builder->select('
            HOUR(fecha_log) as hora,
            COUNT(*) as total_requests,
            COUNT(CASE WHEN error_message IS NOT NULL THEN 1 END) as errores,
            COUNT(DISTINCT ip_address) as ips_unicas,
            AVG(duracion_ms) as duracion_promedio
        ')
        ->where('DATE(fecha_log)', $fecha)
        ->groupBy('HOUR(fecha_log)')
        ->orderBy('hora', 'ASC')
        ->get()->getResultArray();
    }

    /**
     * Detectar posibles problemas de rendimiento
     */
    public function detectarProblemas(): array
    {
        $problemas = [];
        
        // Errores recientes (última hora)
        $erroresRecientes = $this->where('fecha_log >=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                                ->where('error_message IS NOT NULL')
                                ->countAllResults();
        
        if ($erroresRecientes > 10) {
            $problemas[] = [
                'tipo' => 'errores_frecuentes',
                'descripcion' => "Se han detectado {$erroresRecientes} errores en la última hora",
                'severidad' => 'alta'
            ];
        }

        // Requests lentos
        $requestsLentos = $this->where('duracion_ms >', 10000)
                              ->where('fecha_log >=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                              ->countAllResults();
        
        if ($requestsLentos > 5) {
            $problemas[] = [
                'tipo' => 'rendimiento_lento',
                'descripcion' => "Se han detectado {$requestsLentos} requests lentos (>10s) en la última hora",
                'severidad' => 'media'
            ];
        }

        // Fallos de servicios específicos
        $servicios = ['hubspot', 'google_drive', 'webhook'];
        
        foreach ($servicios as $servicio) {
            $fallosServicio = $this->where('servicio', $servicio)
                                  ->where('error_message IS NOT NULL')
                                  ->where('fecha_log >=', date('Y-m-d H:i:s', strtotime('-30 minutes')))
                                  ->countAllResults();
            
            if ($fallosServicio > 3) {
                $problemas[] = [
                    'tipo' => 'servicio_inestable',
                    'descripcion' => "El servicio {$servicio} ha fallado {$fallosServicio} veces en los últimos 30 minutos",
                    'severidad' => 'alta',
                    'servicio' => $servicio
                ];
            }
        }

        return $problemas;
    }

    /**
     * Exportar logs a CSV
     */
    public function exportarCSV(array $filtros = []): string
    {
        $builder = $this->builder();
        
        // Aplicar filtros
        if (!empty($filtros['servicio'])) {
            $builder->where('servicio', $filtros['servicio']);
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $builder->where('fecha_log >=', $filtros['fecha_inicio']);
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $builder->where('fecha_log <=', $filtros['fecha_fin'] . ' 23:59:59');
        }
        
        if (isset($filtros['solo_errores']) && $filtros['solo_errores']) {
            $builder->where('error_message IS NOT NULL');
        }

        $logs = $builder->orderBy('fecha_log', 'DESC')
                       ->limit($filtros['limite'] ?? 1000)
                       ->get()
                       ->getResultArray();

        // Generar CSV
        $csv = "ID,Fecha,Servicio,Operacion,Status,Duracion(ms),Error,Endpoint,IP\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $log['id'],
                $log['fecha_log'],
                $log['servicio'],
                $log['operacion'],
                $log['response_status'] ?: 'N/A',
                $log['duracion_ms'] ?: 'N/A',
                str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $log['error_message'] ?: 'N/A'),
                $log['endpoint'] ?: 'N/A',
                $log['ip_address'] ?: 'N/A'
            );
        }

        return $csv;
    }
}