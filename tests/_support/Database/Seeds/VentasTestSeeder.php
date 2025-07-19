<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * VentasTestSeeder
 * 
 * Seeder para generar datos de prueba del módulo de ventas
 * Crea un conjunto completo de datos para testing
 */
class VentasTestSeeder extends Seeder
{
    public function run()
    {
        // 1. Crear empresas de prueba
        $empresasData = [
            [
                'razon_social' => 'NuevoAnvar Inmobiliaria S.A. de C.V.',
                'nombre_comercial' => 'NuevoAnvar Test',
                'rfc' => 'NAI123456789',
                'telefono' => '6691234567',
                'email' => 'test@nuevoanvar.com',
                'domicilio' => 'Av. Test #123, Col. Pruebas, Mazatlán, Sin.',
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'razon_social' => 'Desarrollos Test S.A. de C.V.',
                'nombre_comercial' => 'Desarrollos Test',
                'rfc' => 'DTE987654321',
                'telefono' => '6699876543',
                'email' => 'info@desarrollostest.com',
                'domicilio' => 'Blvd. Pruebas #456, Col. Testing, Mazatlán, Sin.',
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('empresas')->insertBatch($empresasData);
        $empresaIds = $this->db->insertID();

        // 2. Crear proyectos de prueba
        $proyectosData = [
            [
                'nombre' => 'Residencial Las Flores Test',
                'empresas_id' => 1,
                'descripcion' => 'Proyecto residencial de prueba con todas las amenidades',
                'ubicacion' => 'Mazatlán, Sinaloa',
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Villas del Mar Test',
                'empresas_id' => 1,
                'descripcion' => 'Desarrollo costero de lujo para testing',
                'ubicacion' => 'Zona Dorada, Mazatlán',
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Fraccionamiento Los Pinos Test',
                'empresas_id' => 2,
                'descripcion' => 'Fraccionamiento familiar de prueba',
                'ubicacion' => 'Periferia de Mazatlán',
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('proyectos')->insertBatch($proyectosData);

        // 3. Crear estados civiles si no existen
        $estadosCiviles = [
            ['nombre' => 'Soltero(a)', 'activo' => true],
            ['nombre' => 'Casado(a)', 'activo' => true],
            ['nombre' => 'Divorciado(a)', 'activo' => true],
            ['nombre' => 'Viudo(a)', 'activo' => true],
            ['nombre' => 'Unión Libre', 'activo' => true]
        ];

        $this->db->table('estados_civiles')->insertBatch($estadosCiviles);

        // 4. Crear fuentes de información si no existen
        $fuentesInfo = [
            ['nombre' => 'Internet', 'activo' => true],
            ['nombre' => 'Recomendación', 'activo' => true],
            ['nombre' => 'Publicidad', 'activo' => true],
            ['nombre' => 'Redes Sociales', 'activo' => true],
            ['nombre' => 'Visita Directa', 'activo' => true]
        ];

        $this->db->table('fuentes_informacion')->insertBatch($fuentesInfo);

        // 5. Crear clientes de prueba
        $clientesData = [
            [
                'nombres' => 'Juan Carlos',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'González',
                'email' => 'juan.perez@test.com',
                'telefono' => '6691234567',
                'rfc' => 'PEGJ800101ABC',
                'curp' => 'PEGJ800101HSLRZN01',
                'fecha_nacimiento' => '1980-01-01',
                'estados_civiles_id' => 2, // Casado
                'fuentes_informacion_id' => 2, // Recomendación
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombres' => 'María Elena',
                'apellido_paterno' => 'Rodríguez',
                'apellido_materno' => 'Martínez',
                'email' => 'maria.rodriguez@test.com',
                'telefono' => '6699876543',
                'rfc' => 'ROMM850315DEF',
                'curp' => 'ROMM850315MSLDRR02',
                'fecha_nacimiento' => '1985-03-15',
                'estados_civiles_id' => 1, // Soltera
                'fuentes_informacion_id' => 1, // Internet
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombres' => 'Roberto',
                'apellido_paterno' => 'Sánchez',
                'apellido_materno' => 'López',
                'email' => 'roberto.sanchez@test.com',
                'telefono' => '6695551234',
                'rfc' => 'SALR750620GHI',
                'curp' => 'SALR750620HSLNPB03',
                'fecha_nacimiento' => '1975-06-20',
                'estados_civiles_id' => 3, // Divorciado
                'fuentes_informacion_id' => 3, // Publicidad
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombres' => 'Ana Cristina',
                'apellido_paterno' => 'Torres',
                'apellido_materno' => 'Herrera',
                'email' => 'ana.torres@test.com',
                'telefono' => '6697778888',
                'rfc' => 'TOHA900910JKL',
                'curp' => 'TOHA900910MSLRRN04',
                'fecha_nacimiento' => '1990-09-10',
                'estados_civiles_id' => 5, // Unión Libre
                'fuentes_informacion_id' => 4, // Redes Sociales
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombres' => 'Luis Fernando',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'Morales',
                'email' => 'luis.garcia@test.com',
                'telefono' => '6692223333',
                'rfc' => 'GAML770405MNO',
                'curp' => 'GAML770405HSLRRS05',
                'fecha_nacimiento' => '1977-04-05',
                'estados_civiles_id' => 2, // Casado
                'fuentes_informacion_id' => 5, // Visita Directa
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('clientes')->insertBatch($clientesData);

        // 6. Crear lotes de prueba
        $lotesData = [
            // Lotes para Residencial Las Flores Test
            [
                'clave' => 'RLF-A01',
                'proyectos_id' => 1,
                'manzana' => 'A',
                'numero' => '01',
                'area' => 150.50,
                'frente' => 10.0,
                'fondo' => 15.0,
                'precio_m2' => 1500.00,
                'precio_total' => 225750.00,
                'tipo' => 'residencial',
                'estado' => 'disponible',
                'disponible' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'clave' => 'RLF-A02',
                'proyectos_id' => 1,
                'manzana' => 'A',
                'numero' => '02',
                'area' => 180.75,
                'frente' => 12.0,
                'fondo' => 15.0,
                'precio_m2' => 1500.00,
                'precio_total' => 271125.00,
                'tipo' => 'residencial',
                'estado' => 'disponible',
                'disponible' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'clave' => 'RLF-B01',
                'proyectos_id' => 1,
                'manzana' => 'B',
                'numero' => '01',
                'area' => 200.00,
                'frente' => 10.0,
                'fondo' => 20.0,
                'precio_m2' => 1600.00,
                'precio_total' => 320000.00,
                'tipo' => 'residencial',
                'estado' => 'disponible',
                'disponible' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            // Lotes para Villas del Mar Test
            [
                'clave' => 'VDM-001',
                'proyectos_id' => 2,
                'manzana' => '1',
                'numero' => '001',
                'area' => 300.00,
                'frente' => 15.0,
                'fondo' => 20.0,
                'precio_m2' => 2500.00,
                'precio_total' => 750000.00,
                'tipo' => 'premium',
                'estado' => 'disponible',
                'disponible' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'clave' => 'VDM-002',
                'proyectos_id' => 2,
                'manzana' => '1',
                'numero' => '002',
                'area' => 250.00,
                'frente' => 12.5,
                'fondo' => 20.0,
                'precio_m2' => 2500.00,
                'precio_total' => 625000.00,
                'tipo' => 'premium',
                'estado' => 'disponible',
                'disponible' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            // Lotes para Fraccionamiento Los Pinos Test
            [
                'clave' => 'FLP-M1-01',
                'proyectos_id' => 3,
                'manzana' => 'M1',
                'numero' => '01',
                'area' => 120.00,
                'frente' => 8.0,
                'fondo' => 15.0,
                'precio_m2' => 800.00,
                'precio_total' => 96000.00,
                'tipo' => 'económico',
                'estado' => 'disponible',
                'disponible' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'clave' => 'FLP-M1-02',
                'proyectos_id' => 3,
                'manzana' => 'M1',
                'numero' => '02',
                'area' => 135.50,
                'frente' => 9.0,
                'fondo' => 15.0,
                'precio_m2' => 800.00,
                'precio_total' => 108400.00,
                'tipo' => 'económico',
                'estado' => 'disponible',
                'disponible' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            // Lote apartado para pruebas
            [
                'clave' => 'TEST-APT-01',
                'proyectos_id' => 1,
                'manzana' => 'TEST',
                'numero' => 'APT01',
                'area' => 175.00,
                'frente' => 10.0,
                'fondo' => 17.5,
                'precio_m2' => 1400.00,
                'precio_total' => 245000.00,
                'tipo' => 'test',
                'estado' => 'apartado',
                'disponible' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('lotes')->insertBatch($lotesData);

        // 7. Crear algunas ventas de prueba
        $ventasData = [
            [
                'folio' => 'VTA-TEST-001',
                'lotes_id' => 8, // Lote apartado
                'clientes_id' => 1, // Juan Carlos
                'empresas_id' => 1,
                'proyectos_id' => 1,
                'total' => 245000.00,
                'anticipo' => 36750.00, // 15%
                'anticipo_pagado' => 20000.00,
                'estado' => 'apartado',
                'es_credito' => false,
                'fecha_venta' => date('Y-m-d'),
                'fecha_limite_apartado' => date('Y-m-d', strtotime('+15 days')),
                'observaciones' => 'Apartado de prueba para testing',
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('ventas')->insertBatch($ventasData);

        // 8. Crear configuraciones de cobranza de prueba
        $configuracionesData = [
            [
                'proyectos_id' => 1,
                'anticipo_minimo_porcentaje' => 15.0,
                'anticipo_minimo_monto' => 30000.00,
                'dias_cancelacion_apartado' => 15,
                'tasa_interes_moratorio' => 24.0,
                'cobrar_interes_moratorio' => true,
                'limite_interes_moratorio' => 50.0,
                'meses_credito_maximo' => 60,
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'proyectos_id' => 2,
                'anticipo_minimo_porcentaje' => 20.0,
                'anticipo_minimo_monto' => 100000.00,
                'dias_cancelacion_apartado' => 30,
                'tasa_interes_moratorio' => 30.0,
                'cobrar_interes_moratorio' => true,
                'limite_interes_moratorio' => 60.0,
                'meses_credito_maximo' => 120,
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'proyectos_id' => 3,
                'anticipo_minimo_porcentaje' => 10.0,
                'anticipo_minimo_monto' => 10000.00,
                'dias_cancelacion_apartado' => 10,
                'tasa_interes_moratorio' => 18.0,
                'cobrar_interes_moratorio' => true,
                'limite_interes_moratorio' => 40.0,
                'meses_credito_maximo' => 36,
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Solo insertar si la tabla existe
        if ($this->db->tableExists('configuracion_cobranza')) {
            $this->db->table('configuracion_cobranza')->insertBatch($configuracionesData);
        }

        // 9. Crear direcciones para algunos clientes
        $direccionesData = [
            [
                'clientes_id' => 1,
                'tipo' => 'casa',
                'calle' => 'Av. Principal',
                'numero_exterior' => '123',
                'numero_interior' => 'A',
                'colonia' => 'Centro',
                'ciudad' => 'Mazatlán',
                'estado' => 'Sinaloa',
                'codigo_postal' => '82000',
                'pais' => 'México',
                'es_principal' => true,
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'clientes_id' => 2,
                'tipo' => 'departamento',
                'calle' => 'Calle Secundaria',
                'numero_exterior' => '456',
                'numero_interior' => '3B',
                'colonia' => 'Zona Dorada',
                'ciudad' => 'Mazatlán',
                'estado' => 'Sinaloa',
                'codigo_postal' => '82110',
                'pais' => 'México',
                'es_principal' => true,
                'activo' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('direcciones_clientes')->insertBatch($direccionesData);

        echo "✅ Datos de prueba para módulo de ventas creados exitosamente:\n";
        echo "   - 2 Empresas\n";
        echo "   - 3 Proyectos\n";
        echo "   - 5 Clientes\n";
        echo "   - 8 Lotes (7 disponibles, 1 apartado)\n";
        echo "   - 1 Venta de prueba\n";
        echo "   - 3 Configuraciones de cobranza\n";
        echo "   - Datos auxiliares (estados civiles, fuentes, direcciones)\n";
    }
}