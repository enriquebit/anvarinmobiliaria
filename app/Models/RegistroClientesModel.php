<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\RegistroCliente;

class RegistroClientesModel extends Model
{
    protected $table            = 'registro_leads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = RegistroCliente::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        // Campos nuevos (HubSpot standard)
        'firstname',
        'lastname',
        'apellido_materno',
        'rfc_curp',
        'mobilephone',
        'phone',
        // Campos legacy (mantener compatibilidad)
        'nombre',
        'apellido_paterno', 
        'telefono',
        'medio_contacto',
        'medio_de_contacto',
        // Campos comunes
        'email',
        'desarrollo',
        'manzana',
        'lote',
        'numero_casa_depto',
        'nombre_copropietario',
        'parentesco_copropietario',
        'agente_referido',
        'hubspot_contact_id',
        'hubspot_ticket_id',
        'hubspot_sync_status',
        'hubspot_sync_error',
        'hubspot_sync_attempts',
        'hubspot_last_sync',
        'google_drive_folder_id',
        'google_drive_folder_url',
        'google_drive_sync_status',
        'google_drive_sync_error',
        'acepta_terminos',
        'ip_address',
        'user_agent',
        'estado_registro',
        'folio'  // Campo folio ahora manejado por CodeIgniter
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'fecha_registro';
    protected $updatedField  = 'fecha_actualizacion';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        // Campos nuevos (HubSpot standard)
        'firstname' => 'required|min_length[2]|max_length[100]',
        'lastname' => 'required|min_length[2]|max_length[100]',
        'apellido_materno' => 'permit_empty|min_length[2]|max_length[100]',
        'rfc_curp' => 'required|min_length[13]|max_length[18]|alpha_numeric',
        'mobilephone' => 'required|min_length[10]|max_length[20]',
        'phone' => 'permit_empty|min_length[10]|max_length[20]',
        // Campos legacy (opcionales para compatibilidad)
        'nombre' => 'permit_empty|min_length[2]|max_length[100]',
        'apellido_paterno' => 'permit_empty|min_length[2]|max_length[100]',
        'telefono' => 'permit_empty|min_length[10]|max_length[20]',
        'medio_contacto' => 'permit_empty|in_list[telefono,whatsapp]',
        'medio_de_contacto' => 'required|in_list[whatsapp,telefono]',
        // Campos comunes
        'email' => 'required|valid_email|max_length[150]|is_unique[registro_leads.email,id,{id}]',
        'desarrollo' => 'required|in_list[valle_natura,cordelia]',
        'manzana' => 'permit_empty|max_length[10]',
        'lote' => 'permit_empty|max_length[10]',
        'numero_casa_depto' => 'permit_empty|max_length[10]',
        'nombre_copropietario' => 'permit_empty|max_length[255]',
        'parentesco_copropietario' => 'permit_empty|max_length[100]',
        'agente_referido' => 'permit_empty|max_length[50]',
        'acepta_terminos' => 'permit_empty|integer|in_list[0,1]'
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Este correo electrónico ya está registrado en el sistema.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['beforeInsert', 'generarFolioAntes'];
    protected $afterInsert    = ['afterInsert', 'actualizarFolioDespues'];
    protected $beforeUpdate   = ['beforeUpdate'];
    protected $afterUpdate    = ['afterUpdate'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Callback: Antes de insertar
     */
    protected function beforeInsert(array $data): array
    {
        // Normalizar datos nuevos (HubSpot standard)
        if (isset($data['data']['firstname'])) {
            $data['data']['firstname'] = ucwords(strtolower(trim($data['data']['firstname'])));
        }
        
        if (isset($data['data']['lastname'])) {
            $data['data']['lastname'] = ucwords(strtolower(trim($data['data']['lastname'])));
        }
        
        if (isset($data['data']['apellido_materno'])) {
            $data['data']['apellido_materno'] = ucwords(strtolower(trim($data['data']['apellido_materno'])));
        }
        
        // Normalizar datos legacy (compatibilidad)
        if (isset($data['data']['nombre'])) {
            $data['data']['nombre'] = ucwords(strtolower(trim($data['data']['nombre'])));
        }
        
        if (isset($data['data']['apellido_paterno'])) {
            $data['data']['apellido_paterno'] = ucwords(strtolower(trim($data['data']['apellido_paterno'])));
        }
        
        if (isset($data['data']['email'])) {
            $data['data']['email'] = strtolower(trim($data['data']['email']));
        }
        
        // Limpiar teléfonos - solo números
        if (isset($data['data']['telefono'])) {
            $data['data']['telefono'] = preg_replace('/[^0-9]/', '', $data['data']['telefono']);
        }
        
        if (isset($data['data']['mobilephone'])) {
            $data['data']['mobilephone'] = preg_replace('/[^0-9]/', '', $data['data']['mobilephone']);
        }
        
        if (isset($data['data']['phone'])) {
            $data['data']['phone'] = preg_replace('/[^0-9]/', '', $data['data']['phone']);
        }
        
        // Normalizar acepta_terminos
        if (isset($data['data']['acepta_terminos'])) {
            if ($data['data']['acepta_terminos'] === 'on' || $data['data']['acepta_terminos'] === 'true') {
                $data['data']['acepta_terminos'] = 1;
            } elseif ($data['data']['acepta_terminos'] === 'false') {
                $data['data']['acepta_terminos'] = 0;
            }
        }

        // Log de creación
        error_log('[REGISTRO_MODEL] Creando nuevo registro: ' . ($data['data']['email'] ?? 'sin_email'));

        return $data;
    }

    /**
     * Callback: Después de insertar
     */
    protected function afterInsert(array $data): array
    {
        if (isset($data['id'])) {
            error_log('[REGISTRO_MODEL] Registro creado exitosamente con ID: ' . $data['id']);
        }

        return $data;
    }

    /**
     * Callback: Antes de actualizar
     */
    protected function beforeUpdate(array $data): array
    {
        // Log de actualización
        if (isset($data['id'])) {
            error_log('[REGISTRO_MODEL] Actualizando registro ID: ' . json_encode($data['id']));
        }

        return $data;
    }

    /**
     * Callback: Después de actualizar
     */
    protected function afterUpdate(array $data): array
    {
        if (isset($data['id'])) {
            error_log('[REGISTRO_MODEL] Registro actualizado exitosamente ID: ' . json_encode($data['id']));
        }

        return $data;
    }

    // ===============================================
    // MÉTODOS PERSONALIZADOS DE CONSULTA
    // ===============================================

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

        return $builder->get()->getResult($this->returnType);
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
            SUM(CASE WHEN nombre_copropietario IS NOT NULL AND nombre_copropietario != "" THEN 1 ELSE 0 END) as con_copropietario,
            SUM(CASE WHEN hubspot_sync_status = "success" THEN 1 ELSE 0 END) as hubspot_exitosos,
            SUM(CASE WHEN google_drive_sync_status = "success" THEN 1 ELSE 0 END) as google_drive_exitosos,
            MIN(fecha_registro) as primer_registro,
            MAX(fecha_registro) as ultimo_registro
        ');

        $builder->where('agente_referido', $agenteReferido);

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
            $result['porcentaje_con_copropietario'] = round(($result['con_copropietario'] / $result['total_registros']) * 100, 2);
        } else {
            $result['tasa_exito_hubspot'] = 0;
            $result['tasa_exito_google_drive'] = 0;
            $result['porcentaje_con_copropietario'] = 0;
        }

        return $result;
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
        ')->get()->getRowArray();

        // Estadísticas por período
        $hoy = date('Y-m-d');
        $semanaActual = date('Y-m-d', strtotime('monday this week'));
        $mesActual = date('Y-m-01');

        $builder = $this->builder();
        $periodos = $builder->select('
            SUM(CASE WHEN DATE(fecha_registro) = "' . $hoy . '" THEN 1 ELSE 0 END) as registros_hoy,
            SUM(CASE WHEN DATE(fecha_registro) >= "' . $semanaActual . '" THEN 1 ELSE 0 END) as registros_esta_semana,
            SUM(CASE WHEN DATE(fecha_registro) >= "' . $mesActual . '" THEN 1 ELSE 0 END) as registros_este_mes
        ')->get()->getRowArray();

        return array_merge($estadisticas, $periodos);
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

        $builder->where('agente_referido IS NOT NULL');

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
     * Obtener registros con errores de sincronización
     */
    public function obtenerRegistrosConErrores(): array
    {
        $builder = $this->builder();
        $builder->where('hubspot_sync_status', 'failed')
                ->orWhere('google_drive_sync_status', 'failed')
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
                ->orderBy('fecha_registro', 'ASC');

        return $builder->get()->getResult($this->returnType);
    }

    /**
     * Incrementar contador de intentos de sincronización
     */
    public function incrementarIntentosSync(int $registroId, string $servicio): bool
    {
        $campo = $servicio === 'hubspot' ? 'hubspot_sync_attempts' : 'google_drive_sync_attempts';
        
        $builder = $this->builder();
        $builder->set($campo, "$campo + 1", false)
                ->where('id', $registroId)
                ->update();

        return $this->db->affectedRows() > 0;
    }

    /**
     * Marcar registro como sincronizado exitosamente
     */
    public function marcarSincronizacionExitosa(int $registroId, string $servicio, array $datosAdicionales = []): bool
    {
        $updateData = [];

        if ($servicio === 'hubspot') {
            $updateData['hubspot_sync_status'] = 'success';
            $updateData['hubspot_sync_error'] = null;
            $updateData['hubspot_last_sync'] = date('Y-m-d H:i:s');
            
            if (isset($datosAdicionales['contact_id'])) {
                $updateData['hubspot_contact_id'] = $datosAdicionales['contact_id'];
            }
            
            if (isset($datosAdicionales['ticket_id'])) {
                $updateData['hubspot_ticket_id'] = $datosAdicionales['ticket_id'];
            }
        } else {
            $updateData['google_drive_sync_status'] = 'success';
            $updateData['google_drive_sync_error'] = null;
            
            if (isset($datosAdicionales['folder_id'])) {
                $updateData['google_drive_folder_id'] = $datosAdicionales['folder_id'];
            }
            
            if (isset($datosAdicionales['folder_url'])) {
                $updateData['google_drive_folder_url'] = $datosAdicionales['folder_url'];
            }
        }

        return $this->update($registroId, $updateData);
    }

    /**
     * Marcar registro como fallido en sincronización
     */
    public function marcarSincronizacionFallida(int $registroId, string $servicio, string $error): bool
    {
        $updateData = [];

        if ($servicio === 'hubspot') {
            $updateData['hubspot_sync_status'] = 'failed';
            $updateData['hubspot_sync_error'] = $error;
        } else {
            $updateData['google_drive_sync_status'] = 'failed';
            $updateData['google_drive_sync_error'] = $error;
        }

        return $this->update($registroId, $updateData);
    }

    // ===============================================
    // MÉTODOS PARA MANEJO DE FOLIOS SIN TRIGGERS
    // ===============================================

    public function __construct()
    {
        parent::__construct();
        
        // Cargar helper de folios
        helper('folio');
        
        //log_message('info', '📋 RegistroClientesModel: Inicializado con lógica de folios sin triggers');
    }

    /**
     * Genera folio antes del insert (estimado)
     * Este se ejecuta antes de conocer el ID real
     */
    protected function generarFolioAntes(array $data): array
    {
        // Si ya viene un folio, no lo modificamos
        if (!empty($data['data']['folio'])) {
            log_message('info', "📄 Folio ya proporcionado: " . $data['data']['folio']);
            return $data;
        }
        
        try {
            // Generar folio estimado basado en conteo actual
            $folioEstimado = generarFolio($this->table, 'REG');
            $data['data']['folio'] = $folioEstimado;
            
            log_message('info', "📄 Folio estimado generado: {$folioEstimado}");
            
        } catch (\Exception $e) {
            log_message('error', "❌ Error en generarFolioAntes: " . $e->getMessage());
            // Si falla, se genera en afterInsert
            $data['data']['folio'] = null;
        }
        
        return $data;
    }
    
    /**
     * Actualiza el folio después del insert con el ID real
     * Esto garantiza que el folio sea único y correcto
     */
    protected function actualizarFolioDespues(array $data): array
    {
        if (!isset($data['result']) || !$data['result']) {
            return $data;
        }
        
        try {
            $insertId = $this->getInsertID();
            
            if (!$insertId) {
                log_message('warning', "⚠️ No se pudo obtener el ID insertado");
                return $data;
            }
            
            // Generar folio definitivo con el ID real
            $folioDefinitivo = generarFolioConId($insertId, 'REG');
            
            // Actualizar el registro con el folio correcto
            $updated = $this->update($insertId, ['folio' => $folioDefinitivo]);
            
            if ($updated) {
                log_message('info', "✅ Folio definitivo actualizado: {$folioDefinitivo} para ID {$insertId}");
            } else {
                log_message('error', "❌ Error actualizando folio definitivo para ID {$insertId}");
            }
            
        } catch (\Exception $e) {
            log_message('error', "❌ Error en actualizarFolioDespues: " . $e->getMessage());
        }
        
        return $data;
    }
    
    /**
     * Método especializado para insertar con folio automático
     * Reemplaza completamente la funcionalidad del trigger
     */
    public function insertarConFolio(array $data): ?int
    {
        // Iniciar transacción
        $this->db->transStart();
        
        try {
            log_message('info', '🔄 Iniciando inserción con folio automático');
            
            // Asegurar que no viene folio duplicado
            unset($data['folio']);
            
            // Insertar primero sin folio para obtener ID real
            $inserted = $this->insert($data);
            
            if (!$inserted) {
                $errors = $this->errors();
                log_message('error', '❌ Error en insert inicial: ' . json_encode($errors));
                throw new \RuntimeException('Error al insertar registro: ' . implode(', ', $errors));
            }
            
            $insertId = $this->getInsertID();
            
            if (!$insertId) {
                throw new \RuntimeException('No se pudo obtener el ID del registro insertado');
            }
            
            log_message('info', "✅ Registro insertado con ID: {$insertId}");
            
            // Generar folio definitivo con ID real
            $folioDefinitivo = generarFolioConId($insertId, 'REG');
            
            // Actualizar con el folio correcto
            $updated = $this->update($insertId, ['folio' => $folioDefinitivo]);
            
            if (!$updated) {
                throw new \RuntimeException('Error al actualizar con folio definitivo');
            }
            
            log_message('info', "✅ Folio definitivo asignado: {$folioDefinitivo}");
            
            // Completar transacción
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción de inserción con folio');
            }
            
            log_message('info', "🎉 Registro creado exitosamente - ID: {$insertId}, Folio: {$folioDefinitivo}");
            
            return $insertId;
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '💥 Error en insertarConFolio: ' . $e->getMessage());
            log_message('error', '🔍 Stack trace: ' . $e->getTraceAsString());
            
            // Agregar error al modelo para que se pueda recuperar
            $this->errors = ['insertarConFolio' => $e->getMessage()];
            
            return null;
        }
    }
    
    /**
     * Obtener registros con folio válido
     */
    public function getRegistrosConFolio(int $limit = 50): array
    {
        return $this->where('folio IS NOT NULL')
                   ->where('folio !=', '')
                   ->orderBy('fecha_registro', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Buscar registro por folio
     */
    public function buscarPorFolio(string $folio): ?array
    {
        return $this->where('folio', $folio)->first();
    }
    
    /**
     * Obtener estadísticas de registros por año
     */
    public function getEstadisticasPorAno(int $ano = null): array
    {
        if (!$ano) {
            $ano = date('Y');
        }
        
        return [
            'ano' => $ano,
            'total_registros' => $this->where('YEAR(fecha_registro)', $ano)->countAllResults(false),
            'con_folio' => $this->where('YEAR(fecha_registro)', $ano)
                               ->where('folio IS NOT NULL')
                               ->where('folio !=', '')
                               ->countAllResults(false),
            'pendientes_folio' => $this->where('YEAR(fecha_registro)', $ano)
                                      ->groupStart()
                                          ->where('folio IS NULL')
                                          ->orWhere('folio', '')
                                      ->groupEnd()
                                      ->countAllResults()
        ];
    }
    
    /**
     * Reparar folios faltantes o incorrectos
     * Útil para migración de datos existentes
     */
    public function repararFolios(): array
    {
        $registrosSinFolio = $this->groupStart()
                                 ->where('folio IS NULL')
                                 ->orWhere('folio', '')
                                 ->orWhere('folio', 'REG-2025-000000') // Folio placeholder
                             ->groupEnd()
                             ->orderBy('id', 'ASC')
                             ->findAll();
        
        $reparados = 0;
        $errores = [];
        
        foreach ($registrosSinFolio as $registro) {
            try {
                $folioNuevo = generarFolioConId($registro->id ?? $registro['id'], 'REG');
                
                $updated = $this->update($registro->id ?? $registro['id'], ['folio' => $folioNuevo]);
                
                if ($updated) {
                    $reparados++;
                    log_message('info', "🔧 Folio reparado - ID: {$registro->id}, Nuevo folio: {$folioNuevo}");
                } else {
                    $errores[] = "Error actualizando ID {$registro->id}";
                }
                
            } catch (\Exception $e) {
                $errores[] = "ID {$registro->id}: " . $e->getMessage();
                log_message('error', "❌ Error reparando folio ID {$registro->id}: " . $e->getMessage());
            }
        }
        
        log_message('info', "🔧 Reparación completada - {$reparados} folios reparados, " . count($errores) . " errores");
        
        return [
            'total_procesados' => count($registrosSinFolio),
            'reparados' => $reparados,
            'errores' => $errores
        ];
    }
}