<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use App\Models\PerfilFinanciamientoModel;
use App\Models\EmpresaModel;
use App\Models\ProyectoModel;

class DebugConfiguracionFinancieraController extends BaseController
{
    protected $configModel;
    protected $empresaModel;
    protected $proyectoModel;

    public function __construct()
    {
        $this->configModel = new PerfilFinanciamientoModel();
        $this->empresaModel = new EmpresaModel();
        $this->proyectoModel = new ProyectoModel();
    }

    /**
     * 🎯 MENÚ PRINCIPAL DE DEBUG CONFIGURACIÓN FINANCIERA
     */
    public function index()
    {
        $this->headerDebug("🔍 DEBUG CONFIGURACIÓN FINANCIERA");
        
        echo "<div class='menu-grid'>";
        echo "<div class='debug-card'>";
        echo "<h3>🔧 Tests Configuración Financiera</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/datos') . "'>1. Ver Datos BD</a></li>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/validaciones') . "'>2. Test Validaciones</a></li>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/post-data') . "'>3. Simular POST Update</a></li>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/clean-data') . "'>4. Test CleanData</a></li>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/model-update') . "'>5. Test Model Update</a></li>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/test-foreign-key') . "'>6. Test Foreign Key</a></li>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/test-filtros-inteligentes') . "'>7. 🎯 Test Filtros Inteligentes</a></li>";
        echo "</ul>";
        echo "</div>";
        echo "</div>";
        
        $this->footerDebug();
    }

    /**
     * 1. Ver datos actuales en BD
     */
    public function datos()
    {
        $this->headerDebug("📊 DATOS CONFIGURACIÓN FINANCIERA");
        
        try {
            $configuraciones = $this->configModel->findAll();
            
            echo "<h3>🏢 Configuraciones en BD (" . count($configuraciones) . ")</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr>";
            echo "<th>ID</th><th>Nombre</th><th>Empresa ID</th><th>Meses S/I</th><th>Meses C/I</th>";
            echo "<th>Promoción 0 Eng</th><th>Mensual Comisión</th><th>Penalización Eng</th><th>Activo</th>";
            echo "</tr>";
            
            foreach ($configuraciones as $config) {
                echo "<tr>";
                echo "<td>{$config->id}</td>";
                echo "<td>{$config->nombre}</td>";
                echo "<td>{$config->empresa_id}</td>";
                echo "<td>{$config->meses_sin_intereses}</td>";
                echo "<td>{$config->meses_con_intereses}</td>";
                echo "<td>" . ($config->promocion_cero_enganche ? 'Sí' : 'No') . "</td>";
                echo "<td>{$config->mensualidades_comision}</td>";
                echo "<td>{$config->penalizacion_enganche_tardio}%</td>";
                echo "<td>" . ($config->activo ? 'Sí' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } catch (\Exception $e) {
            echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        }
        
        $this->footerDebug();
    }

    /**
     * 2. Test validaciones
     */
    public function validaciones()
    {
        $this->headerDebug("✅ TEST VALIDACIONES");
        
        // Test de validación de meses
        $testCases = [
            ['meses_sin_intereses' => 0, 'meses_con_intereses' => 0, 'expected' => false],
            ['meses_sin_intereses' => 12, 'meses_con_intereses' => 0, 'expected' => true],
            ['meses_sin_intereses' => 0, 'meses_con_intereses' => 24, 'expected' => true],
            ['meses_sin_intereses' => 6, 'meses_con_intereses' => 18, 'expected' => true],
        ];
        
        echo "<h3>🔍 Test Validación Meses</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Meses S/I</th><th>Meses C/I</th><th>Total</th><th>Esperado</th><th>Resultado</th></tr>";
        
        foreach ($testCases as $case) {
            $mesesSinIntereses = (int)($case['meses_sin_intereses'] ?? 0);
            $mesesConIntereses = (int)($case['meses_con_intereses'] ?? 0);
            $totalMeses = $mesesSinIntereses + $mesesConIntereses;
            $resultado = $totalMeses > 0;
            $status = $resultado === $case['expected'] ? '✅' : '❌';
            
            echo "<tr>";
            echo "<td>{$mesesSinIntereses}</td>";
            echo "<td>{$mesesConIntereses}</td>";
            echo "<td>{$totalMeses}</td>";
            echo "<td>" . ($case['expected'] ? 'VÁLIDO' : 'INVÁLIDO') . "</td>";
            echo "<td>{$status} " . ($resultado ? 'VÁLIDO' : 'INVÁLIDO') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $this->footerDebug();
    }

    /**
     * 3. Simular POST Update
     */
    public function postData()
    {
        $this->headerDebug("📤 SIMULAR POST UPDATE");
        
        // Simular datos POST típicos
        $simulatedPost = [
            'nombre' => 'Configuración Test Debug',
            'descripcion' => 'Descripción de prueba',
            'empresa_id' => '1',
            'proyecto_id' => '',
            'tipo_anticipo' => 'porcentaje',
            'porcentaje_anticipo' => '15',
            'anticipo_fijo' => '0',
            'apartado_minimo' => '5000',
            'enganche_minimo' => '',
            'plazo_liquidar_enganche' => '10',
            'accion_anticipo_incompleto' => 'liberar_lote',
            'penalizacion_apartado' => '0',
            'penalizacion_enganche_tardio' => '5.5',
            'tipo_comision' => 'porcentaje',
            'porcentaje_comision' => '7',
            'comision_fija' => '0',
            'promocion_cero_enganche' => '1',
            'mensualidades_comision' => '2',
            'meses_sin_intereses' => '12',
            'meses_con_intereses' => '48',
            'porcentaje_interes_anual' => '0',
            'dias_anticipo' => '8',
            'porcentaje_cancelacion' => '100',
            'es_default' => '1',
            'activo' => '1',
            'permite_apartado' => '1',
            'aplica_terreno_habitacional' => '1',
            'aplica_terreno_comercial' => '1',
            'metros_cuadrados_max' => '',
            'prioridad' => '0'
        ];
        
        echo "<h3>📋 Datos POST Simulados</h3>";
        echo "<pre>";
        print_r($simulatedPost);
        echo "</pre>";
        
        // Test validación meses
        $mesesSinIntereses = (int)($simulatedPost['meses_sin_intereses'] ?? 0);
        $mesesConIntereses = (int)($simulatedPost['meses_con_intereses'] ?? 0);
        $totalMeses = $mesesSinIntereses + $mesesConIntereses;
        
        echo "<h3>🔍 Validación Meses</h3>";
        echo "<p><strong>Meses sin intereses:</strong> {$mesesSinIntereses}</p>";
        echo "<p><strong>Meses con intereses:</strong> {$mesesConIntereses}</p>";
        echo "<p><strong>Total meses:</strong> {$totalMeses}</p>";
        echo "<p><strong>Validación:</strong> " . ($totalMeses > 0 ? '✅ VÁLIDO' : '❌ INVÁLIDO') . "</p>";
        
        // Test cleanData
        $cleanedData = $this->cleanData($simulatedPost);
        echo "<h3>🧹 Datos Después de CleanData</h3>";
        echo "<pre>";
        print_r($cleanedData);
        echo "</pre>";
        
        $this->footerDebug();
    }

    /**
     * 4. Test CleanData específico
     */
    public function testCleanData()
    {
        $this->headerDebug("🧹 TEST CLEAN DATA");
        
        $testData = [
            'nombre' => 'Test',
            'promocion_cero_enganche' => '1',
            'mensualidades_comision' => '2',
            'penalizacion_enganche_tardio' => '5.5',
            'meses_sin_intereses' => '12',
            'meses_con_intereses' => '',
            'es_default' => '1',
            'activo' => '',
            'permite_apartado' => '1'
        ];
        
        echo "<h3>📝 Datos ANTES de CleanData</h3>";
        echo "<pre>";
        print_r($testData);
        echo "</pre>";
        
        $cleanedData = $this->cleanData($testData);
        
        echo "<h3>✨ Datos DESPUÉS de CleanData</h3>";
        echo "<pre>";
        print_r($cleanedData);
        echo "</pre>";
        
        $this->footerDebug();
    }

    /**
     * 5. Test Model Update
     */
    public function modelUpdate()
    {
        $this->headerDebug("💾 TEST MODEL UPDATE");
        
        try {
            // Obtener una configuración existente
            $configuracion = $this->configModel->first();
            
            if (!$configuracion) {
                echo "<div class='error'>❌ No hay configuraciones en la BD</div>";
                $this->footerDebug();
                return;
            }
            
            echo "<h3>📊 Configuración Original (ID: {$configuracion->id})</h3>";
            echo "<pre>";
            print_r($configuracion->toArray());
            echo "</pre>";
            
            // Datos de prueba para update
            $testData = [
                'nombre' => 'Actualización Test Debug ' . date('H:i:s'),
                'descripcion' => 'Actualizada desde debug',
                'meses_sin_intereses' => 6,
                'meses_con_intereses' => 54,
                'promocion_cero_enganche' => 1,
                'mensualidades_comision' => 3,
                'penalizacion_enganche_tardio' => 3.5,
                'dias_anticipo' => 15
            ];
            
            echo "<h3>📝 Datos para Update</h3>";
            echo "<pre>";
            print_r($testData);
            echo "</pre>";
            
            // Intentar actualizar
            $result = $this->configModel->update($configuracion->id, $testData);
            
            if ($result) {
                echo "<div class='success'>✅ Update exitoso</div>";
                
                // Verificar los cambios
                $configuracionActualizada = $this->configModel->find($configuracion->id);
                echo "<h3>📊 Configuración Actualizada</h3>";
                echo "<pre>";
                print_r($configuracionActualizada->toArray());
                echo "</pre>";
                
            } else {
                echo "<div class='error'>❌ Update falló</div>";
                $errors = $this->configModel->errors();
                if ($errors) {
                    echo "<h3>❌ Errores del Modelo:</h3>";
                    echo "<pre>";
                    print_r($errors);
                    echo "</pre>";
                }
            }
            
        } catch (\Exception $e) {
            echo "<div class='error'>❌ Excepción: " . $e->getMessage() . "</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        $this->footerDebug();
    }

    /**
     * 6. Test Foreign Key
     */
    public function testForeignKey()
    {
        $this->headerDebug("🔗 TEST FOREIGN KEY");
        
        try {
            // Verificar proyectos existentes
            $proyectos = $this->proyectoModel->findAll();
            echo "<h3>📊 Proyectos Existentes</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Activo</th></tr>";
            foreach ($proyectos as $proyecto) {
                echo "<tr><td>{$proyecto->id}</td><td>{$proyecto->nombre}</td><td>" . ($proyecto->activo ? 'Sí' : 'No') . "</td></tr>";
            }
            echo "</table>";
            
            // Test con proyecto_id NULL
            echo "<h3>✅ Test 1: proyecto_id = NULL</h3>";
            $testData1 = [
                'nombre' => 'Test FK NULL',
                'empresa_id' => 4,
                'proyecto_id' => null,
                'tipo_anticipo' => 'porcentaje',
                'porcentaje_anticipo' => 15,
                'tipo_comision' => 'porcentaje',
                'porcentaje_comision' => 7,
                'meses_con_intereses' => 60
            ];
            
            $cleanedData1 = $this->cleanData($testData1);
            echo "<p><strong>Datos originales:</strong></p>";
            echo "<pre>";
            print_r($testData1);
            echo "</pre>";
            
            echo "<p><strong>Datos después de cleanData:</strong></p>";
            echo "<pre>";
            print_r($cleanedData1);
            echo "</pre>";
            
            // Test con proyecto_id válido
            if (!empty($proyectos)) {
                echo "<h3>✅ Test 2: proyecto_id = " . $proyectos[0]->id . "</h3>";
                $testData2 = [
                    'nombre' => 'Test FK Válido',
                    'empresa_id' => 4,
                    'proyecto_id' => $proyectos[0]->id,
                    'tipo_anticipo' => 'porcentaje',
                    'porcentaje_anticipo' => 15,
                    'tipo_comision' => 'porcentaje',
                    'porcentaje_comision' => 7,
                    'meses_con_intereses' => 60
                ];
                
                $cleanedData2 = $this->cleanData($testData2);
                echo "<p><strong>Datos después de cleanData:</strong></p>";
                echo "<pre>";
                print_r($cleanedData2);
                echo "</pre>";
            }
            
            // Test con proyecto_id inválido
            echo "<h3>❌ Test 3: proyecto_id = 999 (no existe)</h3>";
            $testData3 = [
                'nombre' => 'Test FK Inválido',
                'empresa_id' => 4,
                'proyecto_id' => 999,
                'tipo_anticipo' => 'porcentaje',
                'porcentaje_anticipo' => 15,
                'tipo_comision' => 'porcentaje',
                'porcentaje_comision' => 7,
                'meses_con_intereses' => 60
            ];
            
            $cleanedData3 = $this->cleanData($testData3);
            echo "<p><strong>Datos después de cleanData:</strong></p>";
            echo "<pre>";
            print_r($cleanedData3);
            echo "</pre>";
            
            // Test con proyecto_id vacío
            echo "<h3>✅ Test 4: proyecto_id = '' (vacío)</h3>";
            $testData4 = [
                'nombre' => 'Test FK Vacío',
                'empresa_id' => 4,
                'proyecto_id' => '',
                'tipo_anticipo' => 'porcentaje',
                'porcentaje_anticipo' => 15,
                'tipo_comision' => 'porcentaje',
                'porcentaje_comision' => 7,
                'meses_con_intereses' => 60
            ];
            
            $cleanedData4 = $this->cleanData($testData4);
            echo "<p><strong>Datos después de cleanData:</strong></p>";
            echo "<pre>";
            print_r($cleanedData4);
            echo "</pre>";
            
            echo "<p><strong>Nota:</strong> Los casos con proyecto_id = NULL y proyecto_id = '' deben funcionar correctamente.</p>";
            
        } catch (\Exception $e) {
            echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        }
        
        $this->footerDebug();
    }

    /**
     * 7. Test Filtros Inteligentes
     */
    public function testFiltrosInteligentes()
    {
        $this->headerDebug("🎯 TEST FILTROS INTELIGENTES");
        
        try {
            // Primero mostrar configuraciones disponibles
            echo "<h3>📊 Configuraciones Disponibles</h3>";
            $configuraciones = $this->configModel->where('activo', 1)->findAll();
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr>";
            echo "<th>ID</th><th>Nombre</th><th>T.Hab</th><th>T.Com</th><th>Casa</th><th>Depto</th>";
            echo "<th>Sup.Min</th><th>Sup.Max</th><th>Monto Min</th><th>Monto Max</th>";
            echo "</tr>";
            
            foreach ($configuraciones as $config) {
                echo "<tr>";
                echo "<td>{$config->id}</td>";
                echo "<td>{$config->nombre}</td>";
                echo "<td>" . ($config->aplica_terreno_habitacional ? '✅' : '❌') . "</td>";
                echo "<td>" . ($config->aplica_terreno_comercial ? '✅' : '❌') . "</td>";
                echo "<td>" . ($config->aplica_casa ? '✅' : '❌') . "</td>";
                echo "<td>" . ($config->aplica_departamento ? '✅' : '❌') . "</td>";
                echo "<td>" . ($config->superficie_minima_m2 ?: 'N/A') . "</td>";
                echo "<td>" . ($config->metros_cuadrados_max ?: 'N/A') . "</td>";
                echo "<td>" . ($config->monto_minimo ? '$' . number_format($config->monto_minimo, 0) : 'N/A') . "</td>";
                echo "<td>" . ($config->monto_maximo ? '$' . number_format($config->monto_maximo, 0) : 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            // Test 1: Lote específico reportado por usuario - ID 4
            echo "<h3>🏠 Test 1: Lote ID 4 (200 m², $220,000 - Habitacional)</h3>";
            $configsLote4 = $this->configModel->getConfiguracionesParaLote(4, true);
            echo "<div class='success'>✅ Encontradas: " . count($configsLote4) . " configuraciones</div>";
            
            foreach ($configsLote4 as $config) {
                echo "<p><strong>- {$config->nombre}</strong> (ID: {$config->id})";
                if (isset($config->motivo_compatibilidad)) {
                    echo "<br>&nbsp;&nbsp;<em>Motivo: {$config->motivo_compatibilidad}</em>";
                }
                echo "</p>";
            }

            // Test 1B: Verificar también el lote 14 original
            echo "<h3>🏠 Test 1B: Lote ID 14 (164.85 m², $215,678 - Habitacional)</h3>";
            $configsLote14 = $this->configModel->getConfiguracionesParaLote(14, true);
            echo "<div class='success'>✅ Encontradas: " . count($configsLote14) . " configuraciones</div>";
            
            foreach ($configsLote14 as $config) {
                echo "<p><strong>- {$config->nombre}</strong> (ID: {$config->id})";
                if (isset($config->motivo_compatibilidad)) {
                    echo "<br>&nbsp;&nbsp;<em>Motivo: {$config->motivo_compatibilidad}</em>";
                }
                echo "</p>";
            }

            // Test 2: Lote comercial
            echo "<h3>🏢 Test 2: Lote ID 18 (Comercial)</h3>";
            $configsLote18 = $this->configModel->getConfiguracionesParaLote(18, true);
            echo "<div class='success'>✅ Encontradas: " . count($configsLote18) . " configuraciones</div>";
            
            foreach ($configsLote18 as $config) {
                echo "<p><strong>- {$config->nombre}</strong> (ID: {$config->id})";
                if (isset($config->motivo_compatibilidad)) {
                    echo "<br>&nbsp;&nbsp;<em>Motivo: {$config->motivo_compatibilidad}</em>";
                }
                echo "</p>";
            }

            // Test 3: Filtro manual para terreno habitacional
            echo "<h3>🎯 Test 3: Filtro Manual - Terreno Habitacional (150 m², $200,000)</h3>";
            $criterios = [
                'tipo_terreno' => 'habitacional',
                'area_m2' => 150.0,
                'precio_lote' => 200000.0
            ];
            
            echo "<p><strong>Criterios:</strong></p>";
            echo "<pre>";
            print_r($criterios);
            echo "</pre>";
            
            $configsFiltradas = $this->configModel->getConfiguracionesFiltradas($criterios, true);
            echo "<div class='success'>✅ Encontradas: " . count($configsFiltradas) . " configuraciones</div>";
            
            foreach ($configsFiltradas as $config) {
                echo "<p><strong>- {$config->nombre}</strong> (ID: {$config->id})";
                if (isset($config->motivo_compatibilidad)) {
                    echo "<br>&nbsp;&nbsp;<em>Motivo: {$config->motivo_compatibilidad}</em>";
                }
                echo "</p>";
            }

            // Test 4: Filtro manual para terreno comercial
            echo "<h3>🎯 Test 4: Filtro Manual - Terreno Comercial (200 m², $500,000)</h3>";
            $criteriosComercial = [
                'tipo_terreno' => 'comercial',
                'area_m2' => 200.0,
                'precio_lote' => 500000.0
            ];
            
            echo "<p><strong>Criterios:</strong></p>";
            echo "<pre>";
            print_r($criteriosComercial);
            echo "</pre>";
            
            $configsComercial = $this->configModel->getConfiguracionesFiltradas($criteriosComercial, true);
            echo "<div class='success'>✅ Encontradas: " . count($configsComercial) . " configuraciones</div>";
            
            foreach ($configsComercial as $config) {
                echo "<p><strong>- {$config->nombre}</strong> (ID: {$config->id})";
                if (isset($config->motivo_compatibilidad)) {
                    echo "<br>&nbsp;&nbsp;<em>Motivo: {$config->motivo_compatibilidad}</em>";
                }
                echo "</p>";
            }

            // Test 5: Verificar problema específico del usuario (casa)
            echo "<h3>🏡 Test 5: Verificar Configuración para Casa (solo aplica_casa = 1)</h3>";
            $criteriosCasa = [
                'tipo_terreno' => 'habitacional', // Los lotes habitacionales deberían incluir casas
                'area_m2' => 150.0,
                'precio_lote' => 200000.0
            ];
            
            $configsCasa = $this->configModel->getConfiguracionesFiltradas($criteriosCasa, true);
            echo "<div class='success'>✅ Para terreno habitacional (que incluye casas): " . count($configsCasa) . " configuraciones</div>";
            
            foreach ($configsCasa as $config) {
                $aplicaCasa = $config->aplica_casa ? '✅ Casa' : '❌ Casa';
                $aplicaTerreno = $config->aplica_terreno_habitacional ? '✅ T.Hab' : '❌ T.Hab';
                $aplicaDepto = $config->aplica_departamento ? '✅ Depto' : '❌ Depto';
                
                echo "<p><strong>- {$config->nombre}</strong> (ID: {$config->id})";
                echo "<br>&nbsp;&nbsp;Aplica a: {$aplicaTerreno} | {$aplicaCasa} | {$aplicaDepto}";
                if (isset($config->motivo_compatibilidad)) {
                    echo "<br>&nbsp;&nbsp;<em>Motivo: {$config->motivo_compatibilidad}</em>";
                }
                echo "</p>";
            }

            echo "<div class='debug-card'>";
            echo "<h4>🔍 Análisis del Problema</h4>";
            echo "<p>Si tienes una configuración que aplica ÚNICAMENTE para casas (aplica_casa=1, aplica_terreno_habitacional=0), ";
            echo "debería aparecer cuando se busca un terreno habitacional porque:</p>";
            echo "<ol>";
            echo "<li>Los lotes habitacionales pueden ser para construcción de casas</li>";
            echo "<li>El filtro incluye configuraciones que apliquen a: terreno_habitacional OR casa OR departamento</li>";
            echo "</ol>";
            echo "<p><strong>✅ La configuración 'CERO ENGANCHE' debería aparecer en los resultados porque aplica a casas.</strong></p>";
            echo "</div>";
            
        } catch (\Exception $e) {
            echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        $this->footerDebug();
    }

    /**
     * Función cleanData copiada del controller principal
     */
    private function cleanData(array $data): array
    {
        // Convertir checkboxes a valores booleanos apropiados
        $booleanFields = [
            'es_default', 'activo', 'permite_apartado', 
            'aplica_terreno_habitacional', 'aplica_terreno_comercial', 
            'promocion_cero_enganche'
        ];
        
        foreach ($booleanFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $data[$field] = 0;
            } else {
                $data[$field] = (int)$data[$field];
            }
        }
        
        // Convertir campos numéricos vacíos a valores por defecto
        $numericFields = [
            'porcentaje_anticipo' => 0,
            'anticipo_fijo' => 0,
            'enganche_minimo' => null,
            'apartado_minimo' => 0,
            'porcentaje_comision' => 0,
            'comision_fija' => 0,
            'meses_sin_intereses' => 0,
            'meses_con_intereses' => 0,
            'porcentaje_interes_anual' => 0,
            'dias_anticipo' => 30,
            'plazo_liquidar_enganche' => 10,
            'penalizacion_apartado' => 0,
            'penalizacion_enganche_tardio' => 0,
            'porcentaje_cancelacion' => 100,
            'metros_cuadrados_max' => null,
            'prioridad' => 0,
            'mensualidades_comision' => 2
        ];
        
        foreach ($numericFields as $field => $default) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $data[$field] = $default;
            } else {
                $data[$field] = is_null($default) ? (float)$data[$field] : (float)$data[$field];
            }
        }
        
        // Campos de texto
        $textFields = ['nombre', 'descripcion', 'tipo_anticipo', 'tipo_comision', 'accion_anticipo_incompleto'];
        foreach ($textFields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = '';
            }
        }
        
        return $data;
    }

    /**
     * Header para debug
     */
    private function headerDebug($titulo)
    {
        echo "<!DOCTYPE html><html><head><title>{$titulo}</title>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .debug-card { background: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
            .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
            .error { background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin: 10px 0; }
            .success { background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin: 10px 0; }
            table { margin: 10px 0; }
            th, td { padding: 8px; text-align: left; }
            th { background: #e9ecef; }
            pre { background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; overflow-x: auto; }
        </style></head><body>";
        echo "<h1>{$titulo}</h1>";
        echo "<p><a href='" . base_url('debug/configuracion-financiera') . "'>🔙 Volver al menú</a></p>";
    }

    /**
     * Footer para debug
     */
    private function footerDebug()
    {
        echo "<hr><p><strong>🕐 Generado:</strong> " . date('Y-m-d H:i:s') . "</p>";
        echo "</body></html>";
    }
}