<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\RegistroLead;

class RegistroLeadModel extends Model
{
    protected $table            = 'registro_leads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = RegistroLead::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        // Campos de información personal
        'firstname',
        'lastname',
        'apellido_materno',
        'rfc',
        'curp',
        'rfc_curp', // Campo legacy, mantener compatibilidad
        'identificador_expediente',
        'email',
        'mobilephone',
        'phone',
        'telefono',
        'medio_de_contacto',
        
        // Información del desarrollo
        'desarrollo',
        'manzana',
        'lote',
        'numero_casa_depto',
        
        // Co-propietario
        'nombre_copropietario',
        'parentesco_copropietario',
        
        // Agente y proceso
        'agente_referido',
        'etapa_proceso',
        
        // Integración HubSpot
        'hubspot_contact_id',
        'hubspot_ticket_id',
        'hubspot_sync_status',
        'hubspot_sync_error',
        'hubspot_sync_attempts',
        'hubspot_last_sync',
        
        // Integración Google Drive
        'google_drive_folder_id',
        'google_drive_folder_url',
        'google_drive_sync_status',
        'google_drive_sync_error',
        'google_drive_sync_attempts',
        'google_drive_last_sync',
        
        // Metadatos del registro
        'fuente_registro',
        'ip_address',
        'user_agent',
        'acepta_terminos',
        'folio',
        'estado_registro',
        
        // Control de estado
        'activo',
        'convertido_a_cliente',
        'fecha_conversion',
        'cliente_id',
        
        // Timestamps
        'fecha_registro',
        'fecha_actualizacion'
    ];

    // Fechas y casts
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'fecha_registro';
    protected $updatedField  = 'fecha_actualizacion';

    // Validaciones
    protected $validationRules = [
        'firstname' => 'required|max_length[100]',
        'lastname' => 'required|max_length[100]',
        'email' => 'required|valid_email|max_length[150]',
        'desarrollo' => 'required|in_list[valle_natura,cordelia]',
        'etapa_proceso' => 'in_list[pendiente,calificado,enviar_documento_para_firma,documento_enviado_para_firma,documento_recibido_firmado]',
        'medio_de_contacto' => 'in_list[whatsapp,telefono]'
    ];

    protected $validationMessages = [
        'firstname' => [
            'required' => 'El nombre es obligatorio',
            'max_length' => 'El nombre no puede exceder 100 caracteres'
        ],
        'lastname' => [
            'required' => 'El apellido paterno es obligatorio',
            'max_length' => 'El apellido paterno no puede exceder 100 caracteres'
        ],
        'email' => [
            'required' => 'El email es obligatorio',
            'valid_email' => 'Debe proporcionar un email válido',
            'max_length' => 'El email no puede exceder 150 caracteres'
        ],
        'desarrollo' => [
            'required' => 'Debe seleccionar un desarrollo',
            'in_list' => 'El desarrollo seleccionado no es válido'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['beforeInsert'];
    protected $afterInsert    = ['afterInsert'];
    protected $beforeUpdate   = ['beforeUpdate'];
    protected $afterUpdate    = ['afterUpdate'];

    // ===============================================
    // CALLBACKS
    // ===============================================

    protected function beforeInsert(array $data): array
    {
        // Generar folio si no existe
        if (empty($data['data']['folio'])) {
            $data['data']['folio'] = $this->generarFolio();
        }
        
        // Generar identificador de expediente
        if (empty($data['data']['identificador_expediente'])) {
            $data['data']['identificador_expediente'] = $this->generarIdentificadorExpediente($data['data']);
        }
        
        // Establecer valores por defecto
        $data['data']['fecha_registro'] = $data['data']['fecha_registro'] ?? date('Y-m-d H:i:s');
        $data['data']['fuente_registro'] = $data['data']['fuente_registro'] ?? 'formulario_web';
        $data['data']['activo'] = $data['data']['activo'] ?? 1;
        $data['data']['convertido_a_cliente'] = 0;
        
        return $data;
    }

    protected function afterInsert(array $data): array
    {
        // Log del nuevo lead
        log_message('info', "Nuevo lead registrado ID: {$data['id']}");
        
        return $data;
    }

    protected function beforeUpdate(array $data): array
    {
        // Actualizar fecha de actualización
        $data['data']['fecha_actualizacion'] = date('Y-m-d H:i:s');
        
        return $data;
    }

    protected function afterUpdate(array $data): array
    {
        // Log de actualización
        if (isset($data['id'])) {
            log_message('info', "Lead actualizado ID: {$data['id'][0]}");
        }
        
        return $data;
    }

    // ===============================================
    // MÉTODOS DE BÚSQUEDA Y FILTRADO
    // ===============================================

    /**
     * Buscar leads por términos
     */
    public function buscarLeads(string $termino, int $limite = 10): array
    {
        return $this->like('firstname', $termino)
                   ->orLike('lastname', $termino)
                   ->orLike('apellido_materno', $termino)
                   ->orLike('email', $termino)
                   ->orLike('telefono', $termino)
                   ->orLike('mobilephone', $termino)
                   ->orLike('rfc', $termino)
                   ->orLike('curp', $termino)
                   ->orLike('identificador_expediente', $termino)
                   ->where('activo', 1)
                   ->orderBy('fecha_registro', 'DESC')
                   ->limit($limite)
                   ->findAll();
    }

    /**
     * Obtener leads por etapa de proceso
     */
    public function obtenerPorEtapa(string $etapa): array
    {
        return $this->where('etapa_proceso', $etapa)
                   ->where('activo', 1)
                   ->orderBy('fecha_registro', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener leads por desarrollo
     */
    public function obtenerPorDesarrollo(string $desarrollo): array
    {
        return $this->where('desarrollo', $desarrollo)
                   ->where('activo', 1)
                   ->orderBy('fecha_registro', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener leads pendientes de sincronización
     */
    public function obtenerPendientesSincronizacion(string $servicio = null): array
    {
        $builder = $this->where('activo', 1);
        
        if ($servicio === 'hubspot') {
            $builder->where('hubspot_sync_status', 'pending');
        } elseif ($servicio === 'google_drive') {
            $builder->where('google_drive_sync_status', 'pending');
        } else {
            $builder->groupStart()
                   ->where('hubspot_sync_status', 'pending')
                   ->orWhere('google_drive_sync_status', 'pending')
                   ->groupEnd();
        }
        
        return $builder->orderBy('fecha_registro', 'ASC')
                      ->findAll();
    }

    /**
     * Obtener leads convertibles a cliente
     */
    public function obtenerConvertiblesACliente(): array
    {
        return $this->where('etapa_proceso', 'documento_recibido_firmado')
                   ->where('convertido_a_cliente', 0)
                   ->where('activo', 1)
                   ->groupStart()
                       ->where('rfc !=', '')
                       ->orWhere('curp !=', '')
                   ->groupEnd()
                   ->orderBy('fecha_registro', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener leads ya convertidos
     */
    public function obtenerConvertidos(): array
    {
        return $this->where('convertido_a_cliente', 1)
                   ->where('activo', 1)
                   ->orderBy('fecha_conversion', 'DESC')
                   ->findAll();
    }

    // ===============================================
    // MÉTODOS DE ACTUALIZACIÓN MASIVA
    // ===============================================

    /**
     * Actualizar estado de sincronización de HubSpot
     */
    public function actualizarSincronizacionHubSpot(int $id, string $estado, ?string $contactId = null, ?string $error = null): bool
    {
        $data = [
            'hubspot_sync_status' => $estado,
            'hubspot_last_sync' => date('Y-m-d H:i:s'),
            'hubspot_sync_attempts' => $this->db->table($this->table)->where('id', $id)->get()->getRow()->hubspot_sync_attempts + 1
        ];
        
        if ($contactId) {
            $data['hubspot_contact_id'] = $contactId;
        }
        
        if ($error) {
            $data['hubspot_sync_error'] = $error;
        }
        
        return $this->update($id, $data);
    }

    /**
     * Actualizar estado de sincronización de Google Drive
     */
    public function actualizarSincronizacionGoogleDrive(int $id, string $estado, ?string $folderId = null, ?string $folderUrl = null, ?string $error = null): bool
    {
        $data = [
            'google_drive_sync_status' => $estado,
            'google_drive_last_sync' => date('Y-m-d H:i:s'),
            'google_drive_sync_attempts' => $this->db->table($this->table)->where('id', $id)->get()->getRow()->google_drive_sync_attempts + 1
        ];
        
        if ($folderId) {
            $data['google_drive_folder_id'] = $folderId;
        }
        
        if ($folderUrl) {
            $data['google_drive_folder_url'] = $folderUrl;
        }
        
        if ($error) {
            $data['google_drive_sync_error'] = $error;
        }
        
        return $this->update($id, $data);
    }

    /**
     * Marcar como convertido a cliente
     */
    public function marcarComoConvertido(int $leadId, int $clienteId): bool
    {
        return $this->update($leadId, [
            'convertido_a_cliente' => 1,
            'fecha_conversion' => date('Y-m-d H:i:s'),
            'cliente_id' => $clienteId
        ]);
    }

    /**
     * Cambiar etapa de proceso
     */
    public function cambiarEtapa(int $id, string $nuevaEtapa): bool
    {
        return $this->update($id, [
            'etapa_proceso' => $nuevaEtapa
        ]);
    }

    // ===============================================
    // MÉTODOS DE ESTADÍSTICAS
    // ===============================================

    /**
     * Obtener estadísticas generales
     */
    public function obtenerEstadisticas(): array
    {
        $stats = [];
        
        // Total de leads activos
        $stats['total_leads'] = $this->where('activo', 1)->countAllResults();
        
        // Por etapa de proceso
        $etapas = ['pendiente', 'calificado', 'enviar_documento_para_firma', 'documento_enviado_para_firma', 'documento_recibido_firmado'];
        foreach ($etapas as $etapa) {
            $stats["etapa_{$etapa}"] = $this->where('etapa_proceso', $etapa)->where('activo', 1)->countAllResults();
        }
        
        // Por desarrollo
        $stats['valle_natura'] = $this->where('desarrollo', 'valle_natura')->where('activo', 1)->countAllResults();
        $stats['cordelia'] = $this->where('desarrollo', 'cordelia')->where('activo', 1)->countAllResults();
        
        // Convertidos
        $stats['convertidos'] = $this->where('convertido_a_cliente', 1)->where('activo', 1)->countAllResults();
        $stats['por_convertir'] = $this->where('etapa_proceso', 'documento_recibido_firmado')
                                      ->where('convertido_a_cliente', 0)
                                      ->where('activo', 1)
                                      ->countAllResults();
        
        // Sincronización
        $stats['hubspot_sincronizados'] = $this->where('hubspot_sync_status', 'success')->where('activo', 1)->countAllResults();
        $stats['google_drive_sincronizados'] = $this->where('google_drive_sync_status', 'success')->where('activo', 1)->countAllResults();
        
        return $stats;
    }

    /**
     * Obtener estadísticas por período
     */
    public function obtenerEstadisticasPorPeriodo(string $fechaInicio, string $fechaFin): array
    {
        return $this->where('fecha_registro >=', $fechaInicio)
                   ->where('fecha_registro <=', $fechaFin)
                   ->where('activo', 1)
                   ->select('DATE(fecha_registro) as fecha, COUNT(*) as total')
                   ->groupBy('DATE(fecha_registro)')
                   ->orderBy('fecha', 'ASC')
                   ->findAll();
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Generar folio único
     */
    private function generarFolio(): string
    {
        $prefijo = 'LEAD';
        $timestamp = date('Ymd');
        $ultimo = $this->select('folio')
                      ->like('folio', "{$prefijo}-{$timestamp}")
                      ->orderBy('id', 'DESC')
                      ->first();
        
        if ($ultimo) {
            $ultimoNumero = (int) substr($ultimo->folio, -4);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        return sprintf('%s-%s-%04d', $prefijo, $timestamp, $nuevoNumero);
    }

    /**
     * Generar identificador de expediente
     */
    private function generarIdentificadorExpediente(array $data): string
    {
        // Prioridad: RFC > CURP > Nombre + ID
        if (!empty($data['rfc'])) {
            return strtoupper(trim($data['rfc']));
        }
        
        if (!empty($data['curp'])) {
            return strtoupper(trim($data['curp']));
        }
        
        if (!empty($data['rfc_curp'])) {
            return strtoupper(trim($data['rfc_curp']));
        }
        
        // Fallback: generar basado en nombre
        $nombre = strtoupper(substr($data['firstname'] ?? 'LEAD', 0, 3));
        $apellido = strtoupper(substr($data['lastname'] ?? 'X', 0, 3));
        $timestamp = date('Ymd');
        
        return "LEAD_{$nombre}{$apellido}_{$timestamp}";
    }

    /**
     * Verificar si existe RFC/CURP duplicado
     */
    public function existeIdentificador(string $identificador, ?int $exceptoId = null): bool
    {
        $builder = $this->where('activo', 1)
                       ->groupStart()
                           ->where('rfc', $identificador)
                           ->orWhere('curp', $identificador)
                           ->orWhere('rfc_curp', $identificador)
                           ->orWhere('identificador_expediente', $identificador)
                       ->groupEnd();
        
        if ($exceptoId) {
            $builder->where('id !=', $exceptoId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Obtener para DataTables
     */
    public function obtenerParaDataTables(array $filtros = []): array
    {
        $builder = $this->select('
            id, folio, firstname, lastname, apellido_materno, email, 
            telefono, mobilephone, desarrollo, etapa_proceso,
            identificador_expediente, rfc, curp,
            hubspot_sync_status, google_drive_sync_status,
            convertido_a_cliente, fecha_registro, activo
        ')->where('activo', 1);
        
        // Aplicar filtros
        if (!empty($filtros['desarrollo'])) {
            $builder->where('desarrollo', $filtros['desarrollo']);
        }
        
        if (!empty($filtros['etapa_proceso'])) {
            $builder->where('etapa_proceso', $filtros['etapa_proceso']);
        }
        
        if (!empty($filtros['convertido'])) {
            $builder->where('convertido_a_cliente', $filtros['convertido'] === 'si' ? 1 : 0);
        }
        
        return $builder->orderBy('fecha_registro', 'DESC')->findAll();
    }

    /**
     * Obtener estadísticas generales del sistema
     */
    public function obtenerEstadisticasGenerales(): array
    {
        $builder = $this->builder();
        
        // Estadísticas básicas
        $estadisticas = $builder->select('
            COUNT(*) as total_registros,
            COUNT(DISTINCT agente_referido) as total_agentes_activos,
            SUM(CASE WHEN desarrollo = "valle_natura" THEN 1 ELSE 0 END) as total_valle_natura,
            SUM(CASE WHEN desarrollo = "cordelia" THEN 1 ELSE 0 END) as total_cordelia,
            SUM(CASE WHEN hubspot_sync_status = "success" THEN 1 ELSE 0 END) as hubspot_exitosos,
            SUM(CASE WHEN hubspot_sync_status = "failed" THEN 1 ELSE 0 END) as hubspot_fallidos,
            SUM(CASE WHEN hubspot_sync_status = "pending" THEN 1 ELSE 0 END) as hubspot_pendientes,
            SUM(CASE WHEN google_drive_sync_status = "success" THEN 1 ELSE 0 END) as google_drive_exitosos,
            SUM(CASE WHEN google_drive_sync_status = "failed" THEN 1 ELSE 0 END) as google_drive_fallidos,
            SUM(CASE WHEN google_drive_sync_status = "pending" THEN 1 ELSE 0 END) as google_drive_pendientes
        ')->where('activo', 1)->get()->getRowArray();

        // Estadísticas por período
        $hoy = date('Y-m-d');
        $semanaActual = date('Y-m-d', strtotime('monday this week'));
        $mesActual = date('Y-m-01');

        $builder = $this->builder();
        $periodos = $builder->select('
            SUM(CASE WHEN DATE(fecha_registro) = "' . $hoy . '" THEN 1 ELSE 0 END) as registros_hoy,
            SUM(CASE WHEN DATE(fecha_registro) >= "' . $semanaActual . '" THEN 1 ELSE 0 END) as registros_esta_semana,
            SUM(CASE WHEN DATE(fecha_registro) >= "' . $mesActual . '" THEN 1 ELSE 0 END) as registros_este_mes
        ')->where('activo', 1)->get()->getRowArray();

        return array_merge($estadisticas, $periodos);
    }

    /**
     * Obtener registros con filtros
     */
    public function obtenerRegistrosConFiltros(array $filtros = []): array
    {
        $builder = $this->builder();

        // Filtro por agente referido
        if (!empty($filtros['agente_referido'])) {
            $builder->where('agente_referido', $filtros['agente_referido']);
        }

        // Filtro por desarrollo
        if (!empty($filtros['desarrollo'])) {
            $builder->where('desarrollo', $filtros['desarrollo']);
        }

        // Filtro por estado de sync HubSpot
        if (!empty($filtros['hubspot_sync_status'])) {
            $builder->where('hubspot_sync_status', $filtros['hubspot_sync_status']);
        }

        // Filtro por estado de sync Google Drive
        if (!empty($filtros['google_drive_sync_status'])) {
            $builder->where('google_drive_sync_status', $filtros['google_drive_sync_status']);
        }

        // Filtro por rango de fechas
        if (!empty($filtros['fecha_desde'])) {
            $builder->where('fecha_registro >=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $builder->where('fecha_registro <=', $filtros['fecha_hasta'] . ' 23:59:59');
        }

        // Filtro por búsqueda de texto
        if (!empty($filtros['busqueda'])) {
            $builder->groupStart()
                    ->like('firstname', $filtros['busqueda'])
                    ->orLike('lastname', $filtros['busqueda'])
                    ->orLike('apellido_materno', $filtros['busqueda'])
                    ->orLike('email', $filtros['busqueda'])
                    ->orLike('mobilephone', $filtros['busqueda'])
                    ->groupEnd();
        }

        // Ordenamiento
        $orderBy = $filtros['order_by'] ?? 'fecha_registro';
        $orderDir = $filtros['order_dir'] ?? 'DESC';
        $builder->orderBy($orderBy, $orderDir);

        // Paginación
        if (isset($filtros['limit'])) {
            $offset = $filtros['offset'] ?? 0;
            $builder->limit($filtros['limit'], $offset);
        }

        return $builder->where('activo', 1)->get()->getResult($this->returnType);
    }

    /**
     * Obtener top agentes por número de registros
     */
    public function obtenerTopAgentes(int $limite = 10, string $fechaInicio = null, string $fechaFin = null): array
    {
        $builder = $this->builder();
        $builder->select('
            agente_referido,
            COUNT(*) as total_registros,
            SUM(CASE WHEN desarrollo = "valle_natura" THEN 1 ELSE 0 END) as valle_natura,
            SUM(CASE WHEN desarrollo = "cordelia" THEN 1 ELSE 0 END) as cordelia,
            SUM(CASE WHEN hubspot_sync_status = "success" THEN 1 ELSE 0 END) as hubspot_exitosos,
            MAX(fecha_registro) as ultimo_registro
        ');

        $builder->where('agente_referido IS NOT NULL')
                ->where('activo', 1);

        if ($fechaInicio) {
            $builder->where('fecha_registro >=', $fechaInicio);
        }

        if ($fechaFin) {
            $builder->where('fecha_registro <=', $fechaFin . ' 23:59:59');
        }

        $builder->groupBy('agente_referido')
                ->orderBy('total_registros', 'DESC')
                ->limit($limite);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener métricas por agente referido
     */
    public function obtenerMetricasPorAgente(string $agenteReferido, string $fechaInicio = null, string $fechaFin = null): array
    {
        $builder = $this->builder();
        $builder->select('
            COUNT(*) as total_registros,
            SUM(CASE WHEN desarrollo = "valle_natura" THEN 1 ELSE 0 END) as registros_valle_natura,
            SUM(CASE WHEN desarrollo = "cordelia" THEN 1 ELSE 0 END) as registros_cordelia,
            SUM(CASE WHEN firstname IS NOT NULL AND firstname != "" THEN 1 ELSE 0 END) as con_nombre,
            SUM(CASE WHEN hubspot_sync_status = "success" THEN 1 ELSE 0 END) as hubspot_exitosos,
            SUM(CASE WHEN google_drive_sync_status = "success" THEN 1 ELSE 0 END) as google_drive_exitosos,
            MIN(fecha_registro) as primer_registro,
            MAX(fecha_registro) as ultimo_registro
        ');

        $builder->where('agente_referido', $agenteReferido)
                ->where('activo', 1);

        if ($fechaInicio) {
            $builder->where('fecha_registro >=', $fechaInicio);
        }

        if ($fechaFin) {
            $builder->where('fecha_registro <=', $fechaFin . ' 23:59:59');
        }

        $result = $builder->get()->getRowArray();

        // Calcular porcentajes
        if ($result['total_registros'] > 0) {
            $result['tasa_exito_hubspot'] = round(($result['hubspot_exitosos'] / $result['total_registros']) * 100, 2);
            $result['tasa_exito_google_drive'] = round(($result['google_drive_exitosos'] / $result['total_registros']) * 100, 2);
        } else {
            $result['tasa_exito_hubspot'] = 0;
            $result['tasa_exito_google_drive'] = 0;
        }

        return $result;
    }

    /**
     * Obtener registros con errores de sincronización
     */
    public function obtenerRegistrosConErrores(): array
    {
        $builder = $this->builder();
        $builder->where('hubspot_sync_status', 'failed')
                ->orWhere('google_drive_sync_status', 'failed')
                ->where('activo', 1)
                ->orderBy('fecha_registro', 'DESC');

        return $builder->get()->getResult($this->returnType);
    }

    /**
     * Obtener registros pendientes de sincronización
     */
    public function obtenerRegistrosPendientes(): array
    {
        $builder = $this->builder();
        $builder->where('hubspot_sync_status', 'pending')
                ->orWhere('google_drive_sync_status', 'pending')
                ->where('activo', 1)
                ->orderBy('fecha_registro', 'ASC');

        return $builder->get()->getResult($this->returnType);
    }
}