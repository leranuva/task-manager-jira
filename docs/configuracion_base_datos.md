# 🗄️ Configuración de Base de Datos

**Última actualización**: 2025-12-23

---

## 📋 Estado Actual

- **Base de datos actual**: SQLite
- **Ubicación**: `database/database.sqlite`
- **Migraciones ejecutadas**: ✅ Todas (18 migraciones)
- **Tablas creadas**: 23 tablas

---

## 🔧 Configuración para MySQL/MariaDB (XAMPP)

### Paso 1: Crear la Base de Datos en MySQL

1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Crea una nueva base de datos llamada `task_manager_jira`
3. Selecciona el collation: `utf8mb4_unicode_ci`

O ejecuta este comando SQL:

```sql
CREATE DATABASE task_manager_jira CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Paso 2: Configurar el archivo .env

Edita el archivo `.env` en la raíz del proyecto:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager_jira
DB_USERNAME=root
DB_PASSWORD=
```

**Nota**: Si tu MySQL tiene contraseña, configúrala en `DB_PASSWORD`.

### Paso 3: Ejecutar las Migraciones

```bash
php artisan migrate:fresh
```

O si quieres mantener los datos existentes:

```bash
php artisan migrate
```

---

## 🔧 Configuración para PostgreSQL

### Paso 1: Crear la Base de Datos

```sql
CREATE DATABASE task_manager_jira;
```

### Paso 2: Configurar .env

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=task_manager_jira
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### Paso 3: Ejecutar Migraciones

```bash
php artisan migrate:fresh
```

---

## 🔧 Mantener SQLite (Desarrollo)

Si prefieres mantener SQLite para desarrollo:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

**Ventajas de SQLite para desarrollo:**
- No requiere servidor de base de datos
- Archivo único, fácil de respaldar
- Rápido para desarrollo

**Desventajas:**
- No soporta algunas características avanzadas
- Menos adecuado para producción

---

## 📊 Estructura de Tablas

### Tablas del Sistema (Jetstream)
- `users` - Usuarios
- `teams` - Equipos
- `team_user` - Relación usuario-equipo
- `team_invitations` - Invitaciones a equipos
- `personal_access_tokens` - Tokens API
- `password_reset_tokens` - Tokens de recuperación
- `sessions` - Sesiones de usuario

### Tablas de RBAC
- `roles` - Roles del sistema
- `permissions` - Permisos
- `role_permission` - Relación rol-permiso
- `user_role` - Relación usuario-rol-equipo

### Tablas de Negocio
- `projects` - Proyectos
- `tasks` - Tareas
- `comments` - Comentarios (polimórfico)
- `labels` - Etiquetas
- `task_label` - Relación tarea-etiqueta
- `task_assignments` - Asignaciones de tareas

### Tablas del Sistema Laravel
- `migrations` - Control de migraciones
- `cache` - Cache
- `cache_locks` - Locks de cache
- `jobs` - Cola de trabajos
- `job_batches` - Lotes de trabajos
- `failed_jobs` - Trabajos fallidos

---

## 🔍 Verificar Configuración

### Ver estado de la base de datos

```bash
php artisan db:show
```

### Ver estado de migraciones

```bash
php artisan migrate:status
```

### Probar conexión

```bash
php artisan tinker
```

Luego en tinker:
```php
DB::connection()->getPdo();
// Debe mostrar información de la conexión sin errores
```

---

## ⚙️ Configuración Avanzada

### Configurar Charset y Collation

En `config/database.php` puedes ajustar:

```php
'charset' => env('DB_CHARSET', 'utf8mb4'),
'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
```

### Configurar Pool de Conexiones

Para producción, puedes configurar pool de conexiones:

```php
'mysql' => [
    // ... otras configuraciones
    'options' => [
        PDO::ATTR_PERSISTENT => true,
    ],
],
```

### Configurar Timeout

```php
'options' => [
    PDO::ATTR_TIMEOUT => 5,
],
```

---

## 🔒 Seguridad

### Recomendaciones para Producción

1. **Usa credenciales seguras**: No uses `root` sin contraseña
2. **Crea un usuario específico**: Con permisos limitados
3. **Habilita SSL**: Si es posible
4. **Backups regulares**: Configura backups automáticos
5. **Variables de entorno**: Nunca commitees el `.env`

### Crear Usuario MySQL con Permisos Limitados

```sql
CREATE USER 'task_manager_user'@'localhost' IDENTIFIED BY 'contraseña_segura';
GRANT ALL PRIVILEGES ON task_manager_jira.* TO 'task_manager_user'@'localhost';
FLUSH PRIVILEGES;
```

Luego en `.env`:
```env
DB_USERNAME=task_manager_user
DB_PASSWORD=contraseña_segura
```

---

## 📝 Notas Importantes

### UUIDs y MySQL

Las tablas que usan UUIDs (Projects, Tasks, Comments, Labels) funcionan correctamente con MySQL. El trait `HasUuids` genera UUIDs automáticamente.

### Foreign Keys

Todas las foreign keys están configuradas con `onDelete('cascade')` o `onDelete('set null')` según corresponda.

### Índices

Las tablas tienen índices optimizados para:
- Búsquedas por `team_id` (multi-tenancy)
- Búsquedas por `status` y `priority` en tasks
- Búsquedas por `project_id`
- Relaciones polimórficas en comments

---

## 🐛 Solución de Problemas

### Error: "SQLSTATE[HY000] [2002] No connection could be made"

**Solución**: Verifica que MySQL esté corriendo en XAMPP.

### Error: "Access denied for user"

**Solución**: Verifica las credenciales en `.env`.

### Error: "Unknown database"

**Solución**: Crea la base de datos primero.

### Error: "Table already exists"

**Solución**: 
```bash
php artisan migrate:fresh
# O
php artisan migrate:rollback
php artisan migrate
```

---

## 📚 Referencias

- [Laravel Database Configuration](https://laravel.com/docs/database)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [SQLite Documentation](https://www.sqlite.org/docs.html)

---

**Última actualización**: 2025-12-23

