<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migración para agregar campos faltantes a la tabla empresas existente
 * 
 * Propósito: Ampliar tabla empresas con campos del sistema inmobiliario MVP
 * Contexto: La tabla empresas ya existe con estructura básica
 * 
 * ESTRUCTURA ACTUAL:
 * - id, nombre, activo, created_at, updated_at
 * 
 * CAMPOS A AGREGAR:
 * - Información empresarial (rfc, razon_social, domicilio, etc.)
 * - Configuración de anticipos y comisiones
 * - Configuración de financiamiento
 * 
 * @author Sistema Inmobiliario ANVAR
 * @version 1.0 MVP
 */
class AddCamposToEmpresasTable extends Migration
{
    public function up()
    {
        // =====================================================
        // VERIFICAR QUE LA TABLA EXISTE
        // =====================================================
        if (!$this->db->tableExists('empresas')) {
            throw new \RuntimeException('La tabla empresas no existe. Ejecuta primero CreateEmpresasTable.');
        }

        // =====================================================
        // VERIFICAR QUE LAS COLUMNAS NO EXISTAN YA
        // =====================================================
        $existingColumns = $this->db->getFieldNames('empresas');
        
        // Si la columna 'rfc' ya existe, significa que la migración ya se ejecutó
        if (in_array('rfc', $existingColumns)) {
            log_message('info', 'Las columnas de empresas ya existen - Saltando migración');
            return;
        }

        // =====================================================
        // AGREGAR NUEVOS CAMPOS A LA TABLA EXISTENTE
        // =====================================================
        
        $fields = [
            // ✅ INFORMACIÓN EMPRESARIAL BÁSICA
            'rfc' => [
                'type'       => 'VARCHAR',
                'constraint' => 13,
                'null'       => true,
                'comment'    => 'RFC de la empresa',
                'after'      => 'nombre'
            ],
            'razon_social' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => true,
                'comment'    => 'Razón social de la empresa',
                'after'      => 'rfc'
            ],
            'domicilio' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Dirección completa de la empresa',
                'after'      => 'razon_social'
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => true,
                'comment'    => 'Teléfono principal',
                'after'      => 'domicilio'
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Email corporativo',
                'after'      => 'telefono'
            ],
            'representante' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'comment'    => 'Nombre del representante legal',
                'after'      => 'email'
            ],
            
            // ✅ CONTADOR DE PROYECTOS
            'proyectos' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Contador de proyectos asociados',
                'after'      => 'representante'
            ],
            
            // ✅ CONFIGURACIÓN DE ANTICIPOS
            'tipo_anticipo' => [
                'type'       => 'ENUM',
                'constraint' => ['fijo', 'porcentaje'],
                'default'    => 'porcentaje',
                'comment'    => 'Tipo de anticipo: fijo o porcentaje',
                'after'      => 'proyectos'
            ],
            'porcentaje_anticipo' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'comment'    => 'Porcentaje de anticipo (ej: 12.50 para 12.5%)',
                'after'      => 'tipo_anticipo'
            ],
            'anticipo_fijo' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'comment'    => 'Monto fijo de anticipo en MXN',
                'after'      => 'porcentaje_anticipo'
            ],
            'apartado_minimo' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'comment'    => 'Monto mínimo para apartar en MXN',
                'after'      => 'anticipo_fijo'
            ],
            
            // ✅ CONFIGURACIÓN DE COMISIONES
            'tipo_comision' => [
                'type'       => 'ENUM',
                'constraint' => ['fijo', 'porcentaje'],
                'default'    => 'porcentaje',
                'comment'    => 'Tipo de comisión: fijo o porcentaje',
                'after'      => 'apartado_minimo'
            ],
            'porcentaje_comision' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'comment'    => 'Porcentaje de comisión (ej: 7.50 para 7.5%)',
                'after'      => 'tipo_comision'
            ],
            'comision_fija' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'comment'    => 'Monto fijo de comisión en MXN',
                'after'      => 'porcentaje_comision'
            ],
            'apartado_comision' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'comment'    => 'Comisión por apartado en MXN',
                'after'      => 'comision_fija'
            ],
            
            // ✅ CONFIGURACIÓN DE FINANCIAMIENTO
            'meses_sin_intereses' => [
                'type'       => 'INT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Meses sin intereses disponibles',
                'after'      => 'apartado_comision'
            ],
            'meses_con_intereses' => [
                'type'       => 'INT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Meses con intereses disponibles',
                'after'      => 'meses_sin_intereses'
            ],
            'porcentaje_interes_anual' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'comment'    => 'Porcentaje de interés anual (ej: 12.50 para 12.5%)',
                'after'      => 'meses_con_intereses'
            ],
            'dias_anticipo' => [
                'type'       => 'INT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Días para entregar anticipo',
                'after'      => 'porcentaje_interes_anual'
            ],
            'porcentaje_cancelacion' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 100.00,
                'comment'    => 'Porcentaje de devolución por cancelación (ej: 100.00 para 100%)',
                'after'      => 'dias_anticipo'
            ]
        ];

        // ✅ AGREGAR TODOS LOS CAMPOS
        $this->forge->addColumn('empresas', $fields);

        // =====================================================
        // ACTUALIZAR REGISTRO EXISTENTE CON VALORES DEFAULT
        // =====================================================
        
        // Obtener el registro existente de ANVAR
        $builder = $this->db->table('empresas');
        $anvarEmpresa = $builder->where('nombre', 'ANVAR Inmobiliaria')->get()->getRow();
        
        if ($anvarEmpresa) {
            // Actualizar con valores ejemplo del MVP
            $datosAnvar = [
                'rfc' => 'LDH191210PI0',
                'razon_social' => 'ANVAR',
                'domicilio' => 'AV. CRUZ LIZARRAGA 901 L21-22, PALOS PRIETOS, MAZATLAN, SINALOA,82010',
                'telefono' => '669 238 5285',
                'email' => 'contacto@anvarinmobiliaria.com',
                'representante' => 'LIC. RODOLFO SANDOVAL PELAYO',
                'proyectos' => 0,
                'tipo_anticipo' => 'fijo',
                'porcentaje_anticipo' => 12.00,
                'anticipo_fijo' => 18000.00,
                'apartado_minimo' => 5000.00,
                'tipo_comision' => 'fijo',
                'porcentaje_comision' => 7.00,
                'comision_fija' => 12000.00,
                'apartado_comision' => 0.00,
                'meses_sin_intereses' => 60,
                'meses_con_intereses' => 0,
                'porcentaje_interes_anual' => 0.00,
                'dias_anticipo' => 10,
                'porcentaje_cancelacion' => 100.00,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $builder->where('id', $anvarEmpresa->id)->update($datosAnvar);
            log_message('info', 'Registro ANVAR actualizado con datos del MVP');
        }

        // ✅ Log de confirmación
        log_message('info', 'Migración AddCamposToEmpresasTable ejecutada correctamente - Campos agregados a tabla empresas');
    }

    /**
     * Método down() - Rollback de la migración
     * 
     * @return void
     */
    public function down()
    {
        // ✅ Eliminar campos agregados (en orden inverso para evitar problemas)
        $camposAEliminar = [
            'porcentaje_cancelacion',
            'dias_anticipo',
            'porcentaje_interes_anual',
            'meses_con_intereses',
            'meses_sin_intereses',
            'apartado_comision',
            'comision_fija',
            'porcentaje_comision',
            'tipo_comision',
            'apartado_minimo',
            'anticipo_fijo',
            'porcentaje_anticipo',
            'tipo_anticipo',
            'proyectos',
            'representante',
            'email',
            'telefono',
            'domicilio',
            'razon_social',
            'rfc'
        ];

        $this->forge->dropColumn('empresas', $camposAEliminar);
        
        // ✅ Log de confirmación
        log_message('info', 'Rollback AddCamposToEmpresasTable ejecutado - Campos eliminados de tabla empresas');
    }
}