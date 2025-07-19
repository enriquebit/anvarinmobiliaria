# 🌱 SEEDERS DATABASE - NUEVOANVAR

## 📋 Credenciales de Usuarios Creados

### 👨‍💼 **ADMINISTRADORES**
| Email | Password | Nombre | Rol |
|-------|----------|--------|-----|
| `admin@nuevoanvar.test` | `secret1234` | Carlos Enrique Admin | Administrador Principal |
| `gerencia@nuevoanvar.test` | `secret1234` | María Fernanda Gerente | Gerente General |
| `sistemas@nuevoanvar.test` | `secret1234` | Luis Alberto Sistemas | Administrador Sistemas |

### 🏢 **STAFF VENDEDORES** 
| Email | Password | Nombre | Tipo | Agencia |
|-------|----------|--------|------|---------|
| `vendedor1@nuevoanvar.test` | `secret1234` | Ana Patricia Vega | Vendedor | Sucursal Centro |
| `vendedor2@nuevoanvar.test` | `secret1234` | Roberto Carlos Díaz | Vendedor | Sucursal Norte |
| `vendedor3@nuevoanvar.test` | `secret1234` | Carmen Elena López | Vendedor | Sucursal Sur |
| `vendedor4@nuevoanvar.test` | `secret1234` | Javier Alejandro Ruiz | Vendedor | Sucursal Centro |
| `vendedor5@nuevoanvar.test` | `secret1234` | Sofía Isabel Morales | Vendedor | Sucursal Norte |
| `asesor1@nuevoanvar.test` | `secret1234` | Miguel Ángel Torres | Asesor | Sucursal Centro |
| `asesor2@nuevoanvar.test` | `secret1234` | Gabriela Andrea Silva | Asesor | Sucursal Sur |

### 👥 **CLIENTES DE PRUEBA**
| Email | Password | Nombre | Tipo | Status |
|-------|----------|--------|------|--------|
| `cliente1@nuevoanvar.test` | `secret1234` | Juan Carlos Mendoza García | Persona Física | Activo |
| `cliente2@nuevoanvar.test` | `secret1234` | María Guadalupe Hernández Ruiz | Persona Física | Activo |
| `cliente3@nuevoanvar.test` | `secret1234` | Pedro Antonio Ramírez López | Persona Física | Activo |
| `cliente4@nuevoanvar.test` | `secret1234` | Rosa Elena Jiménez Sánchez | Persona Física | Activo |
| `cliente5@nuevoanvar.test` | `secret1234` | Ricardo Alberto Flores Vargas | Persona Física | Activo |
| `cliente6@nuevoanvar.test` | `secret1234` | Constructora ABC SA de CV | Persona Moral | Activo |
| `cliente7@nuevoanvar.test` | `secret1234` | Inmobiliaria XYZ Ltda | Persona Moral | Activo |
| `cliente8@nuevoanvar.test` | `secret1234` | Eduardo Francisco Peña Ortiz | Persona Física | Activo |
| `cliente9@nuevoanvar.test` | `secret1234` | Luz María Castro Delgado | Persona Física | Activo |
| `cliente10@nuevoanvar.test` | `secret1234` | Diego Armando Villa Real | Persona Física | Activo |

---

## 🏗️ **DATOS DE PROYECTOS GENERADOS**

### 📈 **Proyectos Inmobiliarios**
1. **Valle Natura** (V1) - Mazatlán
2. **CORDELIA** (C1) - Seminario
3. **Residencial Los Pinos** (RLP) - Zona Norte
4. **Fraccionamiento El Mirador** (FEM) - Zona Centro
5. **Villas del Sol** (VDS) - Zona Sur

### 🏠 **Tipos de Lotes**
- **Residencial Habitacional** (150-300 m²)
- **Residencial Premium** (301-500 m²)
- **Comercial** (100-250 m²)
- **Industrial** (500-1000 m²)

### 💰 **Rangos de Precios** (por m²)
- **Habitacional**: $3,500 - $5,500 MXN
- **Premium**: $6,000 - $8,500 MXN
- **Comercial**: $4,500 - $7,000 MXN
- **Industrial**: $2,800 - $4,200 MXN

---

## 💼 **PERFILES FINANCIEROS CREADOS**

### 🏦 **Planes de Financiamiento**
1. **Plan Básico** - 10% enganche, 60 meses
2. **Plan Premium** - 20% enganche, 48 meses
3. **Plan Comercial** - 30% enganche, 36 meses
4. **Plan Cero Enganche** - 0% enganche, 72 meses
5. **Plan Express** - 50% enganche, 24 meses

### 📊 **Configuraciones de Comisiones**
- **Vendedor Nuevo**: 3% sobre venta
- **Vendedor Experimentado**: 5% sobre venta
- **Asesor Senior**: 7% sobre venta
- **Gerente de Ventas**: 2% adicional por supervisión

---

## 📋 **DATOS ESTADÍSTICOS GENERADOS**

### 📈 **Volumen de Transacciones**
- **Apartados**: 45 registros
- **Enganches**: 28 registros
- **Mensualidades**: 156 registros
- **Ingresos Totales**: 229 registros
- **Comisiones**: 73 registros

### ⏰ **Período de Datos**
- **Desde**: Enero 2024
- **Hasta**: Diciembre 2024
- **Distribución**: Datos distribuidos uniformemente

### 🎯 **Casos de Prueba Incluidos**
- ✅ Apartados convertidos a enganches
- ✅ Apartados vencidos y penalizados
- ✅ Ventas con cero enganche
- ✅ Planes de financiamiento variados
- ✅ Comisiones en diferentes estados
- ✅ Clientes con múltiples operaciones
- ✅ Filtros por rangos de montos
- ✅ Diferentes formas de pago

---

## 🔧 **COMANDOS DE EJECUCIÓN**

```bash
# Limpiar base de datos
php spark db:reset

# Ejecutar seeder principal
php spark db:seed MainSeeder

# O ejecutar seeders individuales
php spark db:seed UserSeeder
php spark db:seed ClienteSeeder
php spark db:seed ProyectoSeeder
php spark db:seed LoteSeeder
php spark db:seed TransaccionSeeder
```

---

## ⚠️ **NOTAS IMPORTANTES**

1. **Passwords**: Todos los usuarios tienen password `secret1234`
2. **Dominio**: Todos los emails usan `@nuevoanvar.test`
3. **Datos**: Son completamente ficticios para testing
4. **Reset**: La base se limpia completamente antes del seeding
5. **Relaciones**: Todas las FK están correctamente relacionadas
6. **Estados**: Incluye todos los posibles estados de transacciones

---

*📅 Generado automáticamente el: <?= date('d/m/Y H:i:s') ?>*
*🤖 Por: Claude Code Assistant*