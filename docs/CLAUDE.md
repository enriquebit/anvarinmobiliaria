# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 🎯 PROJECT OVERVIEW
***Avoid over-engineering initially***
**NuevoAnvar - Dual Real Estate System Migration Project**

This repository contains a comprehensive real estate management system migration from a legacy PHP system to a modern CodeIgniter 4 architecture. The project manages two distinct codebases for analysis and migration planning:



- **Legacy System**: `./administracion/` - PHP7 procedural codebase (~300 files) 
- **Modern System**: `./` - CodeIgniter 4 + Shield implementation

### Legacy System (READ-ONLY for analysis)
- **Location**: `./administracion/` 
- **Status**: Legacy system with PHP7 procedural code (~300 files)
- **Permission**: READ-ONLY for investigation and understanding
- **CRITICAL**: You may ANALYZE and INVESTIGATE this folder to understand legacy logic, but NEVER modify, edit, or create files within `./administracion/`

### Language Requirements
- **All responses must be provided in Latin American Spanish**
- **Code comments should be in Spanish**
- **Documentation and explanations in Spanish**

## 🏗️ DEVELOPMENT ENVIRONMENT

### Technology Stack
- **Backend**: CodeIgniter 4.x + CodeIgniter Shield (authentication)
- **Frontend**: AdminLTE 3.x + jQuery + Bootstrap 4
- **Database**: MySQL/MariaDB with 19 implemented tables
- **Architecture**: Entity-First MVC with Repository pattern via CI4 Models
- **Development Approach**: MVP-First methodology with incremental modules

### Local Development Setup
```bash
# Access URLs  
Modern CI4 System: http://nuevoanvar.test
Legacy System: ./sistema_legacy_anvarinmobiliaria.com/login.php

# Key Commands
php spark serve                    # Development server (NOT recommended - use Apache/Nginx)
php spark migrate                  # Run database migrations  
php spark migrate:rollback         # Rollback migrations
php spark shield:setup            # Setup Shield authentication
```

## 📊 CURRENT SYSTEM STATUS

### ✅ Implemented Modules (CI4 System)
- **Authentication**: Shield-based with 7 roles (superadmin, admin, vendedor, cliente, etc.)
- **Clients Management**: Complete CRUD with addresses, references, work info, spouse data
- **Companies**: Basic data + financial configuration
- **Projects**: Full CRUD with geolocation, documents, company relationships ⭐
- **Staff**: Employee management with internal roles
- **Catalogs**: Civil status, information sources

### 🚧 Pending Migration Modules
1. **Properties** (lotes, casas, departamentos) - From legacy `tb_lotes`
2. **Sales** (complete pipeline) - From legacy `tb_ventas` 
3. **Payments** (receipts, plans) - From legacy `tb_cobranza`
4. **Advisors** (commissions) - From legacy commission system
5. **Reports** (dashboards) - From legacy `reporte_*.php` files

## 🔧 CRITICAL HELPERS SYSTEM

The CI4 system implements a comprehensive DRY (Don't Repeat Yourself) approach through helpers:

### Authentication Helpers (`app/Helpers/auth_helper.php`)
```php
userName()                  // Get current user name
userRole()                  // Get user role ('admin', 'cliente', etc.)
isAdmin()                   // Boolean admin check
isSuperAdmin()              // Boolean superadmin check
hasPermission($permission)  // Check specific permission
```

### Format Helpers (`app/Helpers/format_helper.php`)
```php
formatPrecio($precio)       // Format currency: $1,234.56 MXN
formatTelefono($telefono)   // Format phone: (55) 1234-5678
formatFecha($fecha)         // Format date: 01/12/2024
formatSuperficie($metros)   // Format area: 150.50 m²
```

### Real Estate Helpers (`app/Helpers/inmobiliario_helper.php`)
```php
badgeEstadoPropiedad($estado)    // HTML badge for property status
iconoTipoPropiedad($tipo)        // HTML icon for property type
calcularComision($venta, $rate)  // Calculate commission amount
generarClavePropiedad()          // Generate unique property keys
```

## 📁 PROJECT STRUCTURE

```
/mnt/c/laragon/www/nuevoanvar/
├── administracion/  # Legacy PHP system for analysis  
│   ├── login.php                         # Entry point
│   ├── comandos/funciones.php            # Main business logic (AJAX)
│   ├── database_schema.sql               # Legacy database structure
│   ├── ventas.php, cobranza.php          # Core modules
│   └── [300+ PHP files]
│
├── ./ # Modern CI4 system
│   ├── app/
│   │   ├── Controllers/Admin/            # Admin controllers
│   │   ├── Models/                       # Data layer with returnType Entity
│   │   ├── Entities/                     # Business logic objects
│   │   ├── Helpers/                      # DRY function libraries
│   │   ├── Views/admin/                  # AdminLTE views
│   │   └── Database/Migrations/          # Incremental schema evolution
│   ├── public/assets/                    # AdminLTE + plugins
│   └── writable/uploads/                 # File storage
├── docs/ # Modern CI4 system
│   ├── ANALISIS_SISTEMA_LEGACY.md            # Legacy system analysis
│   ├── EVALUACION_BASE_CI4.md                # CI4 system evaluation  
│   ├── ROADMAP_MIGRACION_FUSIONADA.md        # Migration roadmap
│   ├── EXPLICACION.md                        # Legacy system flow explanation
│   └── DOCUMENTACION_IMPORTANTE.md           # Analysis instructions
```

## 🎯 MVP-FIRST METHODOLOGY

### Core Development Principles
1. **FUNCIONA PRIMERO** - Working functionality over perfect architecture
2. **DRY with Helpers** - Avoid code duplication through helper functions
3. **Basic CRUD** - Simple operations without complex validations initially
4. **AdminLTE Consistency** - Professional UI using established patterns
5. **Incremental Modules** - One module at a time, complete before moving

### Development Priorities
- **Functionality** > Perfect architecture
- **Speed** > Elegant code
- **Reusability** > Code duplication  
- **User Satisfaction** > Developer purism

## 📊 DATABASE ARCHITECTURE

### Current CI4 Database (19 Tables)
```sql
-- Authentication (Shield) - 6 tables
users, auth_identities, auth_groups_users, auth_remember_tokens, 
auth_permissions_users, auth_logins

-- Client Management - 6 tables
clientes, direcciones_clientes, referencias_clientes, 
informacion_laboral_clientes, informacion_conyuge_clientes, documentos_clientes

-- Business Core - 7 tables
empresas, staff, proyectos, documentos_proyecto, 
estados_civiles, fuentes_informacion, migrations
```

### Legacy Database Key Tables (for Migration)
```sql
-- Core business tables to migrate
tb_usuarios          -> users + auth_* tables
tb_clientes          -> clientes + related tables
tb_proyectos         -> proyectos
tb_lotes             -> propiedades (future)
tb_ventas            -> ventas (future)
tb_cobranza          -> pagos (future)
tb_manzanas          -> manzanas (future)
```

## 🚀 COMMON DEVELOPMENT WORKFLOWS

### Creating a New Module
```bash
"Create [MODULE_NAME] module for NuevoAnvar:
- Migration with timestamps
- Model with returnType Entity
- Entity with basic helpers
- Admin Controller with CRUD
- AdminLTE views
- Use existing helpers
- Routes + sidebar integration  
- MVP functional in Spanish"
```

### Creating Helper Functions
```bash
"Create [name]_helper.php:
- DRY functions for [domain]
- function_exists() wrapper
- PHPDoc in Spanish
- Reusability focus
- Usage examples"
```

### Database Migration Workflow
```bash
php spark make:migration CreateTableName    # Create migration
php spark migrate                          # Apply migration
php spark migrate:rollback                 # Rollback if needed
php spark db:seed SuperAdminSeeder         # Run seeder for admin user
```

### Testing Workflow
```bash
# Manual testing approach (current)
/debug/conexion     # Test database connection
/debug/auth         # Test authentication
/debug/login        # Test login system

# Automated testing (PHPUnit configured)
composer test       # Run PHPUnit test suite
php spark test      # Alternative test command
vendor/bin/phpunit  # Direct PHPUnit execution
```
 
## ⚠️ CRITICAL DEVELOPMENT RULES

### 🔥 Mandatory for MVP
1. **Functionality First** - Make it work before making it perfect
2. **Use Helpers** - Apply DRY principles consistently
3. **Basic CRUD** - Avoid over-engineering initially
4. **AdminLTE Standard** - Maintain consistent UI patterns

### 🛡️ Security Considerations
- CodeIgniter Shield handles authentication/authorization
- Input validation through CI4 validation rules
- Foreign key constraints maintain data integrity
- Role-based access control via filters

### 🚫 What NOT to Do
- Don't use `php spark serve` for production
- Don't create custom authentication (use Shield)
- Don't repeat code (use helpers)
- Don't skip migrations (version control schema changes)

## 🔧 WORKING WITH CI4 SYSTEM

### Daily Development Commands
```bash
# Navigate to CI4 system
cd sistema_actual_anvarinmobiliaria.com/

# Start development server (if not using Apache/Nginx)
php spark serve

# Database operations
php spark migrate                    # Apply pending migrations
php spark migrate:refresh            # Refresh all migrations
php spark shield:setup              # Configure Shield if needed

# Cache operations
php spark cache:clear               # Clear application cache

# Development helpers
php spark list                     # Show all available commands
php spark routes                   # Display application routes
```

### Entity-First Development Pattern
```php
// 1. Create Migration
php spark make:migration CreatePropiedadesTable

// 2. Create Model with Entity return type
class PropiedadModel extends Model {
    protected $returnType = 'App\Entities\Propiedad';
}

// 3. Create Entity with business logic
class Propiedad extends Entity {
    public function isDisponible(): bool { /* business logic */ }
}

// 4. Create Controller with CRUD operations
class AdminPropiedadesController extends BaseController {
    // Uses helpers, entities, and AdminLTE views
}
```

## 🔄 LEGACY SYSTEM MIGRATION CONTEXT

### Legacy System Characteristics
- **Entry Point**: `login.php` with session-based authentication
- **Architecture**: Procedural PHP with direct SQL queries
- **Business Logic**: Concentrated in `comandos/funciones.php` via AJAX
- **Security Issues**: SQL injection vulnerabilities, weak input sanitization
- **Data Structure**: Flat tables with minimal foreign key relationships

### Key Migration Challenges
1. **Data Integrity**: Legacy system lacks proper foreign key constraints
2. **Authentication**: Convert session-based auth to Shield
3. **Business Logic**: Extract and refactor procedural code to Entity/Model methods
4. **Security**: Address SQL injection and input validation issues
5. **UI Modernization**: Convert legacy HTML to AdminLTE components

### Migration Strategy
- **Phase 1**: Data structure migration and integrity establishment
- **Phase 2**: Business logic extraction and modernization
- **Phase 3**: Feature parity achievement with enhanced security

## 🎯 DEVELOPMENT MANTRA

**"FUNCIONA + DRY + MVP = ÉXITO"**
**("WORKS + DRY + MVP = SUCCESS")**

### For Claude Code Context
- **Current State**: Real estate system IN CONSTRUCTION
- **Focus**: MVP functional approach with helpers
- **Scalability**: Think about future modules
- **Documentation**: Update when adding components

**Make it work first, reuse with helpers, optimize later! 🚀**