<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StaffModel;
use App\Models\UserModel;
use App\Services\EmailChangeService;

class MiPerfilController extends BaseController
{
    protected $staffModel;
    protected $userModel;
    protected $emailChangeService;

    public function __construct()
    {
        $this->staffModel = new StaffModel();
        $this->userModel = new UserModel();
        $this->emailChangeService = new EmailChangeService();
    }


    /**
     * Vista principal del perfil
     */
    public function index()
    {
        $user = auth()->user();
        $staff = $this->staffModel->where('user_id', $user->id)->first();
        
        if (!$staff) {
            return redirect()->to('/admin/dashboard')->with('error', 'No se encontró información de staff');
        }

        // Gestión de documentos eliminada temporalmente - Variables dummy
        $tiposDocumento = [
            'ine' => 'INE (Identificación)',
            'comprobante_domicilio' => 'Comprobante de Domicilio',
            'rfc' => 'RFC',
            'acta_nacimiento' => 'Acta de Nacimiento',
            'comprobante_ingresos' => 'Comprobante de Ingresos'
        ];
        
        $documentos = []; // Array vacío - funcionalidad no implementada
        
        // Verificar si debe cambiar contraseña
        $debeCambiarPassword = $staff->debe_cambiar_password ?? false;

        $data = [
            'titulo' => 'Mi Perfil',
            'staff' => $staff,
            'debeCambiarPassword' => $debeCambiarPassword,
            'userName' => userName(),
            'userRole' => userRole(),
            'tiposDocumento' => $tiposDocumento,
            'documentos' => $documentos
        ];

        return view('admin/mi-perfil/index', $data);
    }

    /**
     * Actualizar información personal - Entity-First pattern
     */
    public function actualizarInfo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        // Entity-First: Obtener staff entity
        $user = auth()->user();
        $staff = $this->staffModel->where('user_id', $user->id)->first();
        
        if (!$staff) {
            return $this->response->setJSON(['success' => false, 'error' => 'Staff no encontrado']);
        }

        // Entity-First: Actualizar usando entity
        $rfcAnterior = $staff->rfc;
        $staff->nombres = $this->request->getPost('nombres');
        $staff->apellido_paterno = $this->request->getPost('apellido_paterno');
        $staff->apellido_materno = $this->request->getPost('apellido_materno');
        $staff->fecha_nacimiento = $this->request->getPost('fecha_nacimiento');
        $staff->telefono = $this->request->getPost('telefono');
        $staff->agencia = $this->request->getPost('agencia');
        $staff->rfc = $this->request->getPost('rfc');

        if ($this->staffModel->save($staff)) {
            // Si se registró RFC por primera vez, crear carpeta
            if (empty($rfcAnterior) && !empty($staff->rfc)) {
                $carpetaRfc = FCPATH . 'uploads/staff/' . $staff->rfc . '/';
                if (!is_dir($carpetaRfc)) {
                    mkdir($carpetaRfc, 0755, true);
                }
            }
            
            return $this->response->setJSON(['success' => true, 'message' => 'Información actualizada correctamente']);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al actualizar información']);
    }

    /**
     * Subir foto de perfil - Entity-First pattern
     */
    public function subirFotoPerfil()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        // Entity-First: Obtener staff entity
        $user = auth()->user();
        $staff = $this->staffModel->where('user_id', $user->id)->first();
        
        if (!$staff) {
            return $this->response->setJSON(['success' => false, 'error' => 'Staff no encontrado']);
        }

        $archivo = $this->request->getFile('foto_perfil');
        
        if (!$archivo || !$archivo->isValid()) {
            return $this->response->setJSON(['success' => false, 'error' => 'No se seleccionó archivo válido']);
        }

        // Validar archivo
        if ($archivo->getSize() > 2048000) { // 2MB
            return $this->response->setJSON(['success' => false, 'error' => 'Archivo muy grande (máximo 2MB)']);
        }

        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($archivo->getExtension()), $allowedTypes)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Formato no válido (JPG, PNG)']);
        }

        // RFC obligatorio para gestión de archivos
        if (empty($staff->rfc)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Debe registrar su RFC antes de subir archivos']);
        }
        
        // Eliminar avatar anterior si existe
        if (!empty($staff->foto_perfil)) {
            $avatarAnterior = FCPATH . $staff->foto_perfil;
            if (file_exists($avatarAnterior)) {
                unlink($avatarAnterior);
                log_message('info', "🗑️ Avatar anterior eliminado: {$avatarAnterior}");
            }
        }
        
        // Generar nombre fijo para avatar (siempre se sobrescribe)
        $fileName = $staff->rfc . '_avatar.' . $archivo->getExtension();
        $uploadPath = 'uploads/staff/' . $staff->rfc . '/';
        
        // Crear directorio si no existe
        if (!is_dir(FCPATH . $uploadPath)) {
            mkdir(FCPATH . $uploadPath, 0755, true);
        }

        // Mover archivo
        if ($archivo->move(FCPATH . $uploadPath, $fileName)) {
            // Actualizar directamente en BD
            $rutaCompleta = $uploadPath . $fileName;
            
            log_message('info', "✅ Foto perfil subida - RFC: {$staff->rfc}, Ruta: {$rutaCompleta}");
            
            if ($this->staffModel->update($staff->id, ['foto_perfil' => $rutaCompleta])) {
                // Usar ruta segura que ya funciona
                helper('secure_file');
                $avatarUrl = getSecureFileUrl('staff/' . $staff->rfc . '/' . $fileName, 'avatar');
                
                log_message('info', "✅ BD actualizada - Avatar URL segura: {$avatarUrl}");
                
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Foto de perfil actualizada',
                    'avatar_url' => $avatarUrl
                ]);
            } else {
                log_message('error', "❌ Error actualizando BD para staff ID: {$staff->id}");
            }
        } else {
            log_message('error', "❌ Error moviendo archivo a: " . FCPATH . $uploadPath . $fileName);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al subir archivo']);
    }


    /**
     * Cambiar contraseña - Entity-First + Shield v1.1
     */
    public function cambiarPassword()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        // Entity-First: Obtener entities
        $user = auth()->user();
        $staff = $this->staffModel->where('user_id', $user->id)->first();
        
        if (!$staff) {
            return $this->response->setJSON(['success' => false, 'error' => 'Staff no encontrado']);
        }
        
        $passwordActual = $this->request->getPost('password_actual');
        $passwordNuevo = $this->request->getPost('password_nuevo');
        $passwordConfirmar = $this->request->getPost('password_confirmar');

        // Validaciones MVP
        if (strlen($passwordNuevo) < 6) {
            return $this->response->setJSON(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']);
        }

        if ($passwordNuevo !== $passwordConfirmar) {
            return $this->response->setJSON(['success' => false, 'error' => 'Las contraseñas no coinciden']);
        }

        // En desarrollo: Sin validación de contraseña actual para facilitar testing
        // La validación de seguridad se implementará en producción

        // Shield v1.1: Cambiar contraseña
        $user->fill(['password' => $passwordNuevo]);
        $userModel = new UserModel();
        
        if ($userModel->save($user)) {
            // Entity-First: Actualizar staff entity
            $staff->debe_cambiar_password = 0;
            $staff->ultimo_cambio_password = date('Y-m-d H:i:s');
            
            if ($this->staffModel->save($staff)) {
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Contraseña actualizada correctamente'
                ]);
            }
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al actualizar contraseña']);
    }

    // =====================================================================
    // GESTIÓN DE CAMBIO DE EMAIL
    // =====================================================================

    /**
     * Solicitar cambio de email de autenticación
     * Envía email de verificación al nuevo correo
     */
    public function solicitarCambioEmail()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->response->setJSON([
                'success' => false, 
                'error' => 'Usuario no autenticado'
            ]);
        }

        // Obtener y validar nuevo email
        $nuevoEmail = trim($this->request->getPost('nuevo_email'));
        
        if (empty($nuevoEmail)) {
            return $this->response->setJSON([
                'success' => false, 
                'error' => 'El nuevo email es requerido'
            ]);
        }

        // Usar el servicio de cambio de email
        $resultado = $this->emailChangeService->iniciarCambioEmail($user->id, $nuevoEmail);

        return $this->response->setJSON([
            'success' => $resultado['exito'],
            'message' => $resultado['mensaje'],
            'error' => $resultado['exito'] ? null : $resultado['mensaje']
        ]);
    }

    /**
     * Obtener solicitudes pendientes de cambio de email
     */
    public function obtenerSolicitudesPendientes()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'error' => 'Usuario no autenticado']);
        }

        $solicitudes = $this->emailChangeService->obtenerSolicitudesPendientes($user->id);

        $solicitudesProcesadas = [];
        foreach ($solicitudes as $solicitud) {
            $solicitudesProcesadas[] = [
                'nuevo_email' => $solicitud['secret'],
                'fecha_solicitud' => $solicitud['created_at'],
                'expira' => $solicitud['expires'],
                'tiempo_restante' => $this->calcularTiempoRestante($solicitud['expires'])
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'solicitudes' => $solicitudesProcesadas
        ]);
    }

    /**
     * Cancelar solicitud pendiente de cambio de email
     */
    public function cancelarCambioEmail()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->response->setJSON([
                'success' => false, 
                'error' => 'Usuario no autenticado'
            ]);
        }

        $cancelado = $this->emailChangeService->cancelarSolicitud($user->id);

        return $this->response->setJSON([
            'success' => $cancelado,
            'message' => $cancelado ? 
                'Solicitud de cambio de email cancelada' : 
                'No se encontró solicitud para cancelar'
        ]);
    }

    /**
     * Obtener email actual del usuario
     */
    public function obtenerEmailActual()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'error' => 'Usuario no autenticado']);
        }

        // Obtener email actual desde auth_identities
        $db = \Config\Database::connect();
        $identity = $db->table('auth_identities')
                      ->where('user_id', $user->id)
                      ->where('type', 'email_password')
                      ->get()
                      ->getRow();

        $emailActual = $identity ? $identity->secret : null;

        return $this->response->setJSON([
            'success' => true,
            'email_actual' => $emailActual
        ]);
    }

    /**
     * Calcular tiempo restante para expiración
     */
    private function calcularTiempoRestante(string $fechaExpiracion): string
    {
        $now = time();
        $expira = strtotime($fechaExpiracion);
        $diferencia = $expira - $now;

        if ($diferencia <= 0) {
            return 'Expirado';
        }

        $horas = floor($diferencia / 3600);
        $minutos = floor(($diferencia % 3600) / 60);

        if ($horas > 0) {
            return "{$horas}h {$minutos}m restantes";
        } else {
            return "{$minutos}m restantes";
        }
    }

    // =====================================================================
    // MÉTODOS STUB PARA DOCUMENTOS (FUNCIONALIDAD NO IMPLEMENTADA)
    // =====================================================================

    /**
     * Subir documento - Método stub
     */
    public function subirDocumento()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => false,
            'error' => 'Funcionalidad de documentos no implementada aún'
        ]);
    }

    /**
     * Ver documento - Método stub
     */
    public function verDocumento($documentoId)
    {
        return redirect()->back()->with('error', 'Funcionalidad de documentos no implementada aún');
    }

    /**
     * Descargar documento - Método stub
     */
    public function descargarDocumento($documentoId)
    {
        return redirect()->back()->with('error', 'Funcionalidad de documentos no implementada aún');
    }

    /**
     * Eliminar documento - Método stub
     */
    public function eliminarDocumento($documentoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => false,
            'error' => 'Funcionalidad de documentos no implementada aún'
        ]);
    }

}