## 🚀 MEMORIAS DE DESARROLLO
Visita la documentación 
### ✅ Documentación Generada
- [x] `./docs/*.md`
- [x] `./docs/EVALUACION_BASE_CI4.md`  
- [x] `./docs/CLAUDE.md` - Guía de desarrollo
- [x] `./docs/EXPLICACION.md` - Flujo sistema legacy

BD legacy: database_legacy
BD actual: nuevoanvar_vacio
## Tablas importantes de la base de datos ##
clientes [columnas importantes , "nombres", "apellido_paterno", "apellido_materno"]
staff
ventas
users
Autenticación Shield v1.1 
auth_identities -> aqui se almacenan la credencial email en columna "secret" pass: "secret2"

### Optimización de Vista Edit
-No realizar caché automático en desarrollo
- El caché automático está causando conflictos en entorno de desarrollo
- Metodología de desarrollo Entity-First
- No utilizar over-engineering
- Diseño de UI/UX minimalista cond adminLTE v3.2
### Depuración de Inserción y Consulta de Datos
- Resolución de problemas de inserción y consulta de datos utilizando `var_dump()` para debug
- Uso de `var_dump()` para inspeccionar variables y rastrear problemas de flujo de datos en base de datos

### Consideraciones de Bases de Datos Legacy
- La tabla legacy tiene muchos campos desnormalizados (NProyecto, NUsuario, NEmpresa)
- TODO LO QUE SE ANTEPONGA "N" es sinónimo de Nombre ejemplo "NombreProyecto", NombreUsuario(nosotros no ocupamos  usuario, es el email, Nombre de Empresa)
- Índices omitidos para desarrollo 
- Relajar valdiaciones y restricciones

### Errores de Configuración Shield
- Error de tabla 'auth_groups' no existente en sistema Shield v1.1
- En Shield v1.1 los permisos se gestionan en "./app/Config/AuthGroups.php" y no en las tablas
- Las consultas de usuarios con grupos deben adaptarse a la configuración de Shield v1.1

### Consideraciones de Vistas
- Antes de realizar vistas o actualizar, debemos saber que "doble wrapper" en el caso de las vistas adminLTE causa layout roto

### Migraciones de Base de Datos
- Recuerda que no podemos realizar migraciones porque la base de datos esta sincronizada, para ello debemos generar el script
- NO hacer uso de triggers o transactions a nivel de bae de datos ya que tenemos permisos limitados unacamente esas dos cosas
### Gestión de Documentos
- Recuerda siempre utilizar fragmentación de documentos de tipo offset para lectura cuando el contenido de los archivos excedan el máximo permitido de 24500, deberemos usar offset y limitar parámetros fragmentados para leer y buscar información específica
- Antes de crear un nuevo documento o sql, verifica si ya existe algún metodo igual o similar que nos ayude a evitar e implementar la metodología "Don't Repeat Yourself".

### Consideraciones de Migración y Transformación
- No debe tener datos restaurados aún, no debemos restaurar datos
- Estamos transformando la lógica de programación procedural PHP 7.4 hacia un nuevo framework CodeIgniter 4 con autenticación "Shield v1.1"
- Metodología de desarrollo: "Entity-First", "MVP", "DRY"
- Es mas eficiente hacer librerías y funciones globale de uso común globales que repetir archivos [helpers, utilities, comandos]
### ⚠️ POLÍTICA DE VALIDACIONES EN DESARROLLO
- **NO utilizar muchas restricciones en campos durante desarrollo**
- **Relajar las validaciones para evitar errores en los inputs**
- **Prioridad es el flujo de datos, no la seguridad**
- **Utilizar metodos y nomenclatura convencial por codeigniter4 adminLTE JS, Datatables**
- **Implementar seguridad progresivamente**
- Permitir campos vacíos o con valores por defecto
- Validaciones mínimas solo para evitar errores críticos
- Focus en funcionalidad antes que validación estricta

### Metodología de Desarrollo Adicional
- Menor curva de desarrollo - Usando métodos existentes
- Principio DRY - No repetir código que ya existía
- MVP funcional - Funcionando sin over-engineering
- Entity-First - Aprovechando Models Controllers Services Helpers y Entities existentes
- Pedir tablas de base de datos actuales para no asumir nuevos campos de manera automática

### Credenciales de Base de Datos
- Usuario de MySQL: "root"
- Contraseña de MySQL: "0191"

### Memoria de Cálculos
- Memoriza los cálculos anteriores que generaron la tabla de amortización y los cálculos correctos

### Desarrollo de Helpers y Gestión de Permisos
- Utilizar helpers en la medida de lo posible para reutilizar funciones que puedan ser muy utilizadas en toda la vida del software como envio de emails, generacion de recibos, calculos, matemáticos, administración de archivos, etc lo que se considere necesario
- Utilizar Filters para el uso de permisos granulares en la medida de lo posible

# important-instruction-reminders
NEVER create files unless they're absolutely necessary for achieving your goal.
ALWAYS prefer editing an existing file to creating a new one.
NEVER proactively create documentation files (*.md) or README files. Only create documentation files if explicitly requested by the User.

#Hacer debug en el controlador#
@app/Controllers/Debug