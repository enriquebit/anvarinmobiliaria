<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>
Registro de Clientes - ANVAR Inmobiliaria
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
:root {
    --anvar-primary: #1a1360;
    --anvar-success: #07c15b;
    --anvar-secondary: #b38f59;
    --anvar-light: #f8f9fa;
    --anvar-dark: #343a40;
}

.step-indicator {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
    align-items: center;
}

.step {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 10px;
    font-weight: bold;
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

.step.active {
    background-color: var(--anvar-success);
    color: white;
    box-shadow: 0 4px 8px rgba(7, 193, 91, 0.3);
}

.step.completed {
    background-color: var(--anvar-primary);
    color: white;
}

.step.pending {
    background-color: #e9ecef;
    color: #6c757d;
}

.step-connector {
    width: 80px;
    height: 3px;
    background-color: #e9ecef;
    transition: all 0.3s ease;
}

.step-connector.completed {
    background-color: var(--anvar-primary);
}

.form-step {
    display: none;
    animation: fadeIn 0.5s ease;
}

.form-step.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.required {
    color: #dc3545;
}

.btn-anvar-primary {
    background-color: var(--anvar-primary);
    border-color: var(--anvar-primary);
    color: white;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 25px;
    transition: all 0.3s ease;
}

.btn-anvar-primary:hover {
    background-color: #141050;
    border-color: #141050;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 19, 96, 0.3);
}

.btn-anvar-success {
    background-color: var(--anvar-success);
    border-color: var(--anvar-success);
    color: white;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 25px;
    transition: all 0.3s ease;
}

.btn-anvar-success:hover {
    background-color: #05a049;
    border-color: #05a049;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(7, 193, 91, 0.3);
}

.btn-anvar-secondary {
    background-color: var(--anvar-secondary);
    border-color: var(--anvar-secondary);
    color: white;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 25px;
    transition: all 0.3s ease;
}

.btn-anvar-secondary:hover {
    background-color: #9e7d4f;
    border-color: #9e7d4f;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(179, 143, 89, 0.3);
}

.welcome-container {
    text-align: center;
    padding: 40px 20px;
    background: #fafbfc;
    border-radius: 15px;
    margin-bottom: 30px;
    border: 1px solid #e1e4e8;
}

.welcome-title {
    color: var(--anvar-primary);
    font-weight: bold;
    font-size: 2rem;
    margin-bottom: 20px;
}

.welcome-subtitle {
    color: var(--anvar-success);
    font-weight: 600;
    font-size: 1.2rem;
    margin-bottom: 15px;
}

.brand-logo img {
    transition: transform 0.3s ease;
}

.brand-logo img:hover {
    transform: scale(1.05);
}

.instruction-card {
    background: white;
    border: 1px solid #e1e4e8;
    border-radius: 10px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.instruction-title {
    color: var(--anvar-primary);
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.instruction-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 12px;
    padding: 8px 0;
}

.instruction-item:last-child {
    margin-bottom: 0;
}

.instruction-icon {
    color: var(--anvar-secondary);
    margin-right: 12px;
    margin-top: 2px;
    flex-shrink: 0;
    font-size: 1.1rem;
}

.campo-dinamico {
    transition: all 0.3s ease;
}

.campo-dinamico.hidden {
    display: none !important;
}

.file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    background: #fafbfc;
}

.file-upload-area:hover {
    border-color: var(--anvar-primary);
    background: white;
}

.file-upload-area.dragover {
    border-color: var(--anvar-success);
    background: rgba(7, 193, 91, 0.1);
}

.file-preview {
    max-width: 250px;
    max-height: 180px;
    margin: 15px auto;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.document-icon {
    font-size: 3rem;
    color: var(--anvar-secondary);
    margin-bottom: 15px;
}

.success-container {
    text-align: center;
    padding: 50px 20px;
    background: #f6ffed;
    border-radius: 15px;
    margin: 30px 0;
    border: 1px solid #b7eb8f;
}

.success-icon {
    font-size: 5rem;
    color: var(--anvar-success);
    margin-bottom: 25px;
    animation: checkmark 0.6s ease-in-out;
}

@keyframes checkmark {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.alert-success-custom {
    background-color: rgba(7, 193, 91, 0.1);
    border: 2px solid var(--anvar-success);
    color: var(--anvar-primary);
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
}

.form-control.is-valid:focus {
    border-color: var(--anvar-success);
    box-shadow: 0 0 0 3px rgba(7, 193, 91, 0.1);
}

.desarrollo-selector {
    background: #fafbfc;
    border: 1px solid #e1e4e8;
    border-radius: 8px;
    padding: 20px;
    margin: 10px 0;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.desarrollo-selector:hover {
    border-color: var(--anvar-primary);
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.desarrollo-selector.selected {
    border-color: var(--anvar-success);
    background: white;
    box-shadow: 0 2px 8px rgba(7, 193, 91, 0.15);
}

.desarrollo-icon {
    font-size: 2.5rem;
    color: var(--anvar-secondary);
    margin-bottom: 10px;
}

.debug-info {
    background-color: var(--anvar-light);
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
    font-family: monospace;
    font-size: 12px;
}

/* Mobile-first responsive design */
.form-control {
    border: 1px solid #d1d5da;
    border-radius: 8px;
    padding: 14px 16px;
    font-size: 16px; /* Evita zoom en iOS */
    transition: all 0.2s ease;
    width: 100%;
    -webkit-appearance: none;
    appearance: none;
}

.form-control:hover {
    border-color: #b0b5bb;
}

.form-control:focus {
    border-color: var(--anvar-primary);
    box-shadow: 0 0 0 3px rgba(26, 19, 96, 0.1);
    outline: none;
}

.form-group {
    margin-bottom: 1.2rem;
}

.form-group label {
    font-weight: 500;
    color: #24292e;
    margin-bottom: 8px;
    font-size: 15px;
    display: block;
}

/* Optimización para móviles */
@media (max-width: 768px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
    
    .step-indicator {
        margin-bottom: 1.5rem;
    }
    
    .step {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .step-connector {
        width: 60px;
    }
    
    .btn {
        padding: 12px 24px;
        font-size: 16px;
        width: 100%;
        margin-bottom: 10px;
    }
    
    .btn:last-child {
        margin-bottom: 0;
    }
}

/* Cards más sutiles */
.card {
    border: 1px solid #e1e4e8;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.card-header {
    border-bottom: 1px solid #e1e4e8;
}

/* Ajustar sombra del card principal */
.shadow-lg {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07) !important;
}

/* Sección de co-propietario más sutil */
.text-muted {
    color: #586069 !important;
}

hr {
    border-color: #e1e4e8;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" id="step0-indicator">1</div>
                <div class="step-connector" id="connector0"></div>
                <div class="step pending" id="step1-indicator">2</div>
                <div class="step-connector" id="connector1"></div>
                <div class="step pending" id="step2-indicator">3</div>
            </div>

            <!-- Alert for agente detection -->
            <div class="alert alert-info" id="agenteAlert" style="display: none;">
                <i class="fas fa-user-tie mr-2"></i>
                <span id="agenteMessage"></span>
            </div>

            <!-- Step 0: Welcome Screen -->
            <div class="form-step active" id="step0-content">
                <div class="welcome-container">
                    <div class="brand-logo mb-4">
                        <img src="<?= base_url('assets/img/logo_admin.png') ?>" alt="ANVAR Inmobiliaria" class="img-fluid" style="max-height: 80px;">
                    </div>
                    <h1 class="welcome-title">🌟 ¡Bienvenido a ANVAR Inmobiliaria!</h1>
                    <p class="welcome-subtitle">Este formulario te permite iniciar tu expediente digital de manera segura y rápida.</p>
                    
                    <div class="mt-4 mb-4">
                        <h5 class="welcome-subtitle">¿Ya te registraste? ¡Comencemos!</h5>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        📌 Antes de comenzar:
                    </div>
                    
                    <div class="instruction-item">
                        <i class="fas fa-id-card instruction-icon"></i>
                        <span>El titular debe coincidir exactamente con el nombre en la INE.</span>
                    </div>
                    
                    <div class="instruction-item">
                        <i class="fas fa-eye instruction-icon"></i>
                        <span>No se aceptan documentos borrosos, cortados o con sombra.</span>
                    </div>
                    
                    <div class="instruction-item">
                        <i class="fas fa-camera instruction-icon"></i>
                        <span>La selfie debe tomarse con buena luz, rostro visible y la INE en mano.</span>
                    </div>
                    
                    <div class="instruction-item">
                        <i class="fas fa-envelope instruction-icon"></i>
                        <span>El correo y teléfono serán tus medios oficiales de comunicación.</span>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="button" class="btn btn-anvar-success btn-lg" onclick="nextStep()">
                        <i class="fas fa-arrow-right mr-2"></i>
                        Continuar
                    </button>
                </div>
            </div>

            <!-- Step 1: Personal Data Form -->
            <div class="form-step" id="step1-content">
                <div class="card shadow-lg border-0">
                    <div class="card-header text-center" style="background: var(--anvar-primary); color: white;">
                        <h4 class="mb-0">
                            <i class="fas fa-user mr-2"></i>
                            Información Personal del Titular
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="personalDataForm" novalidate>
                            
                            <!-- Información Personal -->
                            <div class="form-group">
                                <label class="required">Nombre del titular *</label>
                                <div class="row">
                                    <div class="col-4">
                                        <input type="text" class="form-control" id="firstname" name="firstname" required
                                               placeholder="Nombre(s)" maxlength="100">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" id="lastname" name="lastname" required
                                               placeholder="Apellido paterno" maxlength="100">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno"
                                               placeholder="Apellido materno" maxlength="100">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- RFC/CURP -->
                            <div class="form-group">
                                <label for="rfc_curp" class="required">RFC o CURP *</label>
                                <input type="text" class="form-control" id="rfc_curp" name="rfc_curp" required
                                       placeholder="RFC de 13 dígitos o CURP de 18 dígitos" maxlength="18" 
                                       style="text-transform: uppercase;" pattern="[A-Z0-9]{13,18}">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Ingresa tu RFC (13 caracteres) o CURP (18 caracteres) tal como aparece en tu documento oficial.
                                </small>
                            </div>

                            <!-- Co-propietario (Opcional) -->
                            <div class="form-group">
                                <label for="nombre_copropietario">
                                    <i class="fas fa-users mr-1"></i>
                                    Nombre del co-propietario (opcional)
                                </label>
                                <input type="text" class="form-control" id="nombre_copropietario" name="nombre_copropietario"
                                       placeholder="Nombre completo del co-propietario" maxlength="255">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="parentesco_copropietario">Parentesco del co-propietario</label>
                                <select class="form-control" id="parentesco_copropietario" name="parentesco_copropietario">
                                    <option value="">Seleccione parentesco</option>
                                    <option value="conyuge">Cónyuge</option>
                                    <option value="hijo">Hijo/a</option>
                                    <option value="padre">Padre/Madre</option>
                                    <option value="hermano">Hermano/a</option>
                                    <option value="otro">Otro</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Información de Contacto -->
                            <div class="form-group">
                                <label for="email" class="required">Correo para trámites y avisos *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       placeholder="ejemplo@correo.com" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="mobilephone" class="required">WhatsApp *</label>
                                <input type="tel" class="form-control" id="mobilephone" name="mobilephone" required
                                       placeholder="5551234567" maxlength="15" pattern="[0-9]{10,15}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Teléfono para llamadas</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       placeholder="5551234567" maxlength="15" pattern="[0-9]{10,15}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Medio de Contacto Preferido -->
                            <div class="form-group">
                                <label for="medio_de_contacto" class="required">Medio de contacto preferido *</label>
                                <select class="form-control" id="medio_de_contacto" name="medio_de_contacto" required>
                                    <option value="">Selecciona tu preferencia</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="telefono">Llamada telefónica</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Desarrollo de Interés -->
                            <div class="form-group">
                                <label class="required">Selecciona el desarrollo donde invertirás *</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="desarrollo-selector" data-desarrollo="valle_natura">
                                            <div class="desarrollo-icon">
                                                <i class="fas fa-tree"></i>
                                            </div>
                                            <h5 style="color: var(--anvar-primary);">Valle Natura</h5>
                                            <p class="text-muted mb-0">Terrenos</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="desarrollo-selector" data-desarrollo="cordelia">
                                            <div class="desarrollo-icon">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <h5 style="color: var(--anvar-primary);">Cordelia</h5>
                                            <p class="text-muted mb-0">Casas y Departamentos</p>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="desarrollo" name="desarrollo" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Campos dinámicos según desarrollo -->
                            <div id="campos-valle-natura" style="display: none;">
                                <div class="form-group">
                                    <label for="manzana">Manzana</label>
                                    <input type="text" class="form-control" id="manzana" name="manzana"
                                           placeholder="Ej: A, B, C..." maxlength="10">
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lote">Lote</label>
                                    <input type="text" class="form-control" id="lote" name="lote"
                                           placeholder="Ej: 1, 15, 23..." maxlength="10">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div id="campos-cordelia" style="display: none;">
                                <div class="form-group">
                                    <label for="numero_casa_depto">Número Casa/Departamento</label>
                                    <input type="text" class="form-control" id="numero_casa_depto" name="numero_casa_depto"
                                           placeholder="Ej: 101, Casa 5..." maxlength="10">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Campo oculto para agente -->
                            <input type="hidden" id="agente_referido" name="agente_referido">

                            <!-- Términos y Condiciones -->
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="acepta_terminos" name="acepta_terminos" required>
                                    <label class="custom-control-label" for="acepta_terminos">
                                        <span class="required">*</span> Acepto los 
                                        <a href="#" target="_blank" style="color: var(--anvar-primary);">términos y condiciones</a> 
                                        y autorizo el tratamiento de mis datos personales.
                                    </label>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-anvar-secondary" onclick="prevStep()">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Anterior
                                </button>
                                <button type="button" class="btn btn-anvar-success" onclick="submitPersonalData()">
                                    <i class="fas fa-arrow-right mr-2"></i>
                                    Continuar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Step 2: Document Upload -->
            <div class="form-step" id="step2-content">
                <div class="card shadow-lg border-0">
                    <div class="card-header text-center" style="background: var(--anvar-success); color: white;">
                        <h4 class="mb-0">Ya casi terminamos</h4>
                        <p class="mb-0 mt-2">Sube tu identificación por ambos lados</p>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Elemento visual de identificación -->
                        <div class="text-center mb-4">
                            <div class="document-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h5 style="color: var(--anvar-primary);">Credencial de Elector (INE)</h5>
                            <p class="text-muted">Frente y reverso en formato JPG, PNG o PDF</p>
                        </div>

                        <form id="documentForm" novalidate>
                            <!-- INE Frente -->
                            <div class="form-group">
                                <label for="ine_frente" class="required">INE - Frente *</label>
                                <div class="file-upload-area" data-upload="ine_frente" id="upload-area-ine_frente">
                                    <i class="fas fa-upload fa-2x mb-3" style="color: var(--anvar-secondary);"></i>
                                    <p class="mb-2"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    <p class="text-muted mb-0">Formatos: JPG, PNG, PDF (Máx. 5MB)</p>
                                </div>
                                <input type="file" id="ine_frente" name="ine_frente" accept="image/*,.pdf" style="position: absolute; left: -9999px; opacity: 0;" required>
                                <div class="file-preview-container" id="preview-ine_frente"></div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- INE Reverso -->
                            <div class="form-group">
                                <label for="ine_reverso" class="required">INE - Reverso *</label>
                                <div class="file-upload-area" data-upload="ine_reverso" id="upload-area-ine_reverso">
                                    <i class="fas fa-upload fa-2x mb-3" style="color: var(--anvar-secondary);"></i>
                                    <p class="mb-2"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    <p class="text-muted mb-0">Formatos: JPG, PNG, PDF (Máx. 5MB)</p>
                                </div>
                                <input type="file" id="ine_reverso" name="ine_reverso" accept="image/*,.pdf" style="position: absolute; left: -9999px; opacity: 0;" required>
                                <div class="file-preview-container" id="preview-ine_reverso"></div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Comprobante de Domicilio -->
                            <div class="form-group">
                                <label for="comprobante_domicilio" class="required">Comprobante de Domicilio *</label>
                                <div class="file-upload-area" data-upload="comprobante_domicilio" id="upload-area-comprobante_domicilio">
                                    <i class="fas fa-upload fa-2x mb-3" style="color: var(--anvar-secondary);"></i>
                                    <p class="mb-2"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    <p class="text-muted mb-0">Recibo de luz, agua, teléfono (Máx. 5MB)</p>
                                </div>
                                <input type="file" id="comprobante_domicilio" name="comprobante_domicilio" accept="image/*,.pdf" style="position: absolute; left: -9999px; opacity: 0;" required>
                                <div class="file-preview-container" id="preview-comprobante_domicilio"></div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-anvar-secondary" onclick="prevStep()">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Anterior
                                </button>
                                <button type="button" class="btn btn-anvar-success" onclick="submitDocuments()">
                                    <i class="fas fa-check mr-2"></i>
                                    Finalizar Registro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Step 3: Success Confirmation -->
            <div class="form-step" id="step3-content">
                <div class="success-container">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    
                    <div class="alert-success-custom">
                        <h4 style="color: var(--anvar-primary); margin-bottom: 20px;">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            ¡Registro Completado con Éxito!
                        </h4>
                        
                        <p style="font-size: 1.1rem; line-height: 1.6;">
                            📄 Hemos recibido correctamente tu información inicial y estamos validando tu identidad.<br><br>
                            
                            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                <strong>⏳ Proceso de Aprobación:</strong><br>
                                Tu cuenta está pendiente de aprobación por nuestro equipo administrativo. 
                                Te notificaremos por email una vez que sea activada para acceder a tu panel de cliente.
                            </div>
                            
                            En breve recibirás tu contrato digital para firmarlo desde casa o agendar una cita presencial.<br><br>
                            <strong>Recuerda:</strong> Tu nombre debe coincidir con tu INE y la firma debe ser personal.<br><br>
                            <span style="color: var(--anvar-success); font-weight: bold;">
                                Gracias por elegir ANVAR Inmobiliaria.
                            </span>
                        </p>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-anvar-primary btn-lg" onclick="location.reload()">
                            <i class="fas fa-home mr-2"></i>
                            Nuevo Registro
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Debug Info (Solo desarrollo) -->
<?php if (ENVIRONMENT === 'development'): ?>
<div class="debug-info mt-4">
    <h6>Debug Information:</h6>
    <div id="debugInfo"></div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Variables globales
    let currentStep = 0;
    let agenteId = null;
    let personalData = {};
    let uploadedFiles = {};

    // Inicializar
    initialize();

    // ===============================================
    // INICIALIZACIÓN
    // ===============================================

    function initialize() {
        // Detectar agente de la URL
        detectarAgente();
        
        // Configurar eventos de desarrollo
        setupDesarrolloSelection();
        
        // Configurar upload de archivos
        setupFileUploads();
        
        // Cargar lotes para el desarrollo seleccionado por defecto (valle_natura)
        const desarrolloDefault = $('#desarrollo').val();
        if (desarrolloDefault) {
            cargarLotesDisponibles(desarrolloDefault);
        }
        
        // Debug info
        updateDebugInfo('Formulario inicializado', { currentStep, agenteId, desarrolloDefault });
        
    }

    function detectarAgente() {
        const urlParams = new URLSearchParams(window.location.search);
        agenteId = urlParams.get('agente');
        
        if (agenteId) {
            $('#agente_referido').val(agenteId);
            $('#agenteAlert').show();
            $('#agenteMessage').text(`Agente referido: ${agenteId}`);
            
            updateDebugInfo('[AGENTE] Agente detectado', { agente: agenteId });
        }
    }

    // ===============================================
    // NAVEGACIÓN ENTRE PASOS
    // ===============================================

    window.nextStep = function() {
        if (currentStep < 3) {
            currentStep++;
            updateStepIndicator();
            showStep(currentStep);
            
            updateDebugInfo(`[NAVEGACION] Avanzar al paso ${currentStep}`);
        }
    };

    window.prevStep = function() {
        if (currentStep > 0) {
            currentStep--;
            updateStepIndicator();
            showStep(currentStep);
            
            updateDebugInfo(`[NAVEGACION] Retroceder al paso ${currentStep}`);
        }
    };

    function showStep(step) {
        $('.form-step').removeClass('active');
        $(`#step${step}-content`).addClass('active');
        
        // Scroll al inicio
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    function updateStepIndicator() {
        $('.step').removeClass('active completed').addClass('pending');
        $('.step-connector').removeClass('completed');
        
        for (let i = 0; i <= currentStep; i++) {
            if (i === currentStep) {
                $(`#step${i}-indicator`).removeClass('pending completed').addClass('active');
            } else {
                $(`#step${i}-indicator`).removeClass('pending active').addClass('completed');
            }
            
            if (i < currentStep) {
                $(`#connector${i}`).addClass('completed');
            }
        }
    }

    // ===============================================
    // SELECCIÓN DE DESARROLLO
    // ===============================================

    function setupDesarrolloSelection() {
        $('.desarrollo-selector').on('click', function() {
            const desarrollo = $(this).data('desarrollo');
            
            // Actualizar selección visual
            $('.desarrollo-selector').removeClass('selected');
            $(this).addClass('selected');
            
            // Actualizar campo hidden
            $('#desarrollo').val(desarrollo);
            
            // Limpiar inputs de lotes del desarrollo anterior
            limpiarInputsLotes();
            
            // Mostrar/ocultar campos según desarrollo
            if (desarrollo === 'valle_natura') {
                $('#campos-cordelia').hide();
                $('#campos-valle-natura').show();
            } else if (desarrollo === 'cordelia') {
                $('#campos-valle-natura').hide();
                $('#campos-cordelia').show();
            }
            
            // Cargar lotes disponibles
            cargarLotesDisponibles(desarrollo);
            
            updateDebugInfo('[DESARROLLO] Desarrollo seleccionado', { desarrollo });
        });
    }

    // ===============================================
    // CARGA DE LOTES POR DESARROLLO
    // ===============================================

    function cargarLotesDisponibles(desarrollo) {
        updateDebugInfo('[LOTES] Iniciando carga de lotes', { desarrollo: desarrollo });
        
        // Mostrar indicador de carga
        mostrarIndicadorCarga();
        
        const ajaxUrl = '<?= base_url('registro-clientes/obtener-lotes') ?>';
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: { desarrollo: desarrollo },
            dataType: 'json',
            timeout: 10000,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                updateDebugInfo('[LOTES] Petición AJAX enviada', { url: ajaxUrl, desarrollo: desarrollo });
            },
            success: function(response) {
                updateDebugInfo('[LOTES] Respuesta recibida', response);
                ocultarIndicadorCarga();
                
                if (response && response.success) {
                    actualizarInterfazLotes(desarrollo, response.data, response.estadisticas);
                    
                    updateDebugInfo('[LOTES] Lotes cargados exitosamente', {
                        desarrollo: desarrollo,
                        total_lotes: response.data.length,
                        estadisticas: response.estadisticas
                    });
                } else {
                    console.error('[LOTES] Error en respuesta:', response);
                    updateDebugInfo('[LOTES] Error en respuesta', response);
                    mostrarErrorLotes('No se pudieron cargar los lotes disponibles: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr, status, error) {
                console.error('[LOTES] Error AJAX completo:', { xhr: xhr, status: status, error: error });
                console.error('[LOTES] Response Text:', xhr.responseText);
                updateDebugInfo('[LOTES] Error AJAX', { 
                    status: status, 
                    error: error, 
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                ocultarIndicadorCarga();
                mostrarErrorLotes('Error de conexión al cargar los lotes: ' + error);
            }
        });
    }

    function mostrarIndicadorCarga() {
        // Crear indicador si no existe
        if ($('#lotes-loading').length === 0) {
            const loader = `
                <div id="lotes-loading" class="alert alert-info text-center">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Cargando inmuebles disponibles...
                </div>
            `;
            $('#desarrollo').closest('.form-group').after(loader);
        }
        $('#lotes-loading').show();
    }

    function ocultarIndicadorCarga() {
        $('#lotes-loading').hide();
    }

    function mostrarErrorLotes(mensaje) {
        // Remover errores anteriores
        $('#lotes-error').remove();
        
        const error = `
            <div id="lotes-error" class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                ${mensaje}
            </div>
        `;
        $('#desarrollo').closest('.form-group').after(error);
    }

    function actualizarInterfazLotes(desarrollo, lotes, estadisticas) {
        
        // Remover indicadores anteriores
        $('#lotes-loading, #lotes-error, #lotes-info').remove();
        
        if (lotes.length === 0) {
            mostrarErrorLotes('No hay inmuebles disponibles en este desarrollo');
            limpiarInputsLotes();
            return;
        }
        
        // Actualizar inputs según el desarrollo
        if (desarrollo === 'valle_natura') {
            actualizarInputsValleNatura(lotes);
        } else if (desarrollo === 'cordelia') {
            actualizarInputsCordelia(lotes);
        }
        
        // Mostrar información de disponibilidad
        const info = `
            <div id="lotes-info" class="alert alert-success">
                <div class="row">
                    <div class="col-sm-8">
                        <strong><i class="fas fa-home mr-2"></i>${lotes.length} inmuebles disponibles</strong>
                        <small class="d-block text-muted">Precios desde $${formatearPrecio(estadisticas.precio_min)}</small>
                    </div>
                    <div class="col-sm-4 text-right">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarDetallesLotes('${desarrollo}')">
                            <i class="fas fa-list mr-1"></i>Ver Detalles
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#desarrollo').closest('.form-group').after(info);
        
        // Guardar lotes en variable global para uso posterior
        window.lotesDisponibles = lotes;
        window.estadisticasLotes = estadisticas;
    }

    function actualizarInputsValleNatura(lotes) {
        
        // Obtener manzanas únicas
        const manzanas = [...new Set(lotes.map(lote => lote.manzana_nombre))].sort();
        
        // Convertir input de manzana en select
        const manzanaInput = $('#manzana');
        const manzanaSelect = `
            <select class="form-control" id="manzana" name="manzana" required>
                <option value="">Selecciona una manzana</option>
                ${manzanas.map(manzana => `<option value="${manzana}">${manzana}</option>`).join('')}
            </select>
        `;
        manzanaInput.replaceWith(manzanaSelect);
        
        // Evento para actualizar lotes cuando se seleccione manzana
        $('#manzana').on('change', function() {
            const manzanaSeleccionada = $(this).val();
            actualizarLotesPorManzana(manzanaSeleccionada, lotes);
        });
        
        // Limpiar select de lotes
        actualizarSelectLotes([]);
        
        updateDebugInfo('[LOTES] Valle Natura configurado', {
            total_lotes: lotes.length,
            manzanas: manzanas
        });
    }

    function actualizarLotesPorManzana(manzana, todosLotes) {
        if (!manzana) {
            actualizarSelectLotes([]);
            return;
        }
        
        const lotesDeManzana = todosLotes.filter(lote => lote.manzana_nombre === manzana);
        actualizarSelectLotes(lotesDeManzana);
        
    }

    function actualizarSelectLotes(lotes) {
        const loteInput = $('#lote');
        
        if (lotes.length === 0) {
            const loteSelect = `
                <select class="form-control" id="lote" name="lote" required disabled>
                    <option value="">Primero selecciona una manzana</option>
                </select>
            `;
            loteInput.replaceWith(loteSelect);
        } else {
            const loteSelect = `
                <select class="form-control" id="lote" name="lote" required>
                    <option value="">Selecciona un lote</option>
                    ${lotes.map(lote => `
                        <option value="${lote.numero}" data-precio="${lote.precio_total}" data-area="${lote.area}">
                            Lote ${lote.numero} - ${lote.area}m² - ${formatearPrecio(lote.precio_total)}
                        </option>
                    `).join('')}
                </select>
            `;
            loteInput.replaceWith(loteSelect);
        }
    }

    function actualizarInputsCordelia(lotes) {
        
        // Convertir input de número casa/depto en select
        const casaInput = $('#numero_casa_depto');
        const casaSelect = `
            <select class="form-control" id="numero_casa_depto" name="numero_casa_depto" required>
                <option value="">Selecciona casa/departamento</option>
                ${lotes.map(lote => `
                    <option value="${lote.numero}" data-precio="${lote.precio_total}" data-area="${lote.area}">
                        ${lote.numero} - ${lote.area}m² - ${formatearPrecio(lote.precio_total)}
                    </option>
                `).join('')}
            </select>
        `;
        casaInput.replaceWith(casaSelect);
        
        updateDebugInfo('[LOTES] Cordelia configurado', {
            total_opciones: lotes.length
        });
    }

    function limpiarInputsLotes() {
        
        // Restaurar inputs originales
        const manzanaInput = $('#manzana');
        if (manzanaInput.is('select')) {
            manzanaInput.replaceWith('<input type="text" class="form-control" id="manzana" name="manzana" placeholder="Ej: A, B, C..." maxlength="10">');
        }
        
        const loteInput = $('#lote');
        if (loteInput.is('select')) {
            loteInput.replaceWith('<input type="text" class="form-control" id="lote" name="lote" placeholder="Ej: 1, 15, 23..." maxlength="10">');
        }
        
        const casaInput = $('#numero_casa_depto');
        if (casaInput.is('select')) {
            casaInput.replaceWith('<input type="text" class="form-control" id="numero_casa_depto" name="numero_casa_depto" placeholder="Ej: 101, Casa 5..." maxlength="10">');
        }
    }

    function formatearPrecio(precio) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 0
        }).format(precio);
    }

    function mostrarDetallesLotes(desarrollo) {
        if (!window.lotesDisponibles || window.lotesDisponibles.length === 0) {
            alert('No hay lotes cargados');
            return;
        }
        
        
        // Crear modal con detalles
        let modalContent = `
            <div class="modal fade" id="modalLotes" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-home mr-2"></i>
                                Inmuebles Disponibles - ${desarrollo === 'valle_natura' ? 'Valle Natura' : 'Cordelia'}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Proyecto</th>
                                            <th>Manzana</th>
                                            <th>Lote</th>
                                            <th>Área</th>
                                            <th>Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
        `;
        
        window.lotesDisponibles.forEach(lote => {
            modalContent += `
                <tr>
                    <td>${lote.proyecto_nombre}</td>
                    <td>${lote.manzana_nombre}</td>
                    <td>${lote.numero}</td>
                    <td>${lote.area} m²</td>
                    <td>${formatearPrecio(lote.precio_total)}</td>
                </tr>
            `;
        });
        
        modalContent += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-3">
                                <strong>Estadísticas:</strong><br>
                                • Total disponibles: ${window.estadisticasLotes.total_disponibles}<br>
                                • Precio promedio: ${formatearPrecio(window.estadisticasLotes.precio_promedio)}<br>
                                • Área promedio: ${Math.round(window.estadisticasLotes.area_promedio)} m²
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remover modal anterior si existe
        $('#modalLotes').remove();
        
        // Agregar y mostrar modal
        $('body').append(modalContent);
        $('#modalLotes').modal('show');
    }

    // ===============================================
    // ENVÍO DE DATOS PERSONALES
    // ===============================================

    window.submitPersonalData = function() {
        
        const form = $('#personalDataForm')[0];
        const formData = new FormData(form);
        
        // Validar formulario
        if (!validatePersonalForm()) {
            return;
        }
        
        // Guardar datos localmente
        personalData = Object.fromEntries(formData.entries());
        
        updateDebugInfo('[ENVIO] Datos personales preparados', personalData);
        
        // Avanzar al siguiente paso
        nextStep();
    };

    function validatePersonalForm() {
        const form = $('#personalDataForm')[0];
        let isValid = true;
        
        // Limpiar validaciones previas
        $('.form-control').removeClass('is-invalid is-valid');
        $('.invalid-feedback').text('');
        
        // Validar campos requeridos
        const requiredFields = ['firstname', 'lastname', 'rfc_curp', 'email', 'mobilephone', 'medio_de_contacto', 'desarrollo'];
        
        requiredFields.forEach(field => {
            const input = $(`#${field}`);
            const value = input.val().trim();
            
            if (!value) {
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').text(`Este campo es obligatorio`);
                isValid = false;
            } else {
                input.addClass('is-valid');
            }
        });
        
        // Validar email
        const email = $('#email').val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('#email').addClass('is-invalid');
            $('#email').siblings('.invalid-feedback').text('Formato de email inválido');
            isValid = false;
        }
        
        // Validar RFC/CURP
        const rfcCurp = $('#rfc_curp').val().trim().toUpperCase();
        if (rfcCurp) {
            if (rfcCurp.length !== 13 && rfcCurp.length !== 18) {
                $('#rfc_curp').addClass('is-invalid');
                $('#rfc_curp').siblings('.invalid-feedback').text('RFC debe tener 13 caracteres o CURP 18 caracteres');
                isValid = false;
            } else if (!/^[A-Z0-9]+$/.test(rfcCurp)) {
                $('#rfc_curp').addClass('is-invalid');
                $('#rfc_curp').siblings('.invalid-feedback').text('Solo se permiten letras y números');
                isValid = false;
            } else {
                // Actualizar el campo con formato uppercase
                $('#rfc_curp').val(rfcCurp);
            }
        }
        
        // Validar términos
        if (!$('#acepta_terminos').is(':checked')) {
            $('#acepta_terminos').addClass('is-invalid');
            $('#acepta_terminos').siblings('.invalid-feedback').text('Debe aceptar los términos y condiciones');
            isValid = false;
        }
        
        return isValid;
    }

    // ===============================================
    // UPLOAD DE ARCHIVOS
    // ===============================================

    function setupFileUploads() {
        
        // Configurar cada área de upload
        const uploadTypes = ['ine_frente', 'ine_reverso', 'comprobante_domicilio'];
        
        uploadTypes.forEach(function(uploadType) {
            const uploadArea = document.getElementById(`upload-area-${uploadType}`);
            const fileInput = document.getElementById(uploadType);
            
            if (!uploadArea || !fileInput) {
                console.error(`[REGISTRO] No se encontró el elemento para ${uploadType}`);
                return;
            }
            
            
            // Limpiar eventos previos
            uploadArea.removeEventListener('click', handleUploadAreaClick);
            fileInput.removeEventListener('change', handleFileInputChange);
            
            // Click en el área de upload
            function handleUploadAreaClick(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Forzar el click en el input file
                setTimeout(() => {
                    fileInput.click();
                }, 10);
            }
            
            // Cambio en el input file
            function handleFileInputChange(e) {
                if (e.target.files && e.target.files.length > 0) {
                    handleFileSelection(e.target.files[0], uploadType);
                }
            }
            
            // Asignar eventos
            uploadArea.addEventListener('click', handleUploadAreaClick);
            fileInput.addEventListener('change', handleFileInputChange);
            
            // Drag and drop
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelection(files[0], uploadType);
                }
            });
            
        });
        
    }

    function handleFileSelection(file, uploadType) {
        // Validar archivo
        if (!validateFile(file, uploadType)) {
            return;
        }
        
        // Guardar archivo
        uploadedFiles[uploadType] = file;
        
        // Mostrar preview
        showFilePreview(file, uploadType);
        
        // Marcar como válido
        $(`#${uploadType}`).addClass('is-valid').removeClass('is-invalid');
        
        updateDebugInfo(`[UPLOAD] Archivo seleccionado: ${uploadType}`, {
            name: file.name,
            size: file.size,
            type: file.type
        });
        
    }

    function validateFile(file, uploadType) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        
        if (file.size > maxSize) {
            alert('El archivo es muy grande. Máximo 5MB.');
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert('Formato no permitido. Use JPG, PNG o PDF.');
            return false;
        }
        
        return true;
    }

    function showFilePreview(file, uploadType) {
        const previewContainer = $(`#preview-${uploadType}`);
        previewContainer.empty();
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = `<img src="${e.target.result}" class="file-preview" alt="Preview">
                           <p class="text-success mt-2"><i class="fas fa-check mr-1"></i>${file.name}</p>`;
                previewContainer.html(img);
            };
            reader.readAsDataURL(file);
        } else {
            const preview = `<div class="text-center mt-3">
                           <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                           <p class="text-success"><i class="fas fa-check mr-1"></i>${file.name}</p>
                         </div>`;
            previewContainer.html(preview);
        }
    }

    // ===============================================
    // ENVÍO FINAL
    // ===============================================

    window.submitDocuments = function() {
        
        // Validar archivos
        const requiredFiles = ['ine_frente', 'ine_reverso', 'comprobante_domicilio'];
        let allFilesValid = true;
        
        requiredFiles.forEach(fileType => {
            if (!uploadedFiles[fileType]) {
                $(`#${fileType}`).addClass('is-invalid');
                $(`#${fileType}`).siblings('.invalid-feedback').text('Este documento es obligatorio');
                allFilesValid = false;
            }
        });
        
        if (!allFilesValid) {
            alert('Por favor, sube todos los documentos requeridos.');
            return;
        }
        
        // Preparar FormData completo
        const formData = new FormData();
        
        // Agregar datos personales
        Object.keys(personalData).forEach(key => {
            formData.append(key, personalData[key]);
        });
        
        // Agregar archivos con nombres esperados por el backend
        const fileMapping = {
            'ine_frente': 'file_ine_frente',
            'ine_reverso': 'file_ine_reverso', 
            'comprobante_domicilio': 'file_comprobante_domicilio'
        };
        
        Object.keys(uploadedFiles).forEach(key => {
            const backendFieldName = fileMapping[key] || key;
            formData.append(backendFieldName, uploadedFiles[key]);
        });
        
        updateDebugInfo('[ENVIO] Enviando registro completo...', {
            personalData: Object.keys(personalData),
            files: Object.keys(uploadedFiles)
        });
        
        // Enviar al servidor
        $.ajax({
            url: '<?= base_url('registro-clientes/procesar') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                
                if (response.success) {
                    updateDebugInfo('[EXITO] Registro completado', response);
                    nextStep(); // Ir a pantalla de éxito
                } else {
                    updateDebugInfo('[ERROR] Error en el registro', response);
                    alert('Error: ' + (response.error || 'Error desconocido'));
                }
            },
            error: function(xhr, status, error) {
                console.error('[REGISTRO] Error en la petición:', error);
                updateDebugInfo('[ERROR] Error de conexión', { status, error });
                alert('Error de conexión. Por favor, intente nuevamente.');
            }
        });
    };

    // ===============================================
    // DEBUG
    // ===============================================

    function updateDebugInfo(action, data = {}) {
        <?php if (ENVIRONMENT === 'development'): ?>
        const timestamp = new Date().toLocaleTimeString();
        const debugEntry = `<div><strong>[${timestamp}]</strong> ${action}<br>
                          <small>${JSON.stringify(data, null, 2)}</small></div><hr>`;
        $('#debugInfo').prepend(debugEntry);
        <?php endif; ?>
    }

});
</script>
<?= $this->endSection() ?>