<?php

namespace App\Services;

use App\Models\ConfiguracionEmpresaModel;
use App\Models\EmpresaModel;
use App\Models\FormaPagoModel;

/**
 * ConfiguracionEmpresaService
 * 
 * Servicio especializado para gestión de configuración financiera por empresa
 * Implementa la lógica de negocio del sistema legacy de configuración
 * Maneja parámetros financieros, folios automáticos y validaciones
 */
class ConfiguracionEmpresaService
{
    protected ConfiguracionEmpresaModel $configuracionModel;
    protected EmpresaModel $empresaModel;
    protected FormaPagoModel $formaPagoModel;

    public function __construct()
    {
        $this->configuracionModel = model('ConfiguracionEmpresaModel');
        $this->empresaModel = model('EmpresaModel');
        $this->formaPagoModel = model('FormaPagoModel');
    }

    /**
     * Inicializar configuración completa para empresa nueva
     */
    public function inicializarConfiguracionEmpresa(
        int $empresaId,
        array $configuracionPersonalizada = []
    ): array {
        
        try {
            // Validar que la empresa existe
            $empresa = $this->empresaModel->find($empresaId);
            if (!$empresa) {
                throw new \Exception('Empresa no encontrada');
            }

            // Verificar que no tenga configuración previa
            $configuracionExistente = $this->configuracionModel->getConfiguracionPorEmpresa($empresaId);
            if ($configuracionExistente) {
                throw new \Exception('La empresa ya tiene configuración financiera');
            }

            // Crear configuración inicial
            $configuracion = $this->configuracionModel->crearConfiguracionInicial($empresaId);

            // Aplicar configuración personalizada si se proporciona
            if (!empty($configuracionPersonalizada)) {
                $configuracion = $this->configuracionModel->actualizarConfiguracion(
                    $empresaId,
                    $configuracionPersonalizada
                );
            }

            // Inicializar catálogo de formas de pago si no existe
            $formasPagoExistentes = $this->formaPagoModel->findAll();
            if (empty($formasPagoExistentes)) {
                $this->formaPagoModel->crearCatalogoInicial();
            }

            return [
                'success' => true,
                'configuracion' => $configuracion,
                'empresa_id' => $empresaId,
                'mensaje' => 'Configuración empresarial inicializada correctamente'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar configuración financiera completa
     */
    public function actualizarPerfilFinanciamiento(
        int $empresaId,
        array $nuevosParametros
    ): array {
        
        try {
            // Validar parámetros críticos
            $validacion = $this->validarParametrosFinancieros($nuevosParametros);
            if (!$validacion['valido']) {
                throw new \Exception('Parámetros inválidos: ' . implode(', ', $validacion['errores']));
            }

            // Actualizar configuración
            $configuracion = $this->configuracionModel->actualizarConfiguracion(
                $empresaId,
                $nuevosParametros
            );

            return [
                'success' => true,
                'configuracion' => $configuracion,
                'parametros_actualizados' => array_keys($nuevosParametros),
                'mensaje' => 'Configuración financiera actualizada correctamente'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar próximo folio automáticamente
     */
    public function generarProximoFolio(int $empresaId, string $tipoFolio): array
    {
        try {
            $nuevoFolio = $this->configuracionModel->incrementarFolio($empresaId, $tipoFolio);

            return [
                'success' => true,
                'folio_generado' => $nuevoFolio,
                'tipo_folio' => $tipoFolio,
                'empresa_id' => $empresaId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'folio_generado' => null
            ];
        }
    }

    /**
     * Calcular parámetros financieros para operación específica
     */
    public function calcularParametrosOperacion(
        int $empresaId,
        string $tipoOperacion,
        array $datosOperacion
    ): array {
        
        try {
            $parametros = $this->configuracionModel->getParametrosFinancieros($empresaId);
            $resultados = [];

            switch ($tipoOperacion) {
                case 'apartado':
                    $resultados = $this->calcularParametrosApartado($parametros, $datosOperacion);
                    break;
                    
                case 'enganche':
                    $resultados = $this->calcularParametrosEnganche($parametros, $datosOperacion);
                    break;
                    
                case 'venta_credito':
                    $resultados = $this->calcularParametrosVentaCredito($parametros, $datosOperacion);
                    break;
                    
                case 'comisiones':
                    $resultados = $this->calcularParametrosComisiones($parametros, $datosOperacion);
                    break;
                    
                default:
                    throw new \Exception('Tipo de operación no válido');
            }

            return [
                'success' => true,
                'tipo_operacion' => $tipoOperacion,
                'parametros_utilizados' => $parametros,
                'resultados' => $resultados
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener configuración completa con análisis
     */
    public function getConfiguracionCompleta(int $empresaId): array
    {
        try {
            $configuracion = $this->configuracionModel->getConfiguracionPorEmpresa($empresaId);
            if (!$configuracion) {
                throw new \Exception('No existe configuración para esta empresa');
            }

            $empresa = $this->empresaModel->find($empresaId);
            $resumenFolios = $this->configuracionModel->getResumenFolios($empresaId);
            $configuracionCompleta = $configuracion->getConfiguracionCompleta();

            // Análisis de configuración
            $analisis = [
                'configuracion_valida' => $this->configuracionModel->tieneConfiguracionCompleta($empresaId),
                'parametros_criticos' => $this->identificarParametrosCriticos($configuracion),
                'recomendaciones' => $this->generarRecomendaciones($configuracion),
                'comparacion_mercado' => $this->compararConEstandaresMercado($configuracion)
            ];

            return [
                'empresa' => $empresa,
                'configuracion' => $configuracion,
                'configuracion_completa' => $configuracionCompleta,
                'resumen_folios' => $resumenFolios,
                'analisis' => $analisis
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar configuración antes de operaciones críticas
     */
    public function validarConfiguracionParaOperacion(
        int $empresaId,
        string $tipoOperacion
    ): array {
        
        try {
            $configuracion = $this->configuracionModel->getConfiguracionPorEmpresa($empresaId);
            if (!$configuracion) {
                throw new \Exception('No existe configuración para esta empresa');
            }

            $validaciones = [
                'configuracion_completa' => true,
                'parametros_validos' => true,
                'alertas' => [],
                'errores' => [],
                'puede_operar' => true
            ];

            // Validar configuración general
            try {
                $configuracion->validarConfiguracion();
            } catch (\Exception $e) {
                $validaciones['configuracion_completa'] = false;
                $validaciones['errores'][] = $e->getMessage();
            }

            // Validaciones específicas por tipo de operación
            switch ($tipoOperacion) {
                case 'apartado':
                    $this->validarParametrosApartado($configuracion, $validaciones);
                    break;
                    
                case 'venta':
                    $this->validarParametrosVenta($configuracion, $validaciones);
                    break;
                    
                case 'cobranza':
                    $this->validarParametrosCobranza($configuracion, $validaciones);
                    break;
            }

            $validaciones['puede_operar'] = empty($validaciones['errores']);

            return $validaciones;

        } catch (\Exception $e) {
            return [
                'configuracion_completa' => false,
                'parametros_validos' => false,
                'puede_operar' => false,
                'errores' => [$e->getMessage()],
                'alertas' => []
            ];
        }
    }

    /**
     * Exportar configuración para respaldo/migración
     */
    public function exportarConfiguracion(int $empresaId): array
    {
        try {
            $backup = $this->configuracionModel->exportarConfiguracion($empresaId);
            
            // Agregar metadatos adicionales
            $backup['metadatos'] = [
                'version_sistema' => '1.0.0',
                'fecha_exportacion' => date('Y-m-d H:i:s'),
                'usuario_exportacion' => auth()->id(),
                'hash_verificacion' => md5(json_encode($backup['parametros_raw']))
            ];

            return [
                'success' => true,
                'backup' => $backup
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Importar configuración desde respaldo
     */
    public function importarConfiguracion(int $empresaId, array $backupData): array
    {
        try {
            // Validar estructura del backup
            if (!isset($backupData['parametros_raw'])) {
                throw new \Exception('Estructura de backup inválida');
            }

            // Verificar integridad del backup
            if (isset($backupData['metadatos']['hash_verificacion'])) {
                $hashCalculado = md5(json_encode($backupData['parametros_raw']));
                if ($hashCalculado !== $backupData['metadatos']['hash_verificacion']) {
                    throw new \Exception('Integridad del backup comprometida');
                }
            }

            // Aplicar configuración
            $configuracion = $this->configuracionModel->actualizarConfiguracion(
                $empresaId,
                $backupData['parametros_raw']
            );

            return [
                'success' => true,
                'configuracion' => $configuracion,
                'mensaje' => 'Configuración importada correctamente'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de configuraciones por empresa
     */
    public function generarReporteConfiguraciones(): array
    {
        try {
            $configuraciones = $this->configuracionModel->getConfiguracionesActivas();
            
            $reporte = [
                'total_empresas' => count($configuraciones),
                'configuraciones_detalle' => [],
                'estadisticas' => [
                    'apartado_minimo_promedio' => 0,
                    'porcentaje_enganche_promedio' => 0,
                    'tasa_interes_promedio' => 0,
                    'empresas_con_configuracion_completa' => 0
                ]
            ];

            $sumaApartado = 0;
            $sumaEnganche = 0;
            $sumaTasa = 0;
            $completas = 0;

            foreach ($configuraciones as $config) {
                $empresaCompleta = $this->configuracionModel->tieneConfiguracionCompleta($config['empresas_id']);
                
                $reporte['configuraciones_detalle'][] = [
                    'empresa_id' => $config['empresas_id'],
                    'empresa_nombre' => $config['empresa_nombre'],
                    'apartado_minimo' => $config['apartado_minimo'],
                    'porcentaje_enganche' => $config['porcentaje_enganche'],
                    'tasa_interes_anual' => $config['tasa_interes_anual'],
                    'configuracion_completa' => $empresaCompleta,
                    'ultimo_folio_apartado' => $config['folio_apartado'],
                    'ultimo_folio_venta' => $config['folio_venta']
                ];

                $sumaApartado += $config['apartado_minimo'];
                $sumaEnganche += $config['porcentaje_enganche'];
                $sumaTasa += $config['tasa_interes_anual'];
                
                if ($empresaCompleta) {
                    $completas++;
                }
            }

            if (count($configuraciones) > 0) {
                $reporte['estadisticas']['apartado_minimo_promedio'] = $sumaApartado / count($configuraciones);
                $reporte['estadisticas']['porcentaje_enganche_promedio'] = $sumaEnganche / count($configuraciones);
                $reporte['estadisticas']['tasa_interes_promedio'] = $sumaTasa / count($configuraciones);
                $reporte['estadisticas']['empresas_con_configuracion_completa'] = $completas;
            }

            return $reporte;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar parámetros financieros
     */
    private function validarParametrosFinancieros(array $parametros): array
    {
        $validacion = ['valido' => true, 'errores' => []];

        // Validar apartado mínimo
        if (isset($parametros['apartado_minimo'])) {
            if ($parametros['apartado_minimo'] < 0) {
                $validacion['errores'][] = 'El apartado mínimo no puede ser negativo';
            }
            if ($parametros['apartado_minimo'] > 100000) {
                $validacion['errores'][] = 'El apartado mínimo parece excesivo (>$100,000)';
            }
        }

        // Validar porcentaje de enganche
        if (isset($parametros['porcentaje_enganche'])) {
            if ($parametros['porcentaje_enganche'] < 0 || $parametros['porcentaje_enganche'] > 100) {
                $validacion['errores'][] = 'El porcentaje de enganche debe estar entre 0% y 100%';
            }
        }

        // Validar tasas de interés
        if (isset($parametros['tasa_interes_anual'])) {
            if ($parametros['tasa_interes_anual'] < 0 || $parametros['tasa_interes_anual'] > 100) {
                $validacion['errores'][] = 'La tasa de interés anual debe estar entre 0% y 100%';
            }
        }

        if (isset($parametros['tasa_moratoria_anual'])) {
            if ($parametros['tasa_moratoria_anual'] < 0 || $parametros['tasa_moratoria_anual'] > 100) {
                $validacion['errores'][] = 'La tasa moratoria debe estar entre 0% y 100%';
            }
        }

        $validacion['valido'] = empty($validacion['errores']);

        return $validacion;
    }

    /**
     * Calcular parámetros para apartado
     */
    private function calcularParametrosApartado(array $parametros, array $datos): array
    {
        $monto = $datos['monto'] ?? 0;
        
        return [
            'monto_solicitado' => $monto,
            'cumple_minimo' => $monto >= $parametros['apartado_minimo'],
            'apartado_minimo_requerido' => $parametros['apartado_minimo'],
            'diferencia_minimo' => max(0, $parametros['apartado_minimo'] - $monto),
            'dias_limite' => $parametros['dias_apartado'] ?? 15,
            'fecha_limite' => date('Y-m-d', strtotime('+' . ($parametros['dias_apartado'] ?? 15) . ' days')),
            'comision_generada' => $parametros['comision_apartado']
        ];
    }

    /**
     * Calcular parámetros para enganche
     */
    private function calcularParametrosEnganche(array $parametros, array $datos): array
    {
        $precioTotal = $datos['precio_total'] ?? 0;
        $montoEnganche = ($precioTotal * $parametros['porcentaje_enganche']) / 100;
        
        return [
            'precio_total' => $precioTotal,
            'porcentaje_requerido' => $parametros['porcentaje_enganche'],
            'monto_enganche_calculado' => round($montoEnganche, 2),
            'saldo_financiar' => round($precioTotal - $montoEnganche, 2),
            'dias_liquidacion' => $parametros['dias_enganche'] ?? 30,
            'fecha_limite_liquidacion' => date('Y-m-d', strtotime('+' . ($parametros['dias_enganche'] ?? 30) . ' days'))
        ];
    }

    /**
     * Calcular parámetros para venta a crédito
     */
    private function calcularParametrosVentaCredito(array $parametros, array $datos): array
    {
        $montoFinanciar = $datos['monto_financiar'] ?? 0;
        $plazoMeses = $datos['plazo_meses'] ?? 12;
        
        // Cálculo básico de pago mensual con interés simple
        $tasaMensual = $parametros['tasa_interes_mensual'];
        $pagoMensual = $montoFinanciar * (1 + ($tasaMensual * $plazoMeses / 100)) / $plazoMeses;
        
        return [
            'monto_financiar' => $montoFinanciar,
            'plazo_meses' => $plazoMeses,
            'tasa_anual' => $parametros['tasa_interes_anual'],
            'tasa_mensual' => $tasaMensual,
            'pago_mensual_estimado' => round($pagoMensual, 2),
            'total_intereses_estimado' => round(($pagoMensual * $plazoMeses) - $montoFinanciar, 2),
            'total_a_pagar' => round($pagoMensual * $plazoMeses, 2),
            'aplicar_redondeo' => $parametros['redondear_pagos']
        ];
    }

    /**
     * Calcular parámetros para comisiones
     */
    private function calcularParametrosComisiones(array $parametros, array $datos): array
    {
        $montoVenta = $datos['monto_venta'] ?? 0;
        $comisionTotal = ($montoVenta * $parametros['porcentaje_comision_total']) / 100;
        
        return [
            'monto_venta' => $montoVenta,
            'comision_apartado' => $parametros['comision_apartado'],
            'porcentaje_comision_total' => $parametros['porcentaje_comision_total'],
            'comision_total_calculada' => round($comisionTotal, 2),
            'comision_total_ambas' => round($parametros['comision_apartado'] + $comisionTotal, 2)
        ];
    }

    /**
     * Identificar parámetros críticos
     */
    private function identificarParametrosCriticos($configuracion): array
    {
        $criticos = [];

        if ($configuracion->apartado_minimo > 50000) {
            $criticos[] = 'Apartado mínimo muy alto: $' . number_format($configuracion->apartado_minimo, 2);
        }

        if ($configuracion->porcentaje_enganche > 50) {
            $criticos[] = 'Porcentaje de enganche muy alto: ' . $configuracion->porcentaje_enganche . '%';
        }

        if ($configuracion->tasa_interes_anual > 24) {
            $criticos[] = 'Tasa de interés muy alta: ' . $configuracion->tasa_interes_anual . '%';
        }

        if ($configuracion->dias_apartado < 5) {
            $criticos[] = 'Días de apartado muy cortos: ' . $configuracion->dias_apartado . ' días';
        }

        return $criticos;
    }

    /**
     * Generar recomendaciones
     */
    private function generarRecomendaciones($configuracion): array
    {
        $recomendaciones = [];

        // Recomendaciones basadas en mejores prácticas
        if ($configuracion->apartado_minimo < 1000) {
            $recomendaciones[] = 'Considerar aumentar el apartado mínimo para mejorar el compromiso del cliente';
        }

        if ($configuracion->porcentaje_enganche < 10) {
            $recomendaciones[] = 'Un enganche mayor puede reducir el riesgo crediticio';
        }

        if ($configuracion->tasa_moratoria_anual < $configuracion->tasa_interes_anual) {
            $recomendaciones[] = 'La tasa moratoria debería ser mayor a la tasa de interés regular';
        }

        if (!$configuracion->redondear_pagos_mensuales) {
            $recomendaciones[] = 'Habilitar redondeo de pagos facilita la cobranza';
        }

        return $recomendaciones;
    }

    /**
     * Comparar con estándares de mercado
     */
    private function compararConEstandaresMercado($configuracion): array
    {
        $estandares = [
            'apartado_minimo' => ['min' => 5000, 'max' => 25000, 'promedio' => 10000],
            'porcentaje_enganche' => ['min' => 15, 'max' => 30, 'promedio' => 20],
            'tasa_interes_anual' => ['min' => 8, 'max' => 18, 'promedio' => 12],
            'tasa_moratoria_anual' => ['min' => 18, 'max' => 36, 'promedio' => 24]
        ];

        $comparacion = [];

        foreach ($estandares as $parametro => $rango) {
            $valor = $configuracion->$parametro;
            
            if ($valor < $rango['min']) {
                $comparacion[$parametro] = 'Debajo del promedio de mercado';
            } elseif ($valor > $rango['max']) {
                $comparacion[$parametro] = 'Arriba del promedio de mercado';
            } else {
                $comparacion[$parametro] = 'Dentro del rango de mercado';
            }
        }

        return $comparacion;
    }

    /**
     * Validar parámetros para apartado
     */
    private function validarParametrosApartado($configuracion, array &$validaciones): void
    {
        if ($configuracion->apartado_minimo <= 0) {
            $validaciones['errores'][] = 'Apartado mínimo no configurado correctamente';
        }

        if ($configuracion->dias_apartado <= 0) {
            $validaciones['errores'][] = 'Días de apartado no configurados';
        }

        if ($configuracion->comision_apartado < 0) {
            $validaciones['alertas'][] = 'Comisión de apartado en cero';
        }
    }

    /**
     * Validar parámetros para venta
     */
    private function validarParametrosVenta($configuracion, array &$validaciones): void
    {
        if ($configuracion->porcentaje_enganche <= 0) {
            $validaciones['alertas'][] = 'Porcentaje de enganche en cero puede aumentar riesgo';
        }

        if ($configuracion->tasa_interes_anual <= 0) {
            $validaciones['alertas'][] = 'Tasa de interés en cero';
        }

        if ($configuracion->folio_venta <= 0) {
            $validaciones['errores'][] = 'Folio de venta no inicializado';
        }
    }

    /**
     * Validar parámetros para cobranza
     */
    private function validarParametrosCobranza($configuracion, array &$validaciones): void
    {
        if ($configuracion->tasa_moratoria_anual <= 0) {
            $validaciones['alertas'][] = 'Tasa moratoria en cero puede afectar cobranza';
        }

        if (!$configuracion->permitir_pagos_parciales) {
            $validaciones['alertas'][] = 'Pagos parciales deshabilitados puede afectar recuperación';
        }
    }
}