<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Cliente57TestDataSeeder extends Seeder
{
    public function run()
    {
        // Datos para el cliente ID 57 (ÚRSULA BALBUENA CORRALES)
        $clienteId = 57;
        
        // Insertar dirección
        $this->db->table('direcciones_clientes')->insert([
            'cliente_id' => $clienteId,
            'domicilio' => 'AV REFORMA 456',
            'numero' => '101',
            'colonia' => 'CENTRO',
            'codigo_postal' => '06000',
            'ciudad' => 'CIUDAD DE MEXICO',
            'estado' => 'CDMX',
            'tiempo_radicando' => '3 años',
            'tipo_residencia' => 'renta',
            'tipo' => 'principal',
            'activo' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Insertar información laboral
        $this->db->table('informacion_laboral_clientes')->insert([
            'cliente_id' => $clienteId,
            'nombre_empresa' => 'CORPORATIVO ANVAR',
            'puesto_cargo' => 'GERENTE DE VENTAS',
            'antiguedad' => '2 años',
            'telefono_trabajo' => '5555123456',
            'direccion_trabajo' => 'TORRE EJECUTIVA PISO 15',
            'salario' => 25000.00,
            'activo' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Insertar referencias
        $referencias = [
            [
                'cliente_id' => $clienteId,
                'numero' => 1,
                'nombre_completo' => 'MARIA FERNANDA LOPEZ',
                'telefono' => '5551234567',
                'parentesco' => 'HERMANA',
                'tipo' => 'referencia_1',
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'cliente_id' => $clienteId,
                'numero' => 2,
                'nombre_completo' => 'CARLOS ANTONIO RUIZ',
                'telefono' => '5559876543',
                'parentesco' => 'AMIGO',
                'tipo' => 'referencia_2',
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($referencias as $referencia) {
            $this->db->table('referencias_clientes')->insert($referencia);
        }

        echo "✅ Datos de prueba insertados para el cliente ID {$clienteId} (ÚRSULA BALBUENA CORRALES)\n";
    }
}