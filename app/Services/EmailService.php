<?php

namespace App\Services;

use CodeIgniter\Email\Email;
use Exception;

class EmailService
{
    private $email;

    public function __construct()
    {
        $this->email = \Config\Services::email();
    }

    /**
     * Enviar email de bienvenida con magic link
     */
    public function enviarMagicLinkBienvenida(array $magicLinkData): array
    {
        try {
            // Configurar email
            $this->email->setFrom('noreply@anvar.com.mx', 'ANVAR Inmobiliaria');
            $this->email->setTo($magicLinkData['email_personal']);
            $this->email->setSubject('🎉 Bienvenido a ANVAR Inmobiliaria - Accede a tu portal de cliente');

            // Generar contenido HTML del email
            $htmlContent = $this->generarHtmlMagicLink($magicLinkData);
            $this->email->setMessage($htmlContent);

            // Enviar email
            if ($this->email->send()) {
                log_message('info', "[EMAIL] Magic link enviado exitosamente a: {$magicLinkData['email_personal']}");
                
                return [
                    'success' => true,
                    'message' => 'Email de bienvenida enviado exitosamente',
                    'email_destino' => $magicLinkData['email_personal']
                ];
            } else {
                throw new Exception('Error enviando email: ' . $this->email->printDebugger());
            }

        } catch (Exception $e) {
            log_message('error', "[EMAIL] Error enviando magic link: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'email_destino' => $magicLinkData['email_personal'] ?? 'unknown'
            ];
        }
    }

    /**
     * Generar contenido HTML para el email de magic link
     */
    private function generarHtmlMagicLink(array $data): string
    {
        $logoUrl = base_url('assets/img/anvar-logo.png');
        $magicLinkUrl = $data['magic_link_url'];
        $nombreCompleto = $data['nombre_completo'];
        $desarrollo = $data['desarrollo_interes'];
        $emailCliente = $data['email_cliente'];
        $expiraEn = date('d/m/Y H:i', strtotime($data['expira_en']));

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Bienvenido a ANVAR Inmobiliaria</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f4f4f4;
                }
                .container {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #1a1360;
                }
                .logo {
                    max-width: 200px;
                    height: auto;
                    margin-bottom: 10px;
                }
                .title {
                    color: #1a1360;
                    font-size: 24px;
                    margin: 0;
                }
                .subtitle {
                    color: #666;
                    font-size: 16px;
                    margin: 10px 0 0 0;
                }
                .welcome-section {
                    margin: 30px 0;
                    padding: 20px;
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border-radius: 8px;
                    border-left: 4px solid #07c15b;
                }
                .client-info {
                    background: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                    border: 1px solid #dee2e6;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin: 10px 0;
                    padding: 8px 0;
                    border-bottom: 1px dotted #ddd;
                }
                .info-label {
                    font-weight: bold;
                    color: #1a1360;
                }
                .magic-button {
                    display: block;
                    width: 80%;
                    max-width: 400px;
                    margin: 30px auto;
                    padding: 15px 30px;
                    background: linear-gradient(135deg, #1a1360 0%, #07c15b 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 50px;
                    text-align: center;
                    font-weight: bold;
                    font-size: 18px;
                    transition: transform 0.2s;
                }
                .magic-button:hover {
                    transform: translateY(-2px);
                    color: white;
                    text-decoration: none;
                }
                .warning-box {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                }
                .warning-icon {
                    color: #856404;
                    font-size: 18px;
                    margin-right: 10px;
                }
                .footer {
                    text-align: center;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #dee2e6;
                    color: #666;
                    font-size: 14px;
                }
                .contact-info {
                    margin: 20px 0;
                    text-align: center;
                }
                .contact-item {
                    margin: 5px 0;
                }
                @media (max-width: 600px) {
                    body { padding: 10px; }
                    .container { padding: 20px; }
                    .info-row { flex-direction: column; }
                    .magic-button { width: 95%; font-size: 16px; padding: 12px 20px; }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='{$logoUrl}' alt='ANVAR Inmobiliaria' class='logo' />
                    <h1 class='title'>¡Bienvenido a ANVAR!</h1>
                    <p class='subtitle'>Tu portal de cliente está listo</p>
                </div>
                
                <div class='welcome-section'>
                    <h2 style='color: #1a1360; margin-top: 0;'>¡Hola {$nombreCompleto}! 👋</h2>
                    <p>Nos da mucho gusto tenerte como parte de la familia ANVAR. Hemos creado tu cuenta de cliente para que puedas:</p>
                    <ul style='color: #495057;'>
                        <li>📋 Ver el estado de tu registro</li>
                        <li>📄 Subir y gestionar tus documentos</li>
                        <li>🏠 Consultar información de tu desarrollo de interés</li>
                        <li>📞 Contactar a tu agente asignado</li>
                        <li>📈 Seguir el progreso de tu proceso</li>
                    </ul>
                </div>

                <div class='client-info'>
                    <h3 style='color: #1a1360; margin-top: 0;'>Información de tu cuenta:</h3>
                    <div class='info-row'>
                        <span class='info-label'>Email de cliente:</span>
                        <span>{$emailCliente}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>Desarrollo de interés:</span>
                        <span>{$desarrollo}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>Tipo de identificador:</span>
                        <span>{$data['tipo_identificador']}</span>
                    </div>
                </div>

                <a href='{$magicLinkUrl}' class='magic-button'>
                    🔐 Acceder a mi portal de cliente
                </a>

                <div class='warning-box'>
                    <span class='warning-icon'>⚠️</span>
                    <strong>Importante:</strong> Este enlace de acceso es válido hasta el <strong>{$expiraEn}</strong>. 
                    Por seguridad, expirará automáticamente después de esta fecha.
                </div>

                <div class='contact-info'>
                    <h4 style='color: #1a1360;'>¿Necesitas ayuda?</h4>
                    <div class='contact-item'>📞 <strong>Teléfono:</strong> +52 (xxx) xxx-xxxx</div>
                    <div class='contact-item'>📧 <strong>Email:</strong> soporte@anvar.com.mx</div>
                    <div class='contact-item'>💬 <strong>WhatsApp:</strong> +52 (xxx) xxx-xxxx</div>
                </div>

                <div class='footer'>
                    <p><strong>ANVAR Inmobiliaria</strong></p>
                    <p>Construyendo tu futuro, creando hogares</p>
                    <p style='font-size: 12px; color: #999;'>
                        Este es un email automático, por favor no responder. 
                        Si tienes dudas, contacta a tu agente asignado.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Enviar email de notificación al equipo sobre nuevo registro
     */
    public function enviarNotificacionEquipo(array $datosCliente, array $credenciales): array
    {
        try {
            $nombreCompleto = trim($datosCliente['firstname'] . ' ' . $datosCliente['lastname']);
            $desarrollo = $datosCliente['desarrollo'] === 'valle_natura' ? 'Valle Natura' : 'Cordelia';
            
            // Emails del equipo (configurables)
            $equipoEmails = [
                'admin@anvar.com.mx',
                'ventas@anvar.com.mx'
            ];
            
            if (!empty($datosCliente['agente_referido'])) {
                // Aquí se podría buscar el email del agente específico
                $equipoEmails[] = 'agente@anvar.com.mx';
            }

            $this->email->setFrom('sistema@anvar.com.mx', 'Sistema ANVAR');
            $this->email->setTo($equipoEmails);
            $this->email->setSubject("🆕 Nuevo cliente registrado: {$nombreCompleto}");

            $htmlContent = $this->generarHtmlNotificacionEquipo($datosCliente, $credenciales);
            $this->email->setMessage($htmlContent);

            if ($this->email->send()) {
                log_message('info', "[EMAIL] Notificación al equipo enviada para: {$nombreCompleto}");
                
                return [
                    'success' => true,
                    'message' => 'Notificación enviada al equipo',
                    'emails_destino' => $equipoEmails
                ];
            } else {
                throw new Exception('Error enviando notificación: ' . $this->email->printDebugger());
            }

        } catch (Exception $e) {
            log_message('error', "[EMAIL] Error enviando notificación al equipo: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar contenido HTML para notificación al equipo
     */
    private function generarHtmlNotificacionEquipo(array $datosCliente, array $credenciales): string
    {
        $nombreCompleto = trim($datosCliente['firstname'] . ' ' . $datosCliente['lastname']);
        $desarrollo = $datosCliente['desarrollo'] === 'valle_natura' ? 'Valle Natura' : 'Cordelia';
        
        return "
        <h2>🆕 Nuevo Cliente Registrado</h2>
        
        <h3>Información del Cliente:</h3>
        <ul>
            <li><strong>Nombre:</strong> {$nombreCompleto}</li>
            <li><strong>Email personal:</strong> {$datosCliente['email']}</li>
            <li><strong>Teléfono:</strong> {$datosCliente['telefono']}</li>
            <li><strong>Desarrollo de interés:</strong> {$desarrollo}</li>
            <li><strong>Agente referido:</strong> " . ($datosCliente['agente_referido'] ?? 'No especificado') . "</li>
        </ul>
        
        <h3>Credenciales Generadas:</h3>
        <ul>
            <li><strong>Email de cliente:</strong> {$credenciales['email_cliente']}</li>
            <li><strong>Identificador:</strong> {$credenciales['identificador']} ({$credenciales['tipo_identificador']})</li>
            <li><strong>Magic link expira:</strong> {$credenciales['magic_link_expires']}</li>
        </ul>
        
        <h3>Acciones requeridas:</h3>
        <ul>
            <li>📋 Revisar el registro en el admin de leads</li>
            <li>📞 Contactar al cliente según medio preferido</li>
            <li>📄 Verificar documentos cuando sean subidos</li>
            <li>🤝 Asignar seguimiento al agente correspondiente</li>
        </ul>
        
        <p><strong>Fecha de registro:</strong> " . date('d/m/Y H:i:s') . "</p>
        ";
    }

    /**
     * Validar configuración de email
     */
    public function validarConfiguracion(): array
    {
        try {
            $config = config('Email');
            
            return [
                'configurado' => true,
                'protocolo' => $config->protocol ?? 'mail',
                'smtp_host' => $config->SMTPHost ?? 'N/A',
                'smtp_port' => $config->SMTPPort ?? 'N/A',
                'smtp_user' => $config->SMTPUser ?? 'N/A',
                'mensaje' => 'Configuración de email válida'
            ];
            
        } catch (Exception $e) {
            return [
                'configurado' => false,
                'error' => $e->getMessage(),
                'mensaje' => 'Error en configuración de email'
            ];
        }
    }
}