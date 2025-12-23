# 📊 Base de Datos - Task Manager Jira/ClickUp

Este directorio contiene las migraciones, seeders y factories de la base de datos.

---

## 📁 Estructura

```
database/
├── database.sqlite          # Base de datos SQLite (desarrollo)
├── factories/              # Factories para generar datos de prueba
├── migrations/             # Migraciones de la base de datos
└── seeders/                # Seeders para poblar la base de datos
```

---

## 🗄️ Estado Actual

- **Conexión**: SQLite (por defecto)
- **Migraciones ejecutadas**: ✅ 18 migraciones
- **Tablas creadas**: 23 tablas

---

## 📋 Comandos Útiles

### Ver estado de migraciones
```bash
php artisan migrate:status
```

### Ejecutar migraciones
```bash
php artisan migrate
```

### Revertir última migración
```bash
php artisan migrate:rollback
```

### Revertir todas las migraciones
```bash
php artisan migrate:reset
```

### Refrescar base de datos (eliminar y recrear)
```bash
php artisan migrate:fresh
```

### Refrescar y ejecutar seeders
```bash
php artisan migrate:fresh --seed
```

### Ver información de la base de datos
```bash
php artisan db:show
```

### Ver estructura de una tabla
```bash
php artisan db:table nombre_tabla --show
```

---

## 🔍 Verificar Integridad

### Verificar Foreign Keys

En SQLite:
```sql
PRAGMA foreign_key_check;
```

En MySQL:
```sql
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    REFERENCED_TABLE_SCHEMA = 'task_manager_jira'
    AND REFERENCED_TABLE_NAME IS NOT NULL;
```

---

## 📝 Notas

- Las tablas con UUIDs: `projects`, `tasks`, `comments`, `labels`
- Todas las tablas principales tienen `team_id` para multi-tenancy
- Soft deletes están habilitados en: `projects`, `tasks`, `comments`, `labels`

---

**Última actualización**: 2025-12-23

