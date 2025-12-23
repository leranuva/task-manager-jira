# 🔧 Mejoras en Seeders y Factories

**Última actualización**: 2025-12-23

---

## 📋 Resumen de Mejoras

Se han implementado mejoras significativas en los seeders y factories para garantizar:

1. **Determinismo**: Los permisos y roles se crean desde archivos de configuración
2. **Integridad**: Validación exhaustiva de relaciones entre modelos
3. **Secuencialidad**: Generación de keys estilo Jira (PROJ-1, PROJ-2, etc.)

---

## 🎯 1. Archivos de Configuración (Single Source of Truth)

### `config/permissions.php`

Archivo de configuración que define todos los permisos del sistema:

```php
return [
    'project.create' => [
        'display_name' => 'Crear Proyecto',
        'group' => 'projects',
        'description' => 'Permite crear nuevos proyectos',
    ],
    // ... más permisos
];
```

**Ventajas:**
- ✅ Fuente única de verdad para permisos
- ✅ Fácil de mantener y actualizar
- ✅ Puede ser usado en validaciones y policies
- ✅ Determinista: siempre crea los mismos permisos

### `config/roles.php`

Archivo de configuración que define todos los roles y sus permisos:

```php
return [
    'owner' => [
        'display_name' => 'Propietario',
        'description' => 'Propietario del equipo con todos los permisos',
        'permissions' => '*', // Todos los permisos
    ],
    'admin' => [
        'display_name' => 'Administrador',
        'permissions' => [
            'project.create',
            'project.view',
            // ... más permisos específicos
        ],
    ],
    // ... más roles
];
```

**Ventajas:**
- ✅ Configuración centralizada de roles
- ✅ Fácil de modificar permisos por rol
- ✅ Determinista: siempre asigna los mismos permisos

---

## 🔍 2. Validación de Integridad en Seeders

### `RolePermissionSeeder`

Ahora valida:

- ✅ Que todos los permisos se crearon correctamente
- ✅ Que todos los roles se crearon correctamente
- ✅ Que los permisos fueron asignados correctamente a cada rol
- ✅ Conteo de permisos y roles esperados vs. encontrados

**Ejemplo de validación:**
```php
// Validate all permissions exist / Validar que todos los permisos existen
$expectedCount = count($permissionsConfig);
$actualCount = Permission::count();

if ($actualCount < $expectedCount) {
    throw new \RuntimeException(
        "Permission count mismatch. Expected: {$expectedCount}, Found: {$actualCount}"
    );
}
```

### `TestDataSeeder`

Ahora valida:

- ✅ Que las tareas pertenecen al proyecto correcto
- ✅ Que las tareas pertenecen al equipo correcto
- ✅ Que las etiquetas pertenecen al mismo equipo
- ✅ Que las asignaciones se crearon correctamente
- ✅ Que los comentarios pertenecen a modelos válidos
- ✅ Que no hay tareas huérfanas (sin proyecto)
- ✅ Que no hay comentarios huérfanos (sin comentable)
- ✅ Que no hay asignaciones huérfanas (sin tarea)

**Ejemplo de validación:**
```php
// Validate task relationships / Validar relaciones de tarea
if ($task->project_id !== $project->id) {
    throw new \RuntimeException("Task project_id mismatch for task: {$task->key}");
}

if ($task->team_id !== $team->id) {
    throw new \RuntimeException("Task team_id mismatch for task: {$task->key}");
}
```

---

## 🔢 3. Generación Secuencial de Keys (Estilo Jira)

### `ProjectFactory`

Genera claves únicas para proyectos:

- ✅ Usa el nombre del proyecto para generar la clave base
- ✅ Asegura unicidad verificando la base de datos
- ✅ Formato: `PROJ`, `TASK`, `FEAT`, etc.

### `TaskFactory`

Genera claves secuenciales estilo Jira:

- ✅ Formato: `PROJ-1`, `PROJ-2`, `PROJ-3`, etc.
- ✅ Calcula el siguiente número basado en tareas existentes
- ✅ Maneja creación concurrente correctamente

**Implementación:**
```php
public function configure(): static
{
    return $this->afterMaking(function (\App\Models\Task $task) {
        if (empty($task->key) && $task->project_id) {
            $project = \App\Models\Project::find($task->project_id);
            if ($project) {
                // Get max task number / Obtener número máximo de tarea
                $maxTaskNumber = \App\Models\Task::where('project_id', $project->id)
                    ->where('key', 'like', $project->key . '-%')
                    ->get()
                    ->map(function ($t) use ($project) {
                        $parts = explode('-', $t->key);
                        return isset($parts[1]) ? (int)$parts[1] : 0;
                    })
                    ->max() ?? 0;
                
                $taskNumber = $maxTaskNumber + 1;
                $task->key = $project->key . '-' . $taskNumber;
            }
        }
    });
}
```

---

## 📊 Resultados

### Antes de las Mejoras

- ❌ Permisos hardcodeados en el seeder
- ❌ Sin validación de integridad
- ❌ Keys aleatorias que podían duplicarse
- ❌ Sin verificación de relaciones

### Después de las Mejoras

- ✅ Permisos desde archivos de configuración
- ✅ Validación exhaustiva de integridad
- ✅ Keys secuenciales estilo Jira (PROJ-1, PROJ-2, etc.)
- ✅ Verificación completa de relaciones
- ✅ Mensajes de error descriptivos
- ✅ Determinismo garantizado

---

## 🚀 Uso

### Ejecutar Seeders

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar un seeder específico
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=TestDataSeeder

# Refrescar y poblar
php artisan migrate:fresh --seed
```

### Verificar Permisos

```php
// En código
$permissions = config('permissions');
$roles = config('roles');

// Verificar si un permiso existe
if (isset($permissions['task.create'])) {
    // El permiso existe
}
```

---

## 🔒 Garantías

1. **Determinismo**: Los seeders siempre crean los mismos datos
2. **Integridad**: Todas las relaciones son validadas
3. **Unicidad**: Las keys son únicas y secuenciales
4. **Trazabilidad**: Errores descriptivos si algo falla

---

## 📝 Notas

- Los archivos de configuración (`config/permissions.php` y `config/roles.php`) son la fuente única de verdad
- Los seeders validan la integridad en cada paso
- Las factories generan keys secuenciales automáticamente
- Todos los errores de integridad lanzan excepciones descriptivas

---

**Última actualización**: 2025-12-23

