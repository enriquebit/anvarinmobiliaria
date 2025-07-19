<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\GoogleDriveService;

class GoogleDriveAuthController extends BaseController
{
    protected $googleDriveService;
    
    public function __construct()
    {
        $this->googleDriveService = new GoogleDriveService();
    }
    
    /**
     * Iniciar proceso de autorización OAuth2
     */
    public function authorize()
    {
        $redirectUri = base_url('admin/google-drive/callback');
        $authUrl = $this->googleDriveService->getAuthorizationUrl($redirectUri);
        
        // Log para debugging
        error_log('[GOOGLE_DRIVE_AUTH] Redirigiendo a autorización: ' . $authUrl);
        
        return redirect()->to($authUrl);
    }
    
    /**
     * Manejar callback de OAuth2
     */
    public function callback()
    {
        $code = $this->request->getGet('code');
        $error = $this->request->getGet('error');
        
        if ($error) {
            error_log('[GOOGLE_DRIVE_AUTH] Error en autorización: ' . $error);
            return redirect()->to('admin/dashboard')->with('error', 'Error al autorizar Google Drive: ' . $error);
        }
        
        if (!$code) {
            error_log('[GOOGLE_DRIVE_AUTH] No se recibió código de autorización');
            return redirect()->to('admin/dashboard')->with('error', 'No se recibió código de autorización');
        }
        
        try {
            $redirectUri = base_url('admin/google-drive/callback');
            $tokens = $this->googleDriveService->exchangeCodeForTokens($code, $redirectUri);
            
            error_log('[GOOGLE_DRIVE_AUTH] Tokens obtenidos exitosamente');
            
            // Verificar que la conexión funciona
            $testResult = $this->testConnection();
            
            if ($testResult['success']) {
                return redirect()->to('admin/dashboard')->with('success', 'Google Drive autorizado exitosamente. Tokens guardados.');
            } else {
                return redirect()->to('admin/dashboard')->with('warning', 'Tokens obtenidos pero hay problemas de conexión: ' . $testResult['error']);
            }
            
        } catch (\Exception $e) {
            error_log('[GOOGLE_DRIVE_AUTH] Error intercambiando tokens: ' . $e->getMessage());
            return redirect()->to('admin/dashboard')->with('error', 'Error obteniendo tokens: ' . $e->getMessage());
        }
    }
    
    /**
     * Probar conexión con Google Drive
     */
    public function testConnection()
    {
        try {
            // Intentar buscar la carpeta raíz
            $carpeta = $this->googleDriveService->buscarCarpeta('ANVAR_Clientes');
            
            $result = [
                'success' => true,
                'message' => 'Conexión exitosa con Google Drive',
                'carpeta_existe' => $carpeta !== null,
                'carpeta_id' => $carpeta['id'] ?? null
            ];
            
            if (ENVIRONMENT === 'development') {
                return $this->response->setJSON($result);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            if (ENVIRONMENT === 'development') {
                return $this->response->setJSON($result);
            }
            
            return $result;
        }
    }
    
    /**
     * Mostrar estado de la autorización
     */
    public function status()
    {
        $tokenFile = WRITEPATH . 'google_drive_tokens.json';
        $hasTokens = file_exists($tokenFile);
        
        $data = [
            'titulo' => 'Estado Google Drive',
            'has_tokens' => $hasTokens,
            'token_file' => $tokenFile
        ];
        
        if ($hasTokens) {
            $tokens = json_decode(file_get_contents($tokenFile), true);
            $data['tokens'] = [
                'created_at' => date('Y-m-d H:i:s', $tokens['created_at'] ?? 0),
                'expires_at' => date('Y-m-d H:i:s', $tokens['expires_at'] ?? 0),
                'is_expired' => time() >= ($tokens['expires_at'] ?? 0)
            ];
        }
        
        return view('admin/google-drive/status', $data);
    }
    
    /**
     * Revocar tokens (logout)
     */
    public function revoke()
    {
        $tokenFile = WRITEPATH . 'google_drive_tokens.json';
        
        if (file_exists($tokenFile)) {
            unlink($tokenFile);
            error_log('[GOOGLE_DRIVE_AUTH] Tokens revocados');
        }
        
        return redirect()->to('admin/dashboard')->with('success', 'Autorización de Google Drive revocada');
    }
}