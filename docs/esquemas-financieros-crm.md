# Esquemas Financieros - Módulo CRM Inmobiliario

## 📌 Resumen Ejecutivo

Este documento define la arquitectura completa del módulo de esquemas financieros para el CRM inmobiliario, incluyendo:
- Esquemas de financiamiento disponibles
- Reglas de negocio para enganches y comisiones
- Diseño técnico del módulo
- Implementación en CodeIgniter/PHP
- Interfaces de configuración dinámica

## 📊 Tabla Completa de Esquemas Financieros

### 1. Esquemas de Financiamiento Base

| **Esquema** | **Enganche/Anticipo** | **Plazo** | **Intereses** | **Primera Mensualidad** | **Observaciones** |
|-------------|----------------------|-----------|---------------|------------------------|------------------|
| **Cero Enganche** | $0 (0%) | 60 meses | 0% | Mismo día de venta | Se ajusta fecha -1 mes |
| **Tradicional** | Variable según terreno | 3-60 meses | 0% | Mes siguiente | Múltiplos de 3 |

### 2. Matriz de Enganches por Tipo de Terreno

| **Tipo de Terreno** | **Superficie** | **Tipo de Enganche** | **Valor/Porcentaje** | **Configurable en** |
|---------------------|----------------|---------------------|---------------------|-------------------|
| Habitacional | ≤ 180 m² | Fijo | $5,000 - $50,000 | `config_enganche.monto_fijo_habitacional` |
| Habitacional | > 180 m² | Porcentaje | 10% - 30% | `config_enganche.porcentaje_habitacional` |
| Comercial | Cualquiera | Porcentaje | 15% - 40% | `config_enganche.porcentaje_comercial` |

### 3. Estructura de Comisiones

| **Esquema** | **Tipo Terreno** | **Superficie** | **Tipo Comisión** | **Cálculo** | **Momento de Pago** |
|-------------|------------------|----------------|-------------------|-------------|---------------------|
| Con Enganche | Habitacional | ≤ 180 m² | Fija | $10,000 | Al recibir enganche |
| Con Enganche | Habitacional | > 180 m² | Porcentaje | 7% del total | Al recibir enganche |
| Con Enganche | Comercial | Cualquiera | Porcentaje | 12% del total | Al recibir enganche |
| Cero Enganche | Cualquiera | Cualquiera | Variable | 2 mensualidades | Mes 1 y 2 |

## 🏗️ Diseño del Módulo: `esquemas_financiamiento`

### Estructura de Tablas

```sql
-- Tabla principal de esquemas
CREATE TABLE esquemas_financiamiento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa_id INT NOT NULL,
    proyecto_id INT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tipo_esquema ENUM('cero_enganche', 'tradicional', 'personalizado') NOT NULL,
    plazo_minimo INT DEFAULT 3,
    plazo_maximo INT DEFAULT 60,
    plazo_default INT DEFAULT 60,
    tasa_interes DECIMAL(5,2) DEFAULT 0.00,
    requiere_enganche BOOLEAN DEFAULT TRUE,
    prioridad INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    INDEX idx_empresa_activo (empresa_id, activo)
);

-- Configuración de enganches
CREATE TABLE config_enganches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    esquema_id INT NOT NULL,
    tipo_terreno ENUM('habitacional', 'comercial', 'industrial') NOT NULL,
    superficie_desde DECIMAL(10,2) DEFAULT 0,
    superficie_hasta DECIMAL(10,2) DEFAULT 99999,
    tipo_calculo ENUM('fijo', 'porcentaje', 'escalonado') NOT NULL,
    monto_fijo DECIMAL(12,2),
    porcentaje DECIMAL(5,2),
    monto_minimo DECIMAL(12,2) DEFAULT 5000.00,
    monto_maximo DECIMAL(12,2),
    descripcion VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (esquema_id) REFERENCES esquemas_financiamiento(id) ON DELETE CASCADE,
    INDEX idx_esquema_tipo (esquema_id, tipo_terreno, activo),
    INDEX idx_superficie (superficie_desde, superficie_hasta)
);

-- Configuración de comisiones
CREATE TABLE config_comisiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    esquema_id INT NOT NULL,
    tipo_terreno ENUM('habitacional', 'comercial', 'industrial') NOT NULL,
    superficie_desde DECIMAL(10,2) DEFAULT 0,
    superficie_hasta DECIMAL(10,2) DEFAULT 99999,
    tipo_calculo ENUM('fijo', 'porcentaje', 'mensualidades', 'escalonado') NOT NULL,
    monto_fijo DECIMAL(12,2),
    porcentaje DECIMAL(5,2),
    num_mensualidades INT DEFAULT 2,
    momento_pago ENUM('enganche', 'mensualidades', 'diferido') DEFAULT 'enganche',
    descripcion VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (esquema_id) REFERENCES esquemas_financiamiento(id) ON DELETE CASCADE,
    INDEX idx_esquema_comision (esquema_id, tipo_terreno, activo)
);

-- Tabla de configuraciones globales
CREATE TABLE configuraciones_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa_id INT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    clave VARCHAR(100) NOT NULL,
    valor TEXT,
    tipo_dato ENUM('string', 'integer', 'decimal', 'boolean', 'json') DEFAULT 'string',
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    UNIQUE KEY uk_empresa_categoria_clave (empresa_id, categoria, clave),
    INDEX idx_categoria (categoria)
);

-- Reglas especiales por esquema
CREATE TABLE esquemas_reglas_especiales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    esquema_id INT NOT NULL,
    regla_tipo VARCHAR(50) NOT NULL,
    condicion_campo VARCHAR(100),
    condicion_operador ENUM('=', '!=', '>', '<', '>=', '<=', 'IN', 'NOT IN', 'BETWEEN'),
    condicion_valor TEXT,
    accion_tipo VARCHAR(50),
    accion_valor TEXT,
    prioridad INT DEFAULT 0,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (esquema_id) REFERENCES esquemas_financiamiento(id) ON DELETE CASCADE,
    INDEX idx_esquema_regla (esquema_id, activo, prioridad)
);

-- Historial de cambios en configuraciones
CREATE TABLE configuraciones_historial (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tabla_origen VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    campo_modificado VARCHAR(100),
    valor_anterior TEXT,
    valor_nuevo TEXT,
    usuario_id INT NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT,
    ip_address VARCHAR(45),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_tabla_registro (tabla_origen, registro_id),
    INDEX idx_fecha (fecha_cambio)
);

-- Datos iniciales de configuración
INSERT INTO configuraciones_sistema (empresa_id, categoria, clave, valor, tipo_dato, descripcion) VALUES
(1, 'VENTAS', 'permitir_multiples_esquemas', 'true', 'boolean', 'Permite seleccionar entre múltiples esquemas de financiamiento'),
(1, 'VENTAS', 'esquema_default_id', '1', 'integer', 'ID del esquema de financiamiento por defecto'),
(1, 'VENTAS', 'validar_enganche_minimo', 'true', 'boolean', 'Valida que el enganche cumpla con el mínimo configurado'),
(1, 'COMISIONES', 'permitir_ajuste_manual', 'true', 'boolean', 'Permite ajustar manualmente las comisiones en ventas'),
(1, 'COMISIONES', 'requiere_autorizacion_ajuste', 'true', 'boolean', 'Requiere autorización para ajustes manuales de comisión'),
(1, 'DOCUMENTOS', 'generar_automatico', 'true', 'boolean', 'Genera documentos automáticamente al confirmar venta');
```

### Implementación del Módulo (CodeIgniter)

```php
// application/models/Configuraciones_model.php
class Configuraciones_model extends CI_Model {
    
    private $historial_activo = true;
    
    public function __construct() {
        parent::__construct();
    }
    
    // Obtener configuración por clave
    public function get_config($empresa_id, $categoria, $clave) {
        $this->db->where([
            'empresa_id' => $empresa_id,
            'categoria' => $categoria,
            'clave' => $clave,
            'activo' => 1
        ]);
        
        $config = $this->db->get('configuraciones_sistema')->row();
        
        if ($config) {
            return $this->cast_valor($config->valor, $config->tipo_dato);
        }
        
        return null;
    }
    
    // Actualizar configuración
    public function set_config($empresa_id, $categoria, $clave, $valor, $usuario_id = null) {
        $config_actual = $this->db->get_where('configuraciones_sistema', [
            'empresa_id' => $empresa_id,
            'categoria' => $categoria,
            'clave' => $clave
        ])->row();
        
        if ($config_actual) {
            // Guardar historial
            if ($this->historial_activo && $config_actual->valor != $valor) {
                $this->guardar_historial(
                    'configuraciones_sistema',
                    $config_actual->id,
                    'valor',
                    $config_actual->valor,
                    $valor,
                    $usuario_id
                );
            }
            
            // Actualizar
            $this->db->update('configuraciones_sistema', [
                'valor' => $valor,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $config_actual->id]);
        }
        
        return $this->db->affected_rows() > 0;
    }
    
    // Cast de valores según tipo
    private function cast_valor($valor, $tipo) {
        switch ($tipo) {
            case 'integer':
                return (int) $valor;
            case 'decimal':
                return (float) $valor;
            case 'boolean':
                return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($valor, true);
            default:
                return $valor;
        }
    }
    
    // Guardar historial de cambios
    private function guardar_historial($tabla, $registro_id, $campo, $valor_anterior, $valor_nuevo, $usuario_id) {
        $this->db->insert('configuraciones_historial', [
            'tabla_origen' => $tabla,
            'registro_id' => $registro_id,
            'campo_modificado' => $campo,
            'valor_anterior' => $valor_anterior,
            'valor_nuevo' => $valor_nuevo,
            'usuario_id' => $usuario_id ?: $this->session->userdata('usuario_id'),
            'ip_address' => $this->input->ip_address()
        ]);
    }
}

// application/models/Esquemas_financiamiento_model.php
class Esquemas_financiamiento_model extends CI_Model {
    
    private $config_model;
    
    public function __construct() {
        parent::__construct();
        $this->load->model('configuraciones_model', 'config_model');
    }
    
    // CRUD de Esquemas
    public function crear_esquema($datos) {
        $this->db->trans_start();
        
        // Insertar esquema principal
        $esquema_data = [
            'empresa_id' => $datos['empresa_id'],
            'proyecto_id' => $datos['proyecto_id'] ?? null,
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'tipo_esquema' => $datos['tipo_esquema'],
            'plazo_minimo' => $datos['plazo_minimo'] ?? 3,
            'plazo_maximo' => $datos['plazo_maximo'] ?? 60,
            'plazo_default' => $datos['plazo_default'] ?? 60,
            'tasa_interes' => $datos['tasa_interes'] ?? 0,
            'requiere_enganche' => $datos['requiere_enganche'] ?? true,
            'prioridad' => $datos['prioridad'] ?? 0,
            'created_by' => $this->session->userdata('usuario_id')
        ];
        
        $this->db->insert('esquemas_financiamiento', $esquema_data);
        $esquema_id = $this->db->insert_id();
        
        // Insertar configuraciones de enganche
        if (isset($datos['config_enganches'])) {
            foreach ($datos['config_enganches'] as $config) {
                $config['esquema_id'] = $esquema_id;
                $this->db->insert('config_enganches', $config);
            }
        }
        
        // Insertar configuraciones de comisiones
        if (isset($datos['config_comisiones'])) {
            foreach ($datos['config_comisiones'] as $config) {
                $config['esquema_id'] = $esquema_id;
                $this->db->insert('config_comisiones', $config);
            }
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status() ? $esquema_id : false;
    }
    
    // Obtener esquemas disponibles
    public function get_esquemas_disponibles($empresa_id, $proyecto_id = null) {
        $this->db->select('ef.*, 
            (SELECT COUNT(*) FROM config_enganches WHERE esquema_id = ef.id AND activo = 1) as num_config_enganches,
            (SELECT COUNT(*) FROM config_comisiones WHERE esquema_id = ef.id AND activo = 1) as num_config_comisiones
        ');
        $this->db->from('esquemas_financiamiento ef');
        $this->db->where('ef.empresa_id', $empresa_id);
        $this->db->where('ef.activo', 1);
        
        if ($proyecto_id) {
            $this->db->where('(ef.proyecto_id IS NULL OR ef.proyecto_id = ' . $proyecto_id . ')');
        }
        
        $this->db->order_by('ef.prioridad DESC, ef.nombre ASC');
        
        return $this->db->get()->result();
    }
    
    // Calcular enganche con múltiples reglas
    public function calcular_enganche($venta_data) {
        $esquema = $this->get_esquema_completo($venta_data['esquema_id']);
        
        if (!$esquema || $esquema->tipo_esquema == 'cero_enganche') {
            return 0;
        }
        
        // Buscar configuración de enganche aplicable
        $this->db->where([
            'esquema_id' => $venta_data['esquema_id'],
            'tipo_terreno' => $venta_data['tipo_terreno'],
            'activo' => 1
        ]);
        $this->db->where('superficie_desde <=', $venta_data['superficie']);
        $this->db->where('superficie_hasta >=', $venta_data['superficie']);
        $this->db->order_by('superficie_desde DESC'); // Más específico primero
        $this->db->limit(1);
        
        $config_enganche = $this->db->get('config_enganches')->row();
        
        if (!$config_enganche) {
            throw new Exception("No se encontró configuración de enganche para los parámetros dados");
        }
        
        $enganche = 0;
        
        switch ($config_enganche->tipo_calculo) {
            case 'fijo':
                $enganche = $config_enganche->monto_fijo;
                break;
                
            case 'porcentaje':
                $enganche = $venta_data['precio_total'] * ($config_enganche->porcentaje / 100);
                break;
                
            case 'escalonado':
                // Implementar lógica escalonada si es necesario
                $enganche = $this->calcular_enganche_escalonado($venta_data, $config_enganche);
                break;
        }
        
        // Aplicar límites
        if ($config_enganche->monto_minimo && $enganche < $config_enganche->monto_minimo) {
            $enganche = $config_enganche->monto_minimo;
        }
        if ($config_enganche->monto_maximo && $enganche > $config_enganche->monto_maximo) {
            $enganche = $config_enganche->monto_maximo;
        }
        
        // Aplicar reglas especiales
        $enganche = $this->aplicar_reglas_especiales($esquema->id, 'enganche', $venta_data, $enganche);
        
        return round($enganche, 2);
    }
    
    // Aplicar reglas especiales
    private function aplicar_reglas_especiales($esquema_id, $tipo_regla, $datos, $valor_base) {
        $this->db->where([
            'esquema_id' => $esquema_id,
            'regla_tipo' => $tipo_regla,
            'activo' => 1
        ]);
        $this->db->order_by('prioridad DESC');
        
        $reglas = $this->db->get('esquemas_reglas_especiales')->result();
        
        $valor_final = $valor_base;
        
        foreach ($reglas as $regla) {
            if ($this->evaluar_condicion($regla, $datos)) {
                $valor_final = $this->aplicar_accion($regla, $valor_final, $datos);
            }
        }
        
        return $valor_final;
    }
    
    // Evaluar condición de regla
    private function evaluar_condicion($regla, $datos) {
        $campo_valor = $datos[$regla->condicion_campo] ?? null;
        $condicion_valor = json_decode($regla->condicion_valor, true) ?: $regla->condicion_valor;
        
        switch ($regla->condicion_operador) {
            case '=':
                return $campo_valor == $condicion_valor;
            case '!=':
                return $campo_valor != $condicion_valor;
            case '>':
                return $campo_valor > $condicion_valor;
            case '<':
                return $campo_valor < $condicion_valor;
            case '>=':
                return $campo_valor >= $condicion_valor;
            case '<=':
                return $campo_valor <= $condicion_valor;
            case 'IN':
                return in_array($campo_valor, (array)$condicion_valor);
            case 'NOT IN':
                return !in_array($campo_valor, (array)$condicion_valor);
            case 'BETWEEN':
                return $campo_valor >= $condicion_valor[0] && $campo_valor <= $condicion_valor[1];
            default:
                return false;
        }
    }
}
```

### Controlador para Gestión Dinámica

```php
// application/controllers/admin/Esquemas_financiamiento.php
class Esquemas_financiamiento extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['esquemas_financiamiento_model', 'configuraciones_model']);
        $this->verificar_permisos();
    }
    
    // Listado de esquemas con DataTables
    public function index() {
        $data['title'] = 'Gestión de Esquemas de Financiamiento';
        $data['puede_crear'] = $this->tiene_permiso('crear_esquema');
        $data['puede_editar'] = $this->tiene_permiso('editar_esquema');
        
        $this->load->view('admin/esquemas/index', $data);
    }
    
    public function obtener_esquemas() {
        $empresa_id = $this->session->userdata('empresa_id');
        $esquemas = $this->esquemas_financiamiento_model->get_esquemas_datatables($empresa_id);
        
        echo json_encode($esquemas);
    }
    
    // Crear nuevo esquema
    public function crear() {
        if (!$this->tiene_permiso('crear_esquema')) {
            show_error('No tiene permisos para esta acción', 403);
        }
        
        if ($this->input->post()) {
            $datos = $this->preparar_datos_esquema();
            
            if ($esquema_id = $this->esquemas_financiamiento_model->crear_esquema($datos)) {
                $this->session->set_flashdata('success', 'Esquema creado exitosamente');
                redirect('admin/esquemas_financiamiento/edit/' . $esquema_id);
            } else {
                $this->session->set_flashdata('error', 'Error al crear el esquema');
            }
        }
        
        $data['title'] = 'Crear Nuevo Esquema';
        $data['tipos_terreno'] = ['habitacional', 'comercial', 'industrial'];
        $data['tipos_calculo'] = ['fijo', 'porcentaje', 'mensualidades', 'escalonado'];
        
        $this->load->view('admin/esquemas/crear', $data);
    }
    
    // Editar esquema existente
    public function edit($id) {
        if (!$this->tiene_permiso('editar_esquema')) {
            show_error('No tiene permisos para esta acción', 403);
        }
        
        $esquema = $this->esquemas_financiamiento_model->get_esquema_completo($id);
        
        if (!$esquema) {
            show_404();
        }
        
        if ($this->input->post()) {
            $datos = $this->preparar_datos_esquema();
            
            if ($this->esquemas_financiamiento_model->actualizar_esquema($id, $datos)) {
                $this->session->set_flashdata('success', 'Esquema actualizado exitosamente');
                redirect('admin/esquemas_financiamiento');
            }
        }
        
        $data['esquema'] = $esquema;
        $data['config_enganches'] = $this->esquemas_financiamiento_model->get_config_enganches($id);
        $data['config_comisiones'] = $this->esquemas_financiamiento_model->get_config_comisiones($id);
        $data['reglas_especiales'] = $this->esquemas_financiamiento_model->get_reglas_especiales($id);
        $data['historial'] = $this->configuraciones_model->get_historial('esquemas_financiamiento', $id);
        
        $this->load->view('admin/esquemas/edit', $data);
    }
    
    // Duplicar esquema
    public function duplicar($id) {
        if (!$this->tiene_permiso('crear_esquema')) {
            show_error('No tiene permisos para esta acción', 403);
        }
        
        $nuevo_id = $this->esquemas_financiamiento_model->duplicar_esquema($id);
        
        if ($nuevo_id) {
            $this->session->set_flashdata('success', 'Esquema duplicado exitosamente');
            redirect('admin/esquemas_financiamiento/edit/' . $nuevo_id);
        } else {
            $this->session->set_flashdata('error', 'Error al duplicar el esquema');
            redirect('admin/esquemas_financiamiento');
        }
    }
    
    // Gestión de configuraciones del sistema
    public function configuraciones() {
        if (!$this->tiene_permiso('configurar_sistema')) {
            show_error('No tiene permisos para esta acción', 403);
        }
        
        if ($this->input->post()) {
            $configs = $this->input->post('config');
            $empresa_id = $this->session->userdata('empresa_id');
            
            foreach ($configs as $categoria => $claves) {
                foreach ($claves as $clave => $valor) {
                    $this->configuraciones_model->set_config(
                        $empresa_id,
                        $categoria,
                        $clave,
                        $valor
                    );
                }
            }
            
            $this->session->set_flashdata('success', 'Configuraciones actualizadas');
            redirect('admin/esquemas_financiamiento/configuraciones');
        }
        
        $empresa_id = $this->session->userdata('empresa_id');
        $data['configuraciones'] = $this->configuraciones_model->get_all_by_empresa($empresa_id);
        $data['categorias'] = $this->configuraciones_model->get_categorias();
        
        $this->load->view('admin/esquemas/configuraciones', $data);
    }
    
    // AJAX: Agregar configuración de enganche
    public function ajax_agregar_config_enganche() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $data = $this->input->post();
        $data['activo'] = 1;
        
        if ($id = $this->db->insert('config_enganches', $data)) {
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al guardar']);
        }
    }
    
    // AJAX: Simulador de esquemas
    public function ajax_simular() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $datos_venta = $this->input->post();
        
        try {
            $enganche = $this->esquemas_financiamiento_model->calcular_enganche($datos_venta);
            $comision = $this->esquemas_financiamiento_model->calcular_comision($datos_venta);
            $tabla_amortizacion = $this->esquemas_financiamiento_model->generar_tabla_amortizacion($datos_venta);
            
            echo json_encode([
                'success' => true,
                'enganche' => $enganche,
                'comision' => $comision,
                'tabla_amortizacion' => $tabla_amortizacion
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // Preparar datos del formulario
    private function preparar_datos_esquema() {
        return [
            'empresa_id' => $this->session->userdata('empresa_id'),
            'nombre' => $this->input->post('nombre'),
            'descripcion' => $this->input->post('descripcion'),
            'tipo_esquema' => $this->input->post('tipo_esquema'),
            'plazo_minimo' => $this->input->post('plazo_minimo'),
            'plazo_maximo' => $this->input->post('plazo_maximo'),
            'plazo_default' => $this->input->post('plazo_default'),
            'tasa_interes' => $this->input->post('tasa_interes'),
            'requiere_enganche' => $this->input->post('requiere_enganche') ? 1 : 0,
            'prioridad' => $this->input->post('prioridad'),
            'config_enganches' => $this->input->post('config_enganches'),
            'config_comisiones' => $this->input->post('config_comisiones')
        ];
    }
    
    // Verificar permisos
    private function tiene_permiso($permiso) {
        // Implementar lógica de permisos
        return true; // Por ahora retorna true
    }
}
```

### Vista de Configuración Dinámica

```html
<!-- application/views/admin/esquemas/index.php -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>Esquemas de Financiamiento
            <?php if($puede_crear): ?>
            <a href="<?= base_url('admin/esquemas_financiamiento/crear') ?>" 
               class="btn btn-primary btn-sm pull-right">
                <i class="fa fa-plus"></i> Nuevo Esquema
            </a>
            <?php endif; ?>
        </h1>
    </section>
    
    <section class="content">
        <div class="box">
            <div class="box-body">
                <table id="tablaEsquemas" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Plazo</th>
                            <th>Requiere Enganche</th>
                            <th>Configuraciones</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#tablaEsquemas').DataTable({
        processing: true,
        serverSide: true,
        ajax: '<?= base_url("admin/esquemas_financiamiento/obtener_esquemas") ?>',
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'tipo_esquema' },
            { data: 'plazo', render: function(data, type, row) {
                return row.plazo_minimo + ' - ' + row.plazo_maximo + ' meses';
            }},
            { data: 'requiere_enganche', render: function(data) {
                return data ? '<span class="label label-success">Sí</span>' : 
                             '<span class="label label-warning">No</span>';
            }},
            { data: 'configuraciones', render: function(data, type, row) {
                return `<small>
                    Enganches: ${row.num_config_enganches}<br>
                    Comisiones: ${row.num_config_comisiones}
                </small>`;
            }},
            { data: 'activo', render: function(data) {
                return data ? '<span class="label label-success">Activo</span>' : 
                             '<span class="label label-danger">Inactivo</span>';
            }},
            { data: 'acciones', orderable: false }
        ]
    });
});
</script>

<!-- application/views/admin/esquemas/editar.php -->
<div class="content-wrapper">
    <section class="content">
        <form id="formEsquema" method="post">
            
            <!-- Información General -->
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Información del Esquema</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre del Esquema</label>
                                <input type="text" name="nombre" class="form-control" 
                                       value="<?= $esquema->nombre ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo de Esquema</label>
                                <select name="tipo_esquema" class="form-control">
                                    <option value="tradicional">Tradicional</option>
                                    <option value="cero_enganche">Cero Enganche</option>
                                    <option value="personalizado">Personalizado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Prioridad</label>
                                <input type="number" name="prioridad" class="form-control" 
                                       value="<?= $esquema->prioridad ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Configuración de Enganches -->
            <div class="box box-success">
                <div class="box-header">
                    <h3 class="box-title">Configuración de Enganches</h3>
                    <button type="button" class="btn btn-sm btn-success pull-right" 
                            onclick="agregarConfigEnganche()">
                        <i class="fa fa-plus"></i> Agregar Configuración
                    </button>
                </div>
                <div class="box-body">
                    <table class="table table-bordered" id="tablaEnganches">
                        <thead>
                            <tr>
                                <th>Tipo Terreno</th>
                                <th>Superficie Desde</th>
                                <th>Superficie Hasta</th>
                                <th>Tipo Cálculo</th>
                                <th>Valor</th>
                                <th>Mínimo</th>
                                <th>Máximo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($config_enganches as $config): ?>
                            <tr data-id="<?= $config->id ?>">
                                <td>
                                    <select name="config_enganches[<?= $config->id ?>][tipo_terreno]" 
                                            class="form-control input-sm">
                                        <option value="habitacional" 
                                            <?= $config->tipo_terreno == 'habitacional' ? 'selected' : '' ?>>
                                            Habitacional
                                        </option>
                                        <option value="comercial"
                                            <?= $config->tipo_terreno == 'comercial' ? 'selected' : '' ?>>
                                            Comercial
                                        </option>
                                        <option value="industrial"
                                            <?= $config->tipo_terreno == 'industrial' ? 'selected' : '' ?>>
                                            Industrial
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="config_enganches[<?= $config->id ?>][superficie_desde]"
                                           class="form-control input-sm" 
                                           value="<?= $config->superficie_desde ?>">
                                </td>
                                <td>
                                    <input type="number" 
                                           name="config_enganches[<?= $config->id ?>][superficie_hasta]"
                                           class="form-control input-sm" 
                                           value="<?= $config->superficie_hasta ?>">
                                </td>
                                <td>
                                    <select name="config_enganches[<?= $config->id ?>][tipo_calculo]" 
                                            class="form-control input-sm tipo-calculo">
                                        <option value="fijo" 
                                            <?= $config->tipo_calculo == 'fijo' ? 'selected' : '' ?>>
                                            Fijo
                                        </option>
                                        <option value="porcentaje"
                                            <?= $config->tipo_calculo == 'porcentaje' ? 'selected' : '' ?>>
                                            Porcentaje
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" 
                                               name="config_enganches[<?= $config->id ?>][valor]"
                                               class="form-control input-sm" 
                                               value="<?= $config->tipo_calculo == 'fijo' ? 
                                                         $config->monto_fijo : $config->porcentaje ?>"
                                               step="0.01">
                                        <span class="input-group-addon">
                                            <?= $config->tipo_calculo == 'fijo' ? '

## 🎯 Ventajas de esta Implementación

1. **Totalmente Dinámico**: Todos los valores son configurables sin tocar código
2. **Multi-empresa**: Cada empresa puede tener sus propias configuraciones
3. **Histórico**: Se mantiene registro de cambios
4. **Validaciones**: Límites mínimos y máximos configurables
5. **Escalable**: Fácil agregar nuevos tipos de esquemas o reglas

## 🔧 Cómo Usar el Módulo

```php
// En el controlador de ventas
$venta_data = [
    'esquema_id' => 1,
    'tipo_terreno' => 'habitacional',
    'superficie' => 150,
    'precio_total' => 340000,
    'plazo_meses' => 60
];

$enganche = $this->esquemas_model->calcular_enganche($venta_data);
$comision = $this->esquemas_model->calcular_comision($venta_data);
```

## 📈 Casos de Uso y Ejemplos Reales

### Ejemplo 1: Venta Tradicional - Terreno Habitacional 144 m²
```php
// Datos de entrada
$venta = [
    'lote' => 'M8-L6',
    'superficie' => 144,
    'precio_m2' => 1100,
    'precio_total' => 158400, // 144 * 1100
    'tipo_terreno' => 'habitacional',
    'esquema' => 'tradicional'
];

// Resultados esperados:
// - Enganche: $10,000 (fijo para ≤180 m²)
// - Comisión: $10,000 (fija)
// - Monto a financiar: $148,400
// - Mensualidad: $2,473.33 (60 meses)
```

### Ejemplo 2: Venta Cero Enganche - Terreno Comercial
```php
// Datos de entrada
$venta = [
    'lote' => 'M12-L20',
    'superficie' => 200,
    'precio_m2' => 1500,
    'precio_total' => 300000,
    'tipo_terreno' => 'comercial',
    'esquema' => 'cero_enganche'
];

// Resultados esperados:
// - Enganche: $0
// - Comisión: $10,000 (2 mensualidades de $5,000)
// - Primera mensualidad: $5,000 (mismo día)
// - Pagos al vendedor: Mes 1 y 2
```

## 🔄 Flujo de Integración con Otros Módulos

### 1. Integración con Módulo de Ventas
```javascript
// Diagrama de flujo
Venta Nueva → Selección Esquema → Cálculo Automático → Generación Documentos
     ↓              ↓                    ↓                      ↓
  Cliente    Config. Dinámica    Enganche/Comisión    Contrato + Tabla
```

### 2. Integración con Cobranza
```php
class Cobranza_controller extends CI_Controller {
    
    public function generar_calendario($venta_id) {
        $venta = $this->ventas_model->get($venta_id);
        $esquema = $this->esquemas_model->get($venta->esquema_id);
        
        // Para cero enganche, primera mensualidad inmediata
        if ($esquema->tipo_esquema == 'cero_enganche') {
            $fecha_inicio = date('Y-m-d'); // Hoy
        } else {
            $fecha_inicio = date('Y-m-d', strtotime('+1 month'));
        }
        
        $this->cobranza_model->crear_calendario([
            'venta_id' => $venta_id,
            'fecha_inicio' => $fecha_inicio,
            'mensualidad' => $venta->mensualidad,
            'plazo' => $venta->plazo_meses
        ]);
    }
}
```

## 🛡️ Validaciones y Reglas de Negocio

### Validaciones Críticas

```php
class Validaciones_esquemas {
    
    public function validar_venta($datos) {
        $errores = [];
        
        // 1. Validar superficie mínima
        if ($datos['superficie'] < 90) {
            $errores[] = 'Superficie mínima: 90 m²';
        }
        
        // 2. Validar enganche mínimo
        if ($datos['esquema'] != 'cero_enganche') {
            $enganche_minimo = $this->calcular_enganche_minimo($datos);
            if ($datos['enganche'] < $enganche_minimo) {
                $errores[] = "Enganche mínimo requerido: $" . number_format($enganche_minimo);
            }
        }
        
        // 3. Validar disponibilidad del lote
        if (!$this->lote_disponible($datos['lote_id'])) {
            $errores[] = 'El lote no está disponible';
        }
        
        // 4. Validar plazo
        if ($datos['plazo'] % 3 != 0 || $datos['plazo'] > 60) {
            $errores[] = 'Plazo debe ser múltiplo de 3, máximo 60 meses';
        }
        
        return $errores;
    }
}
```

## 📊 Reportes y Analytics

### Queries Útiles para Reportes

```sql
-- Reporte de comisiones por vendedor
SELECT 
    v.vendedor_id,
    u.nombre AS vendedor,
    COUNT(v.id) AS ventas_totales,
    SUM(CASE WHEN ef.tipo_esquema = 'cero_enganche' THEN 1 ELSE 0 END) AS ventas_cero_enganche,
    SUM(CASE WHEN ef.tipo_esquema = 'tradicional' THEN 1 ELSE 0 END) AS ventas_tradicional,
    SUM(vc.monto_comision) AS comisiones_totales
FROM ventas v
JOIN usuarios u ON v.vendedor_id = u.id
JOIN esquemas_financiamiento ef ON v.esquema_id = ef.id
JOIN ventas_comisiones vc ON v.id = vc.venta_id
WHERE v.fecha_venta BETWEEN ? AND ?
GROUP BY v.vendedor_id;

-- Análisis de esquemas más utilizados
SELECT 
    ef.nombre AS esquema,
    COUNT(v.id) AS num_ventas,
    AVG(v.precio_total) AS precio_promedio,
    AVG(v.enganche) AS enganche_promedio,
    SUM(v.precio_total) AS volumen_total
FROM ventas v
JOIN esquemas_financiamiento ef ON v.esquema_id = ef.id
WHERE v.status = 'activa'
GROUP BY ef.id
ORDER BY num_ventas DESC;
```

## 🚀 Mejoras Futuras y Roadmap

### Fase 1 (Inmediato)
- [x] Implementar esquemas base (Cero Enganche, Tradicional)
- [x] Configuración dinámica de parámetros
- [ ] Integración con tabla de amortización
- [ ] Generación automática de documentos

### Fase 2 (Corto Plazo)
- [ ] Módulo de reestructuración de créditos
- [ ] Manejo de pagos adicionales post-venta
- [ ] Dashboard de análisis de esquemas

### Fase 3 (Mediano Plazo)
- [ ] Esquemas con intereses variables
- [ ] Simulador de esquemas para clientes
- [ ] Integración con mapas (coordenadas poligonales)
- [ ] Sistema de aprobaciones multinivel

## 🔐 Consideraciones de Seguridad

```php
// Middleware para validar permisos
class Esquemas_middleware {
    
    public function verificar_permiso_modificacion($usuario_id, $accion) {
        $permisos_requeridos = [
            'crear_esquema' => ['admin', 'gerente'],
            'modificar_parametros' => ['admin', 'gerente'],
            'aplicar_esquema_especial' => ['admin', 'gerente', 'supervisor'],
            'ver_reportes' => ['admin', 'gerente', 'supervisor', 'vendedor']
        ];
        
        $rol_usuario = $this->get_rol_usuario($usuario_id);
        
        return in_array($rol_usuario, $permisos_requeridos[$accion] ?? []);
    }
}
```

## 📝 Notas de Implementación

### Consideraciones Especiales para Cero Enganche
1. **Ajuste de fecha**: Restar 1 mes a la fecha de inicio para cobro inmediato
2. **Comisiones**: Se pagan en las 2 primeras mensualidades, no al inicio
3. **Validación**: No permitir si el cliente tiene adeudos previos

### Manejo de Casos Especiales
```php
// Ejemplo: Cliente con acuerdo especial
if ($cliente->tiene_acuerdo_especial) {
    $esquema_personalizado = $this->crear_esquema_temporal([
        'base_esquema_id' => $esquema_standard->id,
        'modificaciones' => [
            'enganche_porcentaje' => $cliente->acuerdo->enganche_especial,
            'plazo_meses' => $cliente->acuerdo->plazo_especial
        ]
    ]);
}
```

## 📞 Endpoints API Sugeridos

```php
// routes.php
$routes->group('api/v1/esquemas', function($routes) {
    $routes->get('/', 'Api\Esquemas::listar');
    $routes->get('(:num)', 'Api\Esquemas::detalle/$1');
    $routes->post('simular', 'Api\Esquemas::simular_venta');
    $routes->get('(:num)/parametros', 'Api\Esquemas::obtener_parametros/$1');
    $routes->put('(:num)/parametros', 'Api\Esquemas::actualizar_parametros/$1');
});
``` : '%' ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="config_enganches[<?= $config->id ?>][monto_minimo]"
                                           class="form-control input-sm" 
                                           value="<?= $config->monto_minimo ?>">
                                </td>
                                <td>
                                    <input type="number" 
                                           name="config_enganches[<?= $config->id ?>][monto_maximo]"
                                           class="form-control input-sm" 
                                           value="<?= $config->monto_maximo ?>">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-danger" 
                                            onclick="eliminarFila(this)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Simulador -->
            <div class="box box-warning collapsed-box">
                <div class="box-header">
                    <h3 class="box-title">Simulador de Esquema</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Tipo Terreno</label>
                            <select id="sim_tipo_terreno" class="form-control">
                                <option value="habitacional">Habitacional</option>
                                <option value="comercial">Comercial</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Superficie (m²)</label>
                            <input type="number" id="sim_superficie" class="form-control" value="150">
                        </div>
                        <div class="col-md-3">
                            <label>Precio Total</label>
                            <input type="number" id="sim_precio" class="form-control" value="300000">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" 
                                    onclick="simularEsquema()">
                                Simular
                            </button>
                        </div>
                    </div>
                    <div id="resultadoSimulacion" style="margin-top: 20px;"></div>
                </div>
            </div>
            
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="<?= base_url('admin/esquemas_financiamiento') ?>" 
                   class="btn btn-default">Cancelar</a>
            </div>
        </form>
    </section>
</div>

<script>
// Funciones JavaScript para manejo dinámico
function agregarConfigEnganche() {
    const tabla = $('#tablaEnganches tbody');
    const nuevoId = 'nuevo_' + Date.now();
    
    const nuevaFila = `
        <tr data-id="${nuevoId}">
            <td>
                <select name="config_enganches[${nuevoId}][tipo_terreno]" class="form-control input-sm">
                    <option value="habitacional">Habitacional</option>
                    <option value="comercial">Comercial</option>
                    <option value="industrial">Industrial</option>
                </select>
            </td>
            <td><input type="number" name="config_enganches[${nuevoId}][superficie_desde]" 
                       class="form-control input-sm" value="0"></td>
            <td><input type="number" name="config_enganches[${nuevoId}][superficie_hasta]" 
                       class="form-control input-sm" value="99999"></td>
            <td>
                <select name="config_enganches[${nuevoId}][tipo_calculo]" 
                        class="form-control input-sm tipo-calculo">
                    <option value="fijo">Fijo</option>
                    <option value="porcentaje">Porcentaje</option>
                </select>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="config_enganches[${nuevoId}][valor]" 
                           class="form-control input-sm" step="0.01">
                    <span class="input-group-addon">$</span>
                </div>
            </td>
            <td><input type="number" name="config_enganches[${nuevoId}][monto_minimo]" 
                       class="form-control input-sm"></td>
            <td><input type="number" name="config_enganches[${nuevoId}][monto_maximo]" 
                       class="form-control input-sm"></td>
            <td>
                <button type="button" class="btn btn-xs btn-danger" onclick="eliminarFila(this)">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    tabla.append(nuevaFila);
}

function simularEsquema() {
    const datos = {
        esquema_id: <?= $esquema->id ?>,
        tipo_terreno: $('#sim_tipo_terreno').val(),
        superficie: $('#sim_superficie').val(),
        precio_total: $('#sim_precio').val(),
        plazo_meses: 60
    };
    
    $.ajax({
        url: '<?= base_url("admin/esquemas_financiamiento/ajax_simular") ?>',
        method: 'POST',
        data: datos,
        success: function(response) {
            const res = JSON.parse(response);
            if (res.success) {
                $('#resultadoSimulacion').html(`
                    <div class="alert alert-info">
                        <h4>Resultados de la Simulación</h4>
                        <p><strong>Enganche:</strong> ${numberFormat(res.enganche)}</p>
                        <p><strong>Comisión Total:</strong> ${numberFormat(res.comision.monto_total)}</p>
                        <p><strong>Tipo de Comisión:</strong> ${res.comision.tipo}</p>
                    </div>
                `);
            }
        }
    });
}

// Cambiar símbolo según tipo de cálculo
$(document).on('change', '.tipo-calculo', function() {
    const simbolo = $(this).val() === 'fijo' ? '

## 🎯 Ventajas de esta Implementación

1. **Totalmente Dinámico**: Todos los valores son configurables sin tocar código
2. **Multi-empresa**: Cada empresa puede tener sus propias configuraciones
3. **Histórico**: Se mantiene registro de cambios
4. **Validaciones**: Límites mínimos y máximos configurables
5. **Escalable**: Fácil agregar nuevos tipos de esquemas o reglas

## 🔧 Cómo Usar el Módulo

```php
// En el controlador de ventas
$venta_data = [
    'esquema_id' => 1,
    'tipo_terreno' => 'habitacional',
    'superficie' => 150,
    'precio_total' => 340000,
    'plazo_meses' => 60
];

$enganche = $this->esquemas_model->calcular_enganche($venta_data);
$comision = $this->esquemas_model->calcular_comision($venta_data);
```

## 📈 Casos de Uso y Ejemplos Reales

### Ejemplo 1: Venta Tradicional - Terreno Habitacional 144 m²
```php
// Datos de entrada
$venta = [
    'lote' => 'M8-L6',
    'superficie' => 144,
    'precio_m2' => 1100,
    'precio_total' => 158400, // 144 * 1100
    'tipo_terreno' => 'habitacional',
    'esquema' => 'tradicional'
];

// Resultados esperados:
// - Enganche: $10,000 (fijo para ≤180 m²)
// - Comisión: $10,000 (fija)
// - Monto a financiar: $148,400
// - Mensualidad: $2,473.33 (60 meses)
```

### Ejemplo 2: Venta Cero Enganche - Terreno Comercial
```php
// Datos de entrada
$venta = [
    'lote' => 'M12-L20',
    'superficie' => 200,
    'precio_m2' => 1500,
    'precio_total' => 300000,
    'tipo_terreno' => 'comercial',
    'esquema' => 'cero_enganche'
];

// Resultados esperados:
// - Enganche: $0
// - Comisión: $10,000 (2 mensualidades de $5,000)
// - Primera mensualidad: $5,000 (mismo día)
// - Pagos al vendedor: Mes 1 y 2
```

## 🔄 Flujo de Integración con Otros Módulos

### 1. Integración con Módulo de Ventas
```javascript
// Diagrama de flujo
Venta Nueva → Selección Esquema → Cálculo Automático → Generación Documentos
     ↓              ↓                    ↓                      ↓
  Cliente    Config. Dinámica    Enganche/Comisión    Contrato + Tabla
```

### 2. Integración con Cobranza
```php
class Cobranza_controller extends CI_Controller {
    
    public function generar_calendario($venta_id) {
        $venta = $this->ventas_model->get($venta_id);
        $esquema = $this->esquemas_model->get($venta->esquema_id);
        
        // Para cero enganche, primera mensualidad inmediata
        if ($esquema->tipo_esquema == 'cero_enganche') {
            $fecha_inicio = date('Y-m-d'); // Hoy
        } else {
            $fecha_inicio = date('Y-m-d', strtotime('+1 month'));
        }
        
        $this->cobranza_model->crear_calendario([
            'venta_id' => $venta_id,
            'fecha_inicio' => $fecha_inicio,
            'mensualidad' => $venta->mensualidad,
            'plazo' => $venta->plazo_meses
        ]);
    }
}
```

## 🛡️ Validaciones y Reglas de Negocio

### Validaciones Críticas

```php
class Validaciones_esquemas {
    
    public function validar_venta($datos) {
        $errores = [];
        
        // 1. Validar superficie mínima
        if ($datos['superficie'] < 90) {
            $errores[] = 'Superficie mínima: 90 m²';
        }
        
        // 2. Validar enganche mínimo
        if ($datos['esquema'] != 'cero_enganche') {
            $enganche_minimo = $this->calcular_enganche_minimo($datos);
            if ($datos['enganche'] < $enganche_minimo) {
                $errores[] = "Enganche mínimo requerido: $" . number_format($enganche_minimo);
            }
        }
        
        // 3. Validar disponibilidad del lote
        if (!$this->lote_disponible($datos['lote_id'])) {
            $errores[] = 'El lote no está disponible';
        }
        
        // 4. Validar plazo
        if ($datos['plazo'] % 3 != 0 || $datos['plazo'] > 60) {
            $errores[] = 'Plazo debe ser múltiplo de 3, máximo 60 meses';
        }
        
        return $errores;
    }
}
```

## 📊 Reportes y Analytics

### Queries Útiles para Reportes

```sql
-- Reporte de comisiones por vendedor
SELECT 
    v.vendedor_id,
    u.nombre AS vendedor,
    COUNT(v.id) AS ventas_totales,
    SUM(CASE WHEN ef.tipo_esquema = 'cero_enganche' THEN 1 ELSE 0 END) AS ventas_cero_enganche,
    SUM(CASE WHEN ef.tipo_esquema = 'tradicional' THEN 1 ELSE 0 END) AS ventas_tradicional,
    SUM(vc.monto_comision) AS comisiones_totales
FROM ventas v
JOIN usuarios u ON v.vendedor_id = u.id
JOIN esquemas_financiamiento ef ON v.esquema_id = ef.id
JOIN ventas_comisiones vc ON v.id = vc.venta_id
WHERE v.fecha_venta BETWEEN ? AND ?
GROUP BY v.vendedor_id;

-- Análisis de esquemas más utilizados
SELECT 
    ef.nombre AS esquema,
    COUNT(v.id) AS num_ventas,
    AVG(v.precio_total) AS precio_promedio,
    AVG(v.enganche) AS enganche_promedio,
    SUM(v.precio_total) AS volumen_total
FROM ventas v
JOIN esquemas_financiamiento ef ON v.esquema_id = ef.id
WHERE v.status = 'activa'
GROUP BY ef.id
ORDER BY num_ventas DESC;
```

## 🚀 Mejoras Futuras y Roadmap

### Fase 1 (Inmediato)
- [x] Implementar esquemas base (Cero Enganche, Tradicional)
- [x] Configuración dinámica de parámetros
- [ ] Integración con tabla de amortización
- [ ] Generación automática de documentos

### Fase 2 (Corto Plazo)
- [ ] Módulo de reestructuración de créditos
- [ ] Manejo de pagos adicionales post-venta
- [ ] Dashboard de análisis de esquemas
- [ ] API REST para integración externa

### Fase 3 (Mediano Plazo)
- [ ] Esquemas con intereses variables
- [ ] Simulador de esquemas para clientes
- [ ] Integración con mapas (coordenadas poligonales)
- [ ] Sistema de aprobaciones multinivel

## 🔐 Consideraciones de Seguridad

```php
// Middleware para validar permisos
class Esquemas_middleware {
    
    public function verificar_permiso_modificacion($usuario_id, $accion) {
        $permisos_requeridos = [
            'crear_esquema' => ['admin', 'gerente'],
            'modificar_parametros' => ['admin', 'gerente'],
            'aplicar_esquema_especial' => ['admin', 'gerente', 'supervisor'],
            'ver_reportes' => ['admin', 'gerente', 'supervisor', 'vendedor']
        ];
        
        $rol_usuario = $this->get_rol_usuario($usuario_id);
        
        return in_array($rol_usuario, $permisos_requeridos[$accion] ?? []);
    }
}
```

## 📝 Notas de Implementación

### Consideraciones Especiales para Cero Enganche
1. **Ajuste de fecha**: Restar 1 mes a la fecha de inicio para cobro inmediato
2. **Comisiones**: Se pagan en las 2 primeras mensualidades, no al inicio
3. **Validación**: No permitir si el cliente tiene adeudos previos

### Manejo de Casos Especiales
```php
// Ejemplo: Cliente con acuerdo especial
if ($cliente->tiene_acuerdo_especial) {
    $esquema_personalizado = $this->crear_esquema_temporal([
        'base_esquema_id' => $esquema_standard->id,
        'modificaciones' => [
            'enganche_porcentaje' => $cliente->acuerdo->enganche_especial,
            'plazo_meses' => $cliente->acuerdo->plazo_especial
        ]
    ]);
}
```

## 📞 Endpoints API Sugeridos

```php
// routes.php
$routes->group('api/v1/esquemas', function($routes) {
    $routes->get('/', 'Api\Esquemas::listar');
    $routes->get('(:num)', 'Api\Esquemas::detalle/$1');
    $routes->post('simular', 'Api\Esquemas::simular_venta');
    $routes->get('(:num)/parametros', 'Api\Esquemas::obtener_parametros/$1');
    $routes->put('(:num)/parametros', 'Api\Esquemas::actualizar_parametros/$1');
});
``` : '%';
    $(this).closest('tr').find('.input-group-addon').text(simbolo);
});
</script>
```

## 🎯 Ventajas de esta Implementación

1. **Totalmente Dinámico**: Todos los valores son configurables sin tocar código
2. **Multi-empresa**: Cada empresa puede tener sus propias configuraciones
3. **Histórico**: Se mantiene registro de cambios
4. **Validaciones**: Límites mínimos y máximos configurables
5. **Escalable**: Fácil agregar nuevos tipos de esquemas o reglas

## 🔧 Cómo Usar el Módulo

```php
// En el controlador de ventas
$venta_data = [
    'esquema_id' => 1,
    'tipo_terreno' => 'habitacional',
    'superficie' => 150,
    'precio_total' => 340000,
    'plazo_meses' => 60
];

$enganche = $this->esquemas_model->calcular_enganche($venta_data);
$comision = $this->esquemas_model->calcular_comision($venta_data);
```

## 📈 Casos de Uso y Ejemplos Reales

### Ejemplo 1: Venta Tradicional - Terreno Habitacional 144 m²
```php
// Datos de entrada
$venta = [
    'lote' => 'M8-L6',
    'superficie' => 144,
    'precio_m2' => 1100,
    'precio_total' => 158400, // 144 * 1100
    'tipo_terreno' => 'habitacional',
    'esquema' => 'tradicional'
];

// Resultados esperados:
// - Enganche: $10,000 (fijo para ≤180 m²)
// - Comisión: $10,000 (fija)
// - Monto a financiar: $148,400
// - Mensualidad: $2,473.33 (60 meses)
```

### Ejemplo 2: Venta Cero Enganche - Terreno Comercial
```php
// Datos de entrada
$venta = [
    'lote' => 'M12-L20',
    'superficie' => 200,
    'precio_m2' => 1500,
    'precio_total' => 300000,
    'tipo_terreno' => 'comercial',
    'esquema' => 'cero_enganche'
];

// Resultados esperados:
// - Enganche: $0
// - Comisión: $10,000 (2 mensualidades de $5,000)
// - Primera mensualidad: $5,000 (mismo día)
// - Pagos al vendedor: Mes 1 y 2
```

## 🔄 Flujo de Integración con Otros Módulos

### 1. Integración con Módulo de Ventas
```javascript
// Diagrama de flujo
Venta Nueva → Selección Esquema → Cálculo Automático → Generación Documentos
     ↓              ↓                    ↓                      ↓
  Cliente    Config. Dinámica    Enganche/Comisión    Contrato + Tabla
```

### 2. Integración con Cobranza
```php
class Cobranza_controller extends CI_Controller {
    
    public function generar_calendario($venta_id) {
        $venta = $this->ventas_model->get($venta_id);
        $esquema = $this->esquemas_model->get($venta->esquema_id);
        
        // Para cero enganche, primera mensualidad inmediata
        if ($esquema->tipo_esquema == 'cero_enganche') {
            $fecha_inicio = date('Y-m-d'); // Hoy
        } else {
            $fecha_inicio = date('Y-m-d', strtotime('+1 month'));
        }
        
        $this->cobranza_model->crear_calendario([
            'venta_id' => $venta_id,
            'fecha_inicio' => $fecha_inicio,
            'mensualidad' => $venta->mensualidad,
            'plazo' => $venta->plazo_meses
        ]);
    }
}
```

## 🛡️ Validaciones y Reglas de Negocio

### Validaciones Críticas

```php
class Validaciones_esquemas {
    
    public function validar_venta($datos) {
        $errores = [];
        
        // 1. Validar superficie mínima
        if ($datos['superficie'] < 90) {
            $errores[] = 'Superficie mínima: 90 m²';
        }
        
        // 2. Validar enganche mínimo
        if ($datos['esquema'] != 'cero_enganche') {
            $enganche_minimo = $this->calcular_enganche_minimo($datos);
            if ($datos['enganche'] < $enganche_minimo) {
                $errores[] = "Enganche mínimo requerido: $" . number_format($enganche_minimo);
            }
        }
        
        // 3. Validar disponibilidad del lote
        if (!$this->lote_disponible($datos['lote_id'])) {
            $errores[] = 'El lote no está disponible';
        }
        
        // 4. Validar plazo
        if ($datos['plazo'] % 3 != 0 || $datos['plazo'] > 60) {
            $errores[] = 'Plazo debe ser múltiplo de 3, máximo 60 meses';
        }
        
        return $errores;
    }
}
```

## 📊 Reportes y Analytics

### Queries Útiles para Reportes

```sql
-- Reporte de comisiones por vendedor
SELECT 
    v.vendedor_id,
    u.nombre AS vendedor,
    COUNT(v.id) AS ventas_totales,
    SUM(CASE WHEN ef.tipo_esquema = 'cero_enganche' THEN 1 ELSE 0 END) AS ventas_cero_enganche,
    SUM(CASE WHEN ef.tipo_esquema = 'tradicional' THEN 1 ELSE 0 END) AS ventas_tradicional,
    SUM(vc.monto_comision) AS comisiones_totales
FROM ventas v
JOIN usuarios u ON v.vendedor_id = u.id
JOIN esquemas_financiamiento ef ON v.esquema_id = ef.id
JOIN ventas_comisiones vc ON v.id = vc.venta_id
WHERE v.fecha_venta BETWEEN ? AND ?
GROUP BY v.vendedor_id;

-- Análisis de esquemas más utilizados
SELECT 
    ef.nombre AS esquema,
    COUNT(v.id) AS num_ventas,
    AVG(v.precio_total) AS precio_promedio,
    AVG(v.enganche) AS enganche_promedio,
    SUM(v.precio_total) AS volumen_total
FROM ventas v
JOIN esquemas_financiamiento ef ON v.esquema_id = ef.id
WHERE v.status = 'activa'
GROUP BY ef.id
ORDER BY num_ventas DESC;
```

## 🚀 Mejoras Futuras y Roadmap

### Fase 1 (Inmediato)
- [x] Implementar esquemas base (Cero Enganche, Tradicional)
- [x] Configuración dinámica de parámetros
- [ ] Integración con tabla de amortización
- [ ] Generación automática de documentos

### Fase 2 (Corto Plazo)
- [ ] Módulo de reestructuración de créditos
- [ ] Manejo de pagos adicionales post-venta
- [ ] Dashboard de análisis de esquemas
- [ ] API REST para integración externa

### Fase 3 (Mediano Plazo)
- [ ] Esquemas con intereses variables
- [ ] Simulador de esquemas para clientes
- [ ] Integración con mapas (coordenadas poligonales)
- [ ] Sistema de aprobaciones multinivel

## 🔐 Consideraciones de Seguridad

```php
// Middleware para validar permisos
class Esquemas_middleware {
    
    public function verificar_permiso_modificacion($usuario_id, $accion) {
        $permisos_requeridos = [
            'crear_esquema' => ['admin', 'gerente'],
            'modificar_parametros' => ['admin', 'gerente'],
            'aplicar_esquema_especial' => ['admin', 'gerente', 'supervisor'],
            'ver_reportes' => ['admin', 'gerente', 'supervisor', 'vendedor']
        ];
        
        $rol_usuario = $this->get_rol_usuario($usuario_id);
        
        return in_array($rol_usuario, $permisos_requeridos[$accion] ?? []);
    }
}
```

## 📝 Notas de Implementación

### Consideraciones Especiales para Cero Enganche
1. **Ajuste de fecha**: Restar 1 mes a la fecha de inicio para cobro inmediato
2. **Comisiones**: Se pagan en las 2 primeras mensualidades, no al inicio
3. **Validación**: No permitir si el cliente tiene adeudos previos

### Manejo de Casos Especiales
```php
// Ejemplo: Cliente con acuerdo especial
if ($cliente->tiene_acuerdo_especial) {
    $esquema_personalizado = $this->crear_esquema_temporal([
        'base_esquema_id' => $esquema_standard->id,
        'modificaciones' => [
            'enganche_porcentaje' => $cliente->acuerdo->enganche_especial,
            'plazo_meses' => $cliente->acuerdo->plazo_especial
        ]
    ]);
}

