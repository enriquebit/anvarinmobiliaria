# Anvar Inmobiliaria 2.0
> Sistema de Gestión Inmobiliaria Completo

## 🏢 Descripción
Sistema integral de gestión inmobiliaria desarrollado con CodeIgniter 4 y Shield v1.1 para la administración completa de ventas, clientes, proyectos y cobranza inmobiliaria.

## ✨ Características Principales

### 🔐 Sistema de Autenticación
- **Shield v1.1** con autenticación robusta
- **6 roles granulares**: SuperAdmin, Admin, SuperVendedor, Vendedor, SubVendedor, Visor, Cliente
- **Permisos granulares** por módulos y acciones
- **Magic Links** para acceso de clientes

### 🏗️ Módulos Core
- **Ventas** - Gestión completa del proceso de venta
- **Clientes** - CRM con leads y seguimiento
- **Proyectos** - Administración de desarrollos inmobiliarios
- **Lotes** - Inventario detallado por manzanas
- **Cobranza** - Sistema de pagos y amortización
- **Comisiones** - Cálculo automático para vendedores

### 💰 Sistema Financiero
- **Tabla de Amortización** automática
- **Pagos sincronizados** entre módulos
- **Comprobantes térmicos** formato 60mm
- **Función num_to_words()** en español
- **Reportes financieros** detallados

### 🎨 Interfaz de Usuario
- **AdminLTE v3.2** responsive
- **DataTables** con traducción española
- **Layouts especializados** por tipo de usuario
- **Dashboard dinámico** con métricas

## 🚀 Tecnologías

- **Framework**: CodeIgniter 4.4+
- **Autenticación**: CodeIgniter Shield v1.1
- **Base de Datos**: MySQL 8.0+
- **Frontend**: AdminLTE v3.2, Bootstrap 4, jQuery
- **PHP**: 8.1+

## 📋 Requisitos del Sistema

### Servidor
- PHP 8.1 o superior
- MySQL 8.0 o superior
- Apache/Nginx con mod_rewrite
- Composer 2.0+

### Extensiones PHP Requeridas
- `intl`
- `mbstring`
- `mysqlnd`
- `curl`
- `json`
- `gd` (para generación de imágenes)

## 🛠️ Instalación

### 1. Clonar Repositorio
```bash
git clone https://github.com/enriquebit/anvarinmobiliaria.git
cd anvarinmobiliaria
```

### 2. Instalar Dependencias
```bash
composer install
npm install
```

### 3. Configurar Base de Datos
```bash
# Copiar archivo de configuración
cp env .env

# Editar configuración de base de datos
nano .env
```

### 4. Migrar Base de Datos
```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

### 5. Configurar Permisos
```bash
chmod -R 755 writable/
chmod -R 755 public/uploads/
```

## ⚙️ Configuración

### Variables de Entorno (.env)
```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/'

database.default.hostname = localhost
database.default.database = nuevoanvar_vacio
database.default.username = root
database.default.password = 0191
database.default.DBDriver = MySQLi

# Configuración de Email
email.protocol = smtp
email.SMTPHost = mail.anvarinmobiliaria.com
email.SMTPUser = noreply@anvarinmobiliaria.com
email.SMTPPass = your_password
email.SMTPPort = 465
email.SMTPCrypto = ssl
```

## 👥 Estructura de Roles

### SuperAdmin
- Acceso total al sistema
- Configuración global
- Gestión de usuarios y permisos

### Admin
- Gestión completa excepto configuración del sistema
- Todos los módulos operativos
- Reportes y estadísticas

### SuperVendedor
- Ventas + supervisión
- Gestión de equipos
- Comisiones avanzadas

### Vendedor
- Ventas y clientes
- Contratos y cobranza básica
- Reportes propios

### SubVendedor
- Ventas limitadas
- Solo clientes asignados
- Supervisión requerida

### Visor
- Solo lectura
- Reportes y consultas
- Sin modificaciones

### Cliente
- Portal personal
- Estado de cuenta
- Documentos propios

## 📊 Módulos Principales

### Ventas
- Proceso completo de venta
- Contratos digitales
- Seguimiento de estatus
- Cálculo automático de comisiones

### Cobranza
- Tabla de amortización automática
- Registro de pagos
- Comprobantes térmicos
- Alertas de vencimiento

### Proyectos
- Gestión de desarrollos
- Inventario de lotes
- Precios por zona
- Documentación técnica

### Reportes
- Ventas por período
- Flujo de caja
- Comisiones
- Estados de cuenta

## 🎯 Roadmap

### v1.1 (Próxima)
- [ ] Integración con pasarelas de pago
- [ ] Firma digital de contratos
- [ ] API REST completa
- [ ] App móvil

### v1.2 (Futuro)
- [ ] Integración con CRM externo
- [ ] Módulo de marketing
- [ ] Analytics avanzado
- [ ] Reportes BI

## 🤝 Contribución
1. Fork el proyecto
2. Crea tu rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia
Este proyecto es privado y propietario de ANVAR Desarrollos Inmobiliarios.

## 📞 Soporte
- **Desarrollador**: enriquebit
- **Empresa**: ANVAR Desarrollos Inmobiliarios

---
**Anvar Inmobiliaria 2.0** - Sistema de Gestión Inmobiliaria Completo
