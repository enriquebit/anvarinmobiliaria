# 🌱 SEEDERS MANUALES - NUEVOANVAR

## 📋 Guía para Inserción Manual de Datos

### ⚠️ **Importante**
- Ejecutar en orden estricto para mantener integridad referencial
- Verificar cada inserción antes de continuar
- Password universal: `secret1234`

---

## 🔧 **PASO 1: USUARIOS Y AUTENTICACIÓN**

```sql
-- 1.1 Crear usuarios
INSERT INTO users (id, username, active, created_at, updated_at) VALUES
(2, 'admin', 1, NOW(), NOW()),
(3, 'vendedor1', 1, NOW(), NOW()),
(4, 'cliente1', 1, NOW(), NOW());

-- 1.2 Crear identidades de autenticación
INSERT INTO auth_identities (user_id, type, name, secret, secret2, created_at, updated_at) VALUES
(2, 'email_password', 'Admin Principal', 'admin@nuevoanvar.test', '$2y$12$68.hBOqeYtcV6zASZ.0UHOo43qW6f/eGICLUv.1SPTI1mWIxJ/b4y', NOW(), NOW()),
(3, 'email_password', 'Ana Patricia Vega', 'vendedor1@nuevoanvar.test', '$2y$12$68.hBOqeYtcV6zASZ.0UHOo43qW6f/eGICLUv.1SPTI1mWIxJ/b4y', NOW(), NOW()),
(4, 'email_password', 'Juan Carlos Mendoza', 'cliente1@nuevoanvar.test', '$2y$12$68.hBOqeYtcV6zASZ.0UHOo43qW6f/eGICLUv.1SPTI1mWIxJ/b4y', NOW(), NOW());

-- Verificar: SELECT COUNT(*) FROM users; (debe ser 4 con superadmin)
```

---

## 🏢 **PASO 2: EMPRESA**

```sql
-- 2.1 Crear empresa (usar DOMICILIO no direccion)
INSERT INTO empresas (id, nombre, razon_social, rfc, telefono, email, domicilio, activo, created_at, updated_at) VALUES
(1, 'NuevoAnvar', 'NuevoAnvar Inmobiliaria SA de CV', 'NAI123456789', '6691234567', 'contacto@nuevoanvar.com', 'Av. Principal 123, Mazatlán, Sinaloa', 1, NOW(), NOW());

-- Verificar: SELECT * FROM empresas;
```

---

## 🏗️ **PASO 3: PROYECTO**

```sql
-- 3.1 Crear proyecto (usar ESTATUS no activo)
INSERT INTO proyectos (id, nombre, clave, empresas_id, descripcion, direccion, estatus, created_at, updated_at) VALUES
(1, 'Valle Natura', 'VN01', 1, 'Desarrollo habitacional sustentable', 'Mazatlán, Sinaloa', 'activo', NOW(), NOW());

-- Verificar: SELECT p.*, e.nombre as empresa FROM proyectos p JOIN empresas e ON e.id = p.empresas_id;
```

---

## 👷 **PASO 4: STAFF**

```sql
-- 4.1 Crear staff vinculado a usuario
INSERT INTO staff (id, user_id, nombres, apellido_paterno, apellido_materno, email, telefono, agencia, tipo, activo, created_at, updated_at) VALUES
(1, 3, 'Ana Patricia', 'Vega', 'Morales', 'vendedor1@nuevoanvar.test', '5512345001', 'Sucursal Centro', 'vendedor', 1, NOW(), NOW());

-- Verificar: SELECT s.*, u.username FROM staff s JOIN users u ON u.id = s.user_id;
```

---

## 👤 **PASO 5: CLIENTE**

```sql
-- 5.1 Crear cliente vinculado a usuario
INSERT INTO clientes (id, user_id, nombres, apellido_paterno, apellido_materno, genero, persona_moral, email, telefono, estado_civil, fuente_informacion, etapa_proceso, fecha_primer_contacto, asesor_asignado, created_at, updated_at) VALUES
(1, 4, 'Juan Carlos', 'Mendoza', 'García', 'M', 0, 'cliente1@nuevoanvar.test', '5512345101', 'casado', 'referido', 'calificado', '2024-01-15 10:30:00', 1, NOW(), NOW());

-- Verificar: SELECT c.*, u.username FROM clientes c JOIN users u ON u.id = c.user_id;
```

---

## 📋 **PASO 6: CATÁLOGOS BÁSICOS**

```sql
-- 6.1 Estados civiles (nombre, valor, activo)
INSERT INTO estados_civiles (id, nombre, valor, activo) VALUES
(1, 'Soltero', 'soltero', 1),
(2, 'Casado', 'casado', 1),
(3, 'Unión Libre', 'union_libre', 1);

-- 6.2 Fuentes información (nombre, valor, activo)
INSERT INTO fuentes_informacion (id, nombre, valor, activo) VALUES
(1, 'Referido', 'referido', 1),
(2, 'Facebook', 'facebook', 1),
(3, 'Instagram', 'instagram', 1);

-- 6.3 Estados lotes (nombre, codigo, descripcion, color)
INSERT INTO estados_lotes (id, nombre, codigo, descripcion, color) VALUES
(1, 'Disponible', 1, 'Lote disponible para venta', '#28a745'),
(2, 'Apartado', 2, 'Lote apartado', '#ffc107'),
(3, 'Vendido', 3, 'Lote vendido', '#dc3545');

-- 6.4 Categorías lotes (nombre, descripcion)
INSERT INTO categorias_lotes (id, nombre, descripcion) VALUES
(1, 'Básico', 'Lote básico residencial'),
(2, 'Premium', 'Lote premium con amenidades'),
(3, 'Comercial', 'Lote para uso comercial');

-- 6.5 Tipos lotes (nombre, descripcion)
INSERT INTO tipos_lotes (id, nombre, descripcion) VALUES
(1, 'Residencial', 'Lote para casa habitación'),
(2, 'Comercial', 'Lote para negocio'),
(3, 'Industrial', 'Lote para industria');

-- Verificar catálogos:
-- SELECT COUNT(*) FROM estados_civiles;
-- SELECT COUNT(*) FROM fuentes_informacion;
-- SELECT COUNT(*) FROM estados_lotes;
-- SELECT COUNT(*) FROM categorias_lotes;
-- SELECT COUNT(*) FROM tipos_lotes;
```

---

## 🏘️ **PASO 7: MANZANA**

```sql
-- 7.1 Crear manzana (usar nombre y clave, NO numero)
INSERT INTO manzanas (id, nombre, clave, proyectos_id, activo, created_at, updated_at) VALUES
(1, 'Manzana 01', 'VN-M01', 1, 1, NOW(), NOW());

-- Verificar: SELECT m.*, p.nombre as proyecto FROM manzanas m JOIN proyectos p ON p.id = m.proyectos_id;
```

---

## 🏠 **PASO 8: LOTES** ⭐

```sql
-- 8.1 Crear lotes vinculados consistentemente
-- ⚠️ CRÍTICO: Usar IDs existentes (empresa=1, proyecto=1, manzana=1)
INSERT INTO lotes (id, numero, clave, empresas_id, proyectos_id, manzanas_id, categorias_lotes_id, tipos_lotes_id, estados_lotes_id, area, frente, fondo, precio_m2, precio_total, activo, created_at, updated_at) VALUES
(1, '01', 'VN-M01-L01', 1, 1, 1, 1, 1, 1, 200.00, 10.00, 20.00, 4500.00, 900000.00, 1, NOW(), NOW()),
(2, '02', 'VN-M01-L02', 1, 1, 1, 2, 1, 1, 250.00, 12.50, 20.00, 5500.00, 1375000.00, 1, NOW(), NOW()),
(3, '03', 'VN-M01-L03', 1, 1, 1, 1, 1, 1, 180.00, 9.00, 20.00, 4500.00, 810000.00, 1, NOW(), NOW());

-- Verificar relaciones completas:
SELECT 
    l.clave as lote,
    l.precio_total,
    e.nombre as empresa,
    p.nombre as proyecto,
    m.nombre as manzana,
    cl.nombre as categoria,
    tl.nombre as tipo,
    el.nombre as estado
FROM lotes l
JOIN empresas e ON e.id = l.empresas_id
JOIN proyectos p ON p.id = l.proyectos_id  
JOIN manzanas m ON m.id = l.manzanas_id
JOIN categorias_lotes cl ON cl.id = l.categorias_lotes_id
JOIN tipos_lotes tl ON tl.id = l.tipos_lotes_id
JOIN estados_lotes el ON el.id = l.estados_lotes_id;
```

---

## 💰 **PASO 9: PERFIL FINANCIAMIENTO**

```sql
-- 9.1 Crear perfil básico
INSERT INTO perfiles_financiamiento (id, empresa_id, nombre, descripcion, activo, created_at, updated_at) VALUES
(1, 1, 'Plan Básico', 'Plan básico de financiamiento 10% enganche', 1, NOW(), NOW());

-- Verificar: SELECT pf.*, e.nombre as empresa FROM perfiles_financiamiento pf JOIN empresas e ON e.id = pf.empresa_id;
```

---

## 📋 **PASO 10: APARTADO**

```sql
-- 10.1 Crear apartado vinculado
INSERT INTO apartados (id, folio_apartado, lote_id, cliente_id, user_id, tipo_plan_financiamiento_id, fecha_apartado, monto_apartado, monto_enganche_requerido, fecha_limite_enganche, forma_pago, referencia_pago, estatus_apartado, fecha_vencimiento, created_at, updated_at) VALUES
(1, 'AP-20240115-0001', 1, 1, 3, 1, '2024-01-15 10:30:00', 5000.00, 90000.00, '2024-04-15', 'deposito', 'DEP123456', 'vigente', '2024-04-15 23:59:59', '2024-01-15 10:30:00', '2024-01-15 10:30:00');

-- 10.2 Actualizar estado del lote
UPDATE lotes SET estados_lotes_id = 2 WHERE id = 1;

-- Verificar: 
SELECT 
    a.folio_apartado,
    l.clave as lote,
    CONCAT(c.nombres, ' ', c.apellido_paterno) as cliente,
    a.monto_apartado,
    a.estatus_apartado
FROM apartados a
JOIN lotes l ON l.id = a.lote_id
JOIN clientes c ON c.id = a.cliente_id;
```

---

## 💵 **PASO 11: INGRESOS**

```sql
-- 11.1 Crear ingresos vinculados
INSERT INTO ingresos (id, folio, tipo_ingreso, monto, fecha_ingreso, metodo_pago, referencia, cliente_id, apartado_id, user_id) VALUES
(1, 'ING-20240115-001', 'apartado', 5000.00, '2024-01-15 10:30:00', 'deposito', 'DEP123456', 1, 1, 3),
(2, 'ING-20240215-002', 'abono_enganche', 20000.00, '2024-02-15 11:00:00', 'efectivo', NULL, 1, 1, 3),
(3, 'ING-20240315-003', 'abono_enganche', 25000.00, '2024-03-15 14:30:00', 'cheque', 'CHQ456789', 1, 1, 3);

-- Verificar:
SELECT 
    i.folio,
    i.tipo_ingreso,
    i.monto,
    a.folio_apartado,
    CONCAT(c.nombres, ' ', c.apellido_paterno) as cliente
FROM ingresos i
JOIN apartados a ON a.id = i.apartado_id
JOIN clientes c ON c.id = i.cliente_id
ORDER BY i.fecha_ingreso;
```

---

## ✅ **VERIFICACIÓN FINAL**

```sql
-- Conteo de registros
SELECT 'RESUMEN FINAL:' as '';
SELECT CONCAT('👥 Usuarios: ', COUNT(*)) as datos FROM users
UNION SELECT CONCAT('🏢 Empresas: ', COUNT(*)) FROM empresas
UNION SELECT CONCAT('🏗️ Proyectos: ', COUNT(*)) FROM proyectos
UNION SELECT CONCAT('🏘️ Manzanas: ', COUNT(*)) FROM manzanas
UNION SELECT CONCAT('🏠 Lotes: ', COUNT(*)) FROM lotes
UNION SELECT CONCAT('📋 Apartados: ', COUNT(*)) FROM apartados
UNION SELECT CONCAT('💰 Ingresos: ', COUNT(*)) FROM ingresos;

-- Verificar que los lotes sean visibles
SELECT 'LOTES VISIBLES:' as '';
SELECT 
    l.id,
    l.clave,
    l.precio_total,
    e.nombre as empresa,
    p.nombre as proyecto,
    m.nombre as manzana,
    el.nombre as estado
FROM lotes l
JOIN empresas e ON e.id = l.empresas_id
JOIN proyectos p ON p.id = l.proyectos_id
JOIN manzanas m ON m.id = l.manzanas_id
JOIN estados_lotes el ON el.id = l.estados_lotes_id
ORDER BY l.id;
```

---

## 🔑 **CREDENCIALES DE ACCESO**

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| **Superadmin** | `superadmin@nuevoanvar.test` | `secret1234` | Super Administrador |
| **Admin** | `admin@nuevoanvar.test` | `secret1234` | Administrador |
| **Vendedor** | `vendedor1@nuevoanvar.test` | `secret1234` | Vendedor |
| **Cliente** | `cliente1@nuevoanvar.test` | `secret1234` | Cliente |

---

## 🎯 **NOTAS IMPORTANTES**

1. **Orden de Inserción**: Seguir estrictamente el orden para evitar errores de foreign key
2. **Verificaciones**: Ejecutar las consultas de verificación después de cada paso
3. **Lotes Visibles**: Los lotes solo serán visibles si tienen relaciones correctas con empresa, proyecto y manzana
4. **IDs Consistentes**: Usar siempre los mismos IDs (empresa=1, proyecto=1, manzana=1)
5. **Estructuras Reales**: Este seeder usa las estructuras reales verificadas de tu BD

---

*📅 Generado: ${new Date().toLocaleDateString('es-ES')}*  
*🤖 Por: Claude Code Assistant*