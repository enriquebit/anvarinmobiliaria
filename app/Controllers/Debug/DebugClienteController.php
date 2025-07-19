<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\UserModel;

class DebugClienteController extends BaseController
{
    public function testShow($clienteId = 2)
    {
        echo "<!DOCTYPE html><html><head><title>Debug Cliente Show</title></head><body>";
        echo "<h1>Debug Cliente ID: {$clienteId}</h1>";
        
        try {
            // Test 1: Cliente básico
            echo "<h2>1. Cliente básico (find)</h2>";
            $clienteModel = new ClienteModel();
            $cliente = $clienteModel->find($clienteId);
            
            if ($cliente) {
                echo "<pre>";
                var_dump($cliente);
                echo "</pre>";
                
                // Test 2: getNombreCompleto
                echo "<h2>2. Test getNombreCompleto()</h2>";
                try {
                    $nombre = $cliente->getNombreCompleto();
                    echo "<p>Nombre completo: <strong>{$nombre}</strong></p>";
                } catch (\Exception $e) {
                    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
                }
                
                // Test 3: isActivo
                echo "<h2>3. Test isActivo()</h2>";
                try {
                    $activo = $cliente->isActivo();
                    echo "<p>¿Está activo?: <strong>" . ($activo ? 'SÍ' : 'NO') . "</strong></p>";
                } catch (\Exception $e) {
                    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
                }
                
                // Test 4: created_at
                echo "<h2>4. Test created_at</h2>";
                if ($cliente->created_at) {
                    echo "<p>Created at: " . $cliente->created_at . "</p>";
                    echo "<p>Humanized: " . $cliente->created_at->humanize() . "</p>";
                } else {
                    echo "<p style='color:red'>created_at es NULL</p>";
                }
                
                // Test 5: getClienteCompleto
                echo "<h2>5. Test getClienteCompleto()</h2>";
                $clienteCompleto = $clienteModel->getClienteCompleto($clienteId);
                if ($clienteCompleto) {
                    echo "<p style='color:green'>getClienteCompleto() funciona correctamente</p>";
                } else {
                    echo "<p style='color:red'>getClienteCompleto() retornó NULL</p>";
                }
                
            } else {
                echo "<p style='color:red'>Cliente no encontrado</p>";
            }
            
            // Test 6: UserModel
            echo "<h2>6. Test UserModel</h2>";
            if (isset($cliente->user_id)) {
                $userModel = new UserModel();
                $user = $userModel->find($cliente->user_id);
                if ($user) {
                    echo "<p>Usuario encontrado - ID: {$user->id}, Active: {$user->active}</p>";
                } else {
                    echo "<p style='color:red'>Usuario no encontrado</p>";
                }
            }
            
        } catch (\Exception $e) {
            echo "<h2 style='color:red'>ERROR GENERAL</h2>";
            echo "<pre>" . $e->getMessage() . "</pre>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        echo "</body></html>";
    }
}