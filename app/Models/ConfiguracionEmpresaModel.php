<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ConfiguracionEmpresa;

/**
 * ConfiguracionEmpresaModel
 * 
 * Modelo para gestión de configuración financiera por empresa
 * Implementa funcionalidades del sistema legacy de configuración
 */
class ConfiguracionEmpresaModel extends Model
{
    protected $table            = 'configuracion_empresa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = ConfiguracionEmpresa::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'empresas_id', 'apartado_minimo', 'dias_apartado', 'porcentaje_enganche',
        'dias_enganche', 'comision_apartado', 'porcentaje_comision_total',
        'tasa_interes_anual', 'tasa_moratoria_anual', 'folio_apartado',
        'folio_enganche', 'folio_venta', 'folio_varios_pagos',
        'redondear_pagos_mensuales', 'permitir_pagos_parciales'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'empresas_id'                 => 'required|integer|is_not_unique[empresas.id]',
        'apartado_minimo'             => 'required|numeric|greater_than_equal_to[0]',
        'dias_apartado'               => 'required|integer|greater_than[0]|less_than_equal_to[365]',
        'porcentaje_enganche'         => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'dias_enganche'               => 'required|integer|greater_than[0]|less_than_equal_to[365]',
        'comision_apartado'           => 'required|numeric|greater_than_equal_to[0]',
        'porcentaje_comision_total'   => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[50]',
        'tasa_interes_anual'          => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'tasa_moratoria_anual'        => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'
    ];

    protected $validationMessages = [
        'empresas_id' => [
            'required' => 'Debe especificar una empresa',
            'is_not_unique' => 'La empresa especificada no existe'
        ],
        'apartado_minimo' => [
            'required' => 'El monto mínimo de apartado es obligatorio',
            'greater_than_equal_to' => 'El monto mínimo no puede ser negativo'
        ],
        'porcentaje_enganche' => [
            'required' => 'El porcentaje de enganche es obligatorio',
            'less_than_equal_to' => 'El porcentaje de enganche no puede ser mayor a 100%'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Obtener configuración por empresa
     */
    public function getConfiguracionPorEmpresa(int $empresaId): ?ConfiguracionEmpresa
    {
        return $this->where('empresas_id', $empresaId)->first();
    }

    /**
     * Crear configuración inicial para empresa
     */
    public function crearConfiguracionInicial(int $empresaId): ConfiguracionEmpresa
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Verificar que no exista configuración
            $existente = $this->getConfiguracionPorEmpresa($empresaId);
            if ($existente) {
                throw new \Exception('Ya existe configuración para esta empresa');
            }

            $configuracion = new ConfiguracionEmpresa();
            $configuracion->empresas_id = $empresaId;
            $configuracion->aplicarConfiguracionPredeterminada();
            
            $configuracion->validarConfiguracion();
            
            if (!$this->save($configuracion)) {
                throw new \Exception('Error al crear la configuración inicial');
            }

            $configuracion->id = $this->getInsertID();

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de configuración inicial');
            }

            return $configuracion;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Actualizar configuración completa
     */
    public function actualizarConfiguracion(
        int $empresaId,
        array $parametros
    ): ConfiguracionEmpresa {
        
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
            
            if (!$configuracion) {
                throw new \Exception('No existe configuración para esta empresa');
            }

            // Actualizar apartado
            if (isset($parametros['apartado_minimo']) || isset($parametros['dias_apartado'])) {
                $configuracion->configurarApartado(
                    $parametros['apartado_minimo'] ?? $configuracion->apartado_minimo,
                    $parametros['dias_apartado'] ?? $configuracion->dias_apartado
                );
            }

            // Actualizar enganche
            if (isset($parametros['porcentaje_enganche']) || isset($parametros['dias_enganche'])) {
                $configuracion->configurarEnganche(
                    $parametros['porcentaje_enganche'] ?? $configuracion->porcentaje_enganche,
                    $parametros['dias_enganche'] ?? $configuracion->dias_enganche
                );
            }

            // Actualizar comisiones
            if (isset($parametros['comision_apartado']) || isset($parametros['porcentaje_comision_total'])) {
                $configuracion->configurarComisiones(
                    $parametros['comision_apartado'] ?? $configuracion->comision_apartado,
                    $parametros['porcentaje_comision_total'] ?? $configuracion->porcentaje_comision_total
                );
            }

            // Actualizar intereses
            if (isset($parametros['tasa_interes_anual']) || isset($parametros['tasa_moratoria_anual'])) {
                $configuracion->configurarIntereses(
                    $parametros['tasa_interes_anual'] ?? $configuracion->tasa_interes_anual,
                    $parametros['tasa_moratoria_anual'] ?? $configuracion->tasa_moratoria_anual
                );
            }

            // Actualizar opciones
            if (isset($parametros['redondear_pagos_mensuales']) || isset($parametros['permitir_pagos_parciales'])) {
                $configuracion->configurarOpciones(
                    $parametros['redondear_pagos_mensuales'] ?? $configuracion->redondear_pagos_mensuales,
                    $parametros['permitir_pagos_parciales'] ?? $configuracion->permitir_pagos_parciales
                );
            }

            $configuracion->validarConfiguracion();
            
            if (!$this->save($configuracion)) {
                throw new \Exception('Error al actualizar la configuración');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de actualización');
            }

            return $configuracion;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Incrementar folio específico de manera segura
     */
    public function incrementarFolio(int $empresaId, string $tipoFolio): string
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
            
            if (!$configuracion) {
                throw new \Exception('No existe configuración para esta empresa');
            }

            $nuevoFolio = '';

            switch ($tipoFolio) {
                case 'apartado':
                    $nuevoFolio = $configuracion->getSiguienteFolioApartado();
                    $configuracion->incrementarFolioApartado();
                    break;
                case 'enganche':
                    $nuevoFolio = $configuracion->getSiguienteFolioEnganche();
                    $configuracion->incrementarFolioEnganche();
                    break;
                case 'venta':
                    $nuevoFolio = $configuracion->getSiguienteFolioVenta();
                    $configuracion->incrementarFolioVenta();
                    break;
                case 'varios_pagos':
                    $nuevoFolio = $configuracion->getSiguienteFolioVariosPagos();
                    $configuracion->incrementarFolioVariosPagos();
                    break;
                default:
                    throw new \Exception('Tipo de folio no válido');
            }

            if (!$this->save($configuracion)) {
                throw new \Exception('Error al incrementar el folio');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de folio');
            }

            return $nuevoFolio;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Obtener todas las configuraciones activas
     */
    public function getConfiguracionesActivas(): array
    {
        $builder = $this->builder();
        
        $builder->select('
            configuracion_empresa.*,
            empresas.nombre as empresa_nombre,
            empresas.razon_social as empresa_razon_social
        ');
        
        $builder->join('empresas', 'empresas.id = configuracion_empresa.empresas_id', 'inner');
        $builder->orderBy('empresas.nombre', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Validar configuración antes de operaciones críticas
     */
    public function validarConfiguracionOperativa(int $empresaId): bool
    {
        $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
        
        if (!$configuracion) {
            throw new \Exception('No existe configuración para esta empresa');
        }

        try {
            $configuracion->validarConfiguracion();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Configuración inválida: ' . $e->getMessage());
        }
    }

    /**
     * Obtener parámetros financieros específicos
     */
    public function getParametrosFinancieros(int $empresaId): array
    {
        $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
        
        if (!$configuracion) {
            throw new \Exception('No existe configuración para esta empresa');
        }

        return [
            'apartado_minimo' => $configuracion->apartado_minimo,
            'porcentaje_enganche' => $configuracion->porcentaje_enganche,
            'comision_apartado' => $configuracion->comision_apartado,
            'porcentaje_comision_total' => $configuracion->porcentaje_comision_total,
            'tasa_interes_anual' => $configuracion->tasa_interes_anual,
            'tasa_interes_mensual' => $configuracion->getTasaInteresMensual(),
            'tasa_moratoria_anual' => $configuracion->tasa_moratoria_anual,
            'tasa_moratoria_diaria' => $configuracion->getTasaMoratoriaDiaria(),
            'redondear_pagos' => $configuracion->redondear_pagos_mensuales,
            'permitir_parciales' => $configuracion->permitir_pagos_parciales
        ];
    }

    /**
     * Calcular montos específicos usando configuración
     */
    public function calcularMontos(int $empresaId, array $parametros): array
    {
        $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
        
        if (!$configuracion) {
            throw new \Exception('No existe configuración para esta empresa');
        }

        $resultados = [];

        // Calcular monto de enganche si se proporciona precio total
        if (isset($parametros['precio_total'])) {
            $resultados['monto_enganche'] = $configuracion->calcularMontoEnganche($parametros['precio_total']);
        }

        // Calcular comisión de apartado
        $resultados['comision_apartado'] = $configuracion->calcularComisionApartado();

        // Calcular comisión total si se proporciona monto de venta
        if (isset($parametros['monto_venta'])) {
            $resultados['comision_total'] = $configuracion->calcularComisionTotal($parametros['monto_venta']);
        }

        // Validar monto mínimo de apartado
        if (isset($parametros['monto_apartado'])) {
            $resultados['cumple_minimo_apartado'] = $configuracion->cumpleMontoMinimoApartado($parametros['monto_apartado']);
        }

        // Calcular fechas límite
        $fechaBase = isset($parametros['fecha_base']) ? new \DateTime($parametros['fecha_base']) : new \DateTime();
        $resultados['fecha_limite_apartado'] = $configuracion->calcularFechaLimiteApartado($fechaBase)->format('Y-m-d');
        $resultados['fecha_limite_enganche'] = $configuracion->calcularFechaLimiteEnganche($fechaBase)->format('Y-m-d');

        return $resultados;
    }

    /**
     * Obtener resumen de folios por empresa
     */
    public function getResumenFolios(int $empresaId): array
    {
        $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
        
        if (!$configuracion) {
            throw new \Exception('No existe configuración para esta empresa');
        }

        return [
            'apartado' => [
                'actual' => $configuracion->folio_apartado,
                'siguiente' => $configuracion->getSiguienteFolioApartado()
            ],
            'enganche' => [
                'actual' => $configuracion->folio_enganche,
                'siguiente' => $configuracion->getSiguienteFolioEnganche()
            ],
            'venta' => [
                'actual' => $configuracion->folio_venta,
                'siguiente' => $configuracion->getSiguienteFolioVenta()
            ],
            'varios_pagos' => [
                'actual' => $configuracion->folio_varios_pagos,
                'siguiente' => $configuracion->getSiguienteFolioVariosPagos()
            ]
        ];
    }

    /**
     * Resetear folios a valores específicos (uso administrativo)
     */
    public function resetearFolios(int $empresaId, array $nuevosFolios): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
            
            if (!$configuracion) {
                throw new \Exception('No existe configuración para esta empresa');
            }

            // Actualizar folios según se proporcionen
            if (isset($nuevosFolios['folio_apartado'])) {
                $configuracion->folio_apartado = max(1, (int)$nuevosFolios['folio_apartado']);
            }
            if (isset($nuevosFolios['folio_enganche'])) {
                $configuracion->folio_enganche = max(1, (int)$nuevosFolios['folio_enganche']);
            }
            if (isset($nuevosFolios['folio_venta'])) {
                $configuracion->folio_venta = max(1, (int)$nuevosFolios['folio_venta']);
            }
            if (isset($nuevosFolios['folio_varios_pagos'])) {
                $configuracion->folio_varios_pagos = max(1, (int)$nuevosFolios['folio_varios_pagos']);
            }

            if (!$this->save($configuracion)) {
                throw new \Exception('Error al resetear los folios');
            }

            $db->transComplete();
            return $db->transStatus() !== false;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Verificar si empresa tiene configuración completa
     */
    public function tieneConfiguracionCompleta(int $empresaId): bool
    {
        $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
        
        if (!$configuracion) {
            return false;
        }

        try {
            $configuracion->validarConfiguracion();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Exportar configuración para respaldo
     */
    public function exportarConfiguracion(int $empresaId): array
    {
        $configuracion = $this->getConfiguracionPorEmpresa($empresaId);
        
        if (!$configuracion) {
            throw new \Exception('No existe configuración para esta empresa');
        }

        return [
            'empresa_id' => $empresaId,
            'fecha_exportacion' => date('Y-m-d H:i:s'),
            'configuracion' => $configuracion->getConfiguracionCompleta(),
            'parametros_raw' => [
                'apartado_minimo' => $configuracion->apartado_minimo,
                'dias_apartado' => $configuracion->dias_apartado,
                'porcentaje_enganche' => $configuracion->porcentaje_enganche,
                'dias_enganche' => $configuracion->dias_enganche,
                'comision_apartado' => $configuracion->comision_apartado,
                'porcentaje_comision_total' => $configuracion->porcentaje_comision_total,
                'tasa_interes_anual' => $configuracion->tasa_interes_anual,
                'tasa_moratoria_anual' => $configuracion->tasa_moratoria_anual,
                'folio_apartado' => $configuracion->folio_apartado,
                'folio_enganche' => $configuracion->folio_enganche,
                'folio_venta' => $configuracion->folio_venta,
                'folio_varios_pagos' => $configuracion->folio_varios_pagos,
                'redondear_pagos_mensuales' => $configuracion->redondear_pagos_mensuales,
                'permitir_pagos_parciales' => $configuracion->permitir_pagos_parciales
            ]
        ];
    }
}