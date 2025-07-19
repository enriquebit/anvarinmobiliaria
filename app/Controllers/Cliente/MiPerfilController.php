<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\DocumentoClienteModel;
use App\Models\UserModel;
use App\Services\UploadService;

class MiPerfilController extends BaseController
{
    protected $clienteModel;
    protected $documentoClienteModel;
    protected $userModel;
    protected $uploadService;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->documentoClienteModel = new DocumentoClienteModel();
        $this->userModel = new UserModel();
        $this->uploadService = new UploadService();
    }

    /**
     * Vista principal del perfil del cliente
     */
    public function index()
    {
        $user = auth()->user();
        $cliente = $this->clienteModel->where('user_id', $user->id)->first();
        
        if (!$cliente) {
            return redirect()->to('/cliente/dashboard')->with('error', 'No se encontró información de cliente');
        }

        // Obtener documentos del cliente (con manejo de error si la tabla no existe)
        try {
            $documentos = $this->documentoClienteModel->getDocumentosByCliente($cliente->id);
            $tiposDocumento = $this->documentoClienteModel->getTiposDocumento();
        } catch (\Exception $e) {
            // Si la tabla no existe, usar valores por defecto
            log_message('warning', 'Tabla documentos_clientes no existe: ' . $e->getMessage());
            $documentos = [];
            $tiposDocumento = [
                'ine' => 'INE/IFE',
                'comprobante_domicilio' => 'Comprobante de Domicilio',
                'comprobante_ingresos' => 'Comprobante de Ingresos',
                'acta_nacimiento' => 'Acta de Nacimiento',
                'curp' => 'CURP',
                'rfc' => 'RFC',
                'estado_cuenta' => 'Estado de Cuenta Bancario',
                'otro' => 'Otro'
            ];
        }

        // Verificar si debe cambiar contraseña usando únicamente force_reset
        $identity = $user->getEmailIdentity();
        $debeCambiarPassword = ($identity && $identity->force_reset);

        $data = [
            'titulo' => 'Mi Perfil',
            'cliente' => $cliente,
            'documentos' => $documentos,
            'tiposDocumento' => $tiposDocumento,
            'debeCambiarPassword' => $debeCambiarPassword,
            'userName' => userName(),
            'userRole' => userRole()
        ];

        return view('cliente/mi-perfil/index', $data);
    }

    /**
     * Actualizar información personal
     */
    public function actualizarInfo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $user = auth()->user();
        $cliente = $this->clienteModel->where('user_id', $user->id)->first();
        
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente no encontrado']);
        }

        $data = [
            'nombres' => $this->request->getPost('nombres'),
            'apellido_paterno' => $this->request->getPost('apellido_paterno'),
            'apellido_materno' => $this->request->getPost('apellido_materno'),
            'telefono' => $this->request->getPost('telefono'),
            'rfc' => $this->request->getPost('rfc'),
            'curp' => $this->request->getPost('curp')
        ];

        if ($this->clienteModel->update($cliente->id, $data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Información actualizada correctamente']);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al actualizar información']);
    }

    /**
     * Subir foto de perfil
     */
    public function subirFotoPerfil()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $user = auth()->user();
        $cliente = $this->clienteModel->where('user_id', $user->id)->first();
        
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente no encontrado']);
        }

        $archivo = $this->request->getFile('foto_perfil');
        
        if (!$archivo || !$archivo->isValid()) {
            return $this->response->setJSON(['success' => false, 'error' => 'No se seleccionó archivo válido']);
        }

        // Usar RFC o ID si no tiene RFC
        $rfc = $cliente->rfc ?: 'CLIENTE_' . $cliente->id;
        
        $resultado = $this->uploadService->subirFotoPerfil($archivo, $rfc, 'clientes');
        
        if ($resultado['success']) {
            // Actualizar ruta en base de datos
            $this->clienteModel->update($cliente->id, ['foto_perfil' => $resultado['ruta']]);
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Foto de perfil actualizada',
                'ruta' => $resultado['ruta']
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => $resultado['error']]);
    }

    /**
     * Subir documento
     */
    public function subirDocumento()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $user = auth()->user();
        $cliente = $this->clienteModel->where('user_id', $user->id)->first();
        
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente no encontrado']);
        }

        $archivo = $this->request->getFile('documento');
        $tipoDocumento = $this->request->getPost('tipo_documento');
        
        if (!$archivo || !$archivo->isValid()) {
            return $this->response->setJSON(['success' => false, 'error' => 'No se seleccionó archivo válido']);
        }

        if (!$tipoDocumento) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tipo de documento requerido']);
        }

        // Usar RFC o ID si no tiene RFC
        $rfc = $cliente->rfc ?: 'CLIENTE_' . $cliente->id;
        
        $resultado = $this->uploadService->subirDocumento($archivo, $rfc, $tipoDocumento, 'clientes');
        
        if ($resultado['success']) {
            // Guardar en base de datos
            $documentoData = [
                'cliente_id' => $cliente->id,
                'tipo_documento' => $tipoDocumento,
                'nombre_archivo' => $resultado['nombre'],
                'ruta_archivo' => $resultado['ruta'],
                'tamano_archivo' => $resultado['tamano'],
                'extension' => $resultado['extension'],
                'mime_type' => $resultado['mime_type'],
                'estado' => 'pendiente'
            ];
            
            if ($this->documentoClienteModel->subirDocumento($documentoData)) {
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Documento subido correctamente'
                ]);
            }
        }

        return $this->response->setJSON(['success' => false, 'error' => $resultado['error'] ?? 'Error al subir documento']);
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $user = auth()->user();
        $cliente = $this->clienteModel->where('user_id', $user->id)->first();
        
        $passwordActual = $this->request->getPost('password_actual');
        $passwordNuevo = $this->request->getPost('password_nuevo');
        $passwordConfirmar = $this->request->getPost('password_confirmar');

        // Validaciones básicas
        if (strlen($passwordNuevo) < 6) {
            return $this->response->setJSON(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']);
        }

        if ($passwordNuevo !== $passwordConfirmar) {
            return $this->response->setJSON(['success' => false, 'error' => 'Las contraseñas no coinciden']);
        }

        // Verificar contraseña actual (solo si no está forzado el cambio)
        $identity = $user->getEmailIdentity();
        $esCambioForzado = ($identity && $identity->force_reset);
        
        if (!$esCambioForzado) {
            if (!auth()->check(['email' => $user->email, 'password' => $passwordActual])) {
                return $this->response->setJSON(['success' => false, 'error' => 'Contraseña actual incorrecta']);
            }
        }

        // Cambiar contraseña usando Shield
        $user->fill(['password' => $passwordNuevo]);
        $userModel = new UserModel();
        
        if ($userModel->save($user)) {
            // ✅ IMPORTANTE: Actualizar force_reset = 0 en auth_identities (flag principal)
            db_connect()->table('auth_identities')
                        ->where('user_id', $user->id)
                        ->where('type', 'email_password')
                        ->update(['force_reset' => 0]);
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Contraseña actualizada correctamente'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al actualizar contraseña']);
    }

    /**
     * Eliminar documento
     */
    public function eliminarDocumento($documentoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $user = auth()->user();
        $cliente = $this->clienteModel->where('user_id', $user->id)->first();
        
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente no encontrado']);
        }

        // Verificar que el documento pertenezca al cliente
        $documento = $this->documentoClienteModel->where('id', $documentoId)
                                                ->where('cliente_id', $cliente->id)
                                                ->first();
        
        if (!$documento) {
            return $this->response->setJSON(['success' => false, 'error' => 'Documento no encontrado']);
        }

        if ($this->documentoClienteModel->eliminarDocumento($documentoId)) {
            // Eliminar archivo físico
            $this->uploadService->eliminarArchivo($documento['ruta_archivo']);
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Documento eliminado correctamente'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al eliminar documento']);
    }
}