<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use App\Libraries\GoogleDriveService;
use Exception;

class GoogleDriveDebugController extends BaseController
{
    protected $logger;
    private $googleDriveService;

    public function __construct()
    {
        $this->logger = \Config\Services::logger();
        $this->googleDriveService = new GoogleDriveService();
    }

    public function index()
    {
        return view('debug/google_drive_debug');
    }

    public function crearCarpeta()
    {
        $this->debugLog('INICIO - Crear carpeta');
        
        $nombreCarpeta = $this->request->getPost('nombre_carpeta');
        $resultados = [];
        
        if (empty($nombreCarpeta)) {
            $resultados['error'] = 'Nombre de carpeta requerido';
            $this->debugLog('ERROR - Nombre de carpeta vacío');
            return view('debug/google_drive_debug', ['resultados' => $resultados]);
        }

        try {
            $this->debugLog('PROCESANDO - Nombre: ' . $nombreCarpeta);
            
            // Verificar conexión
            $this->debugLog('VERIFICANDO - Conexión con Google Drive');
            $conexionOk = $this->googleDriveService->verificarConexion();
            $resultados['conexion'] = $conexionOk ? 'OK' : 'ERROR';
            
            if (!$conexionOk) {
                throw new Exception('No se pudo conectar con Google Drive');
            }

            // Obtener información del usuario
            $this->debugLog('OBTENIENDO - Info del usuario');
            $infoUsuario = $this->googleDriveService->obtenerInfoUsuario();
            $resultados['usuario'] = $infoUsuario;
            
            // Obtener carpeta raíz
            $this->debugLog('OBTENIENDO - Carpeta raíz ANVAR_Clientes');
            $carpetaRaiz = $this->googleDriveService->obtenerCarpetaRaiz('ANVAR_Clientes');
            $resultados['carpeta_raiz'] = $carpetaRaiz;
            
            // Crear carpeta del cliente
            $this->debugLog('CREANDO - Carpeta cliente: ' . $nombreCarpeta);
            $carpetaCliente = $this->googleDriveService->crearCarpetaCliente($nombreCarpeta);
            $resultados['carpeta_cliente'] = $carpetaCliente;
            
            $resultados['success'] = true;
            $resultados['mensaje'] = 'Carpeta creada exitosamente';
            
            $this->debugLog('ÉXITO - Carpeta creada: ' . $carpetaCliente['id']);
            
        } catch (Exception $e) {
            $resultados['error'] = $e->getMessage();
            $resultados['success'] = false;
            $this->debugLog('ERROR - Excepción: ' . $e->getMessage());
        }

        return view('debug/google_drive_debug', ['resultados' => $resultados]);
    }

    public function testAuth()
    {
        $this->debugLog('INICIO - Test Auth');
        
        try {
            $resultados = [];
            
            // Verificar archivos de configuración
            $this->debugLog('VERIFICANDO - Archivos de configuración');
            $clientSecretPath = ROOTPATH . 'client_secret_825354023030-umglsuragup2medsdf2a5am3q6ddleft.apps.googleusercontent.com.json';
            $tokenPath = WRITEPATH . 'google_drive_tokens.json';
            
            $resultados['client_secret_exists'] = file_exists($clientSecretPath);
            $resultados['token_exists'] = file_exists($tokenPath);
            
            if ($resultados['client_secret_exists']) {
                $clientSecret = json_decode(file_get_contents($clientSecretPath), true);
                $resultados['client_secret_valid'] = isset($clientSecret['web']['client_id']);
            }
            
            if ($resultados['token_exists']) {
                $tokens = json_decode(file_get_contents($tokenPath), true);
                $resultados['token_valid'] = isset($tokens['access_token']);
                $resultados['token_expired'] = isset($tokens['expires_at']) ? time() > $tokens['expires_at'] : true;
            }
            
            // Generar URL de autorización si no hay tokens
            if (!$resultados['token_exists'] || !$resultados['token_valid']) {
                // Usar base_url() para compatibilidad dinámica
                $redirectUri = base_url('debug/google-drive/callback');
                $authUrl = $this->googleDriveService->getAuthorizationUrl($redirectUri);
                $resultados['auth_url'] = $authUrl;
            }
            
            $this->debugLog('RESULTADOS - Auth test completado');
            
        } catch (Exception $e) {
            $resultados['error'] = $e->getMessage();
            $this->debugLog('ERROR - Test Auth: ' . $e->getMessage());
        }

        return view('debug/google_drive_debug', ['resultados' => $resultados]);
    }

    public function callback()
    {
        $this->debugLog('INICIO - OAuth Callback');
        
        $code = $this->request->getGet('code');
        $error = $this->request->getGet('error');
        
        if ($error) {
            $this->debugLog('ERROR - OAuth error: ' . $error);
            return redirect()->to('debug/google-drive')->with('error', 'Error de autorización: ' . $error);
        }
        
        if (!$code) {
            $this->debugLog('ERROR - No se recibió código');
            return redirect()->to('debug/google-drive')->with('error', 'No se recibió el código de autorización');
        }
        
        try {
            $redirectUri = base_url('debug/google-drive/callback');
            $tokens = $this->googleDriveService->exchangeCodeForTokens($code, $redirectUri);
            
            $this->debugLog('ÉXITO - Tokens obtenidos');
            
            return redirect()->to('debug/google-drive')->with('success', 'Autorización exitosa. Tokens guardados.');
            
        } catch (Exception $e) {
            $this->debugLog('ERROR - Callback: ' . $e->getMessage());
            return redirect()->to('debug/google-drive')->with('error', 'Error obteniendo tokens: ' . $e->getMessage());
        }
    }

    private function debugLog($message)
    {
        $logFile = WRITEPATH . 'logs/google_drive_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}