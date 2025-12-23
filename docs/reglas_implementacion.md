# 📏 Reglas de Implementación - Task Manager Jira/ClickUp

Este documento define las reglas de oro y el flujo de implementación para mantener un código limpio, modular y escalable.

---

## 📏 1. Reglas de Arquitectura (Patrones de Diseño)

### A. En el Backend (Laravel)

#### Thin Controllers (Controladores Delgados)
- Un controlador solo debe recibir la petición y devolver una respuesta.
- La lógica de negocio va en **Services**.
- El controlador actúa como orquestador, no como ejecutor.

**Ejemplo:**
```php
// ❌ Mal - Lógica en el controlador
public function store(Request $request) {
    $task = new Task();
    $task->title = $request->title;
    $task->user_id = auth()->id();
    $task->save();
    // ... más lógica
}

// ✅ Bien - Lógica en Service
public function store(StoreTaskRequest $request, TaskService $service) {
    $task = $service->create($request->validated());
    return redirect()->route('tasks.show', $task);
}
```

#### Action Classes
- Para procesos complejos (ej. `CreateTaskAction`), crea una clase única que haga una sola cosa.
- Esto evita duplicar código si necesitas crear una tarea desde la web, la API o un comando de consola.
- Cada Action debe tener un método `execute()` o `handle()`.

**Ejemplo:**
```php
class CreateTaskAction
{
    public function execute(array $data, User $user): Task
    {
        // Lógica de creación de tarea
        // Validaciones adicionales
        // Eventos
        // Notificaciones
        return $task;
    }
}
```

#### DTOs (Data Transfer Objects)
- No pases el objeto `Request` completo a tus servicios.
- Crea una clase que transporte solo los datos validados.
- Esto hace el código más testeable y desacoplado.

**Ejemplo:**
```php
class CreateTaskDTO
{
    public function __construct(
        public string $title,
        public string $description,
        public ?int $assigneeId,
        public int $projectId
    ) {}
}

// En el Service
public function create(CreateTaskDTO $dto): Task
{
    // Usar $dto->title, $dto->description, etc.
}
```

#### Traits para Comportamiento Compartido
- Si varios modelos son "Comentables" o "Asignables", usa Traits para reutilizar esa lógica.
- Mantén los Traits enfocados en una sola responsabilidad.

**Ejemplo:**
```php
trait Commentable
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

// Usar en Task, Project, etc.
class Task extends Model
{
    use Commentable;
}
```

---

### B. En el Frontend (Vue 3)

#### Composables (The Power of Vue 3)
- Extrae la lógica reutilizable (ej. la lógica del drag & drop, el manejo de errores de formularios) a archivos `.js` o `.ts` dentro de una carpeta `composables/`.
- Los composables deben empezar con `use` (ej. `useTaskDrag`, `useFormValidation`).

**Ejemplo:**
```javascript
// composables/useTaskDrag.js
export function useTaskDrag() {
    const draggedTask = ref(null)
    
    const onDragStart = (task) => {
        draggedTask.value = task
    }
    
    const onDrop = (targetStatus) => {
        // Lógica de drop
    }
    
    return { draggedTask, onDragStart, onDrop }
}
```

#### Componentes Atómicos
- Divide tu UI en piezas pequeñas.
- Si un componente supera las **200 líneas**, es momento de extraer sub-componentes.
- Un componente debe tener una sola responsabilidad visual.

**Estructura recomendada:**
```
TaskCard.vue (componente principal)
├── TaskHeader.vue (título, prioridad)
├── TaskBody.vue (descripción)
├── TaskFooter.vue (asignado, fecha)
└── TaskActions.vue (botones de acción)
```

#### Single Source of Truth
- El estado compartido (quién es el usuario, lista de proyectos activos) debe vivir en **Pinia**, no pasando "props" a través de 5 niveles de componentes.
- Evita prop drilling usando stores de Pinia.

**Ejemplo:**
```javascript
// ❌ Mal - Prop drilling
<Parent>
  <Child :user="user">
    <GrandChild :user="user">
      <GreatGrandChild :user="user" />
    </GrandChild>
  </Child>
</Parent>

// ✅ Bien - Pinia Store
// En cualquier componente
const userStore = useUserStore()
const user = computed(() => userStore.currentUser)
```

---

## 📋 2. Orden de Implementación del Código

Sigue este orden jerárquico para cada funcionalidad nueva (ej. "Módulo de Comentarios"):

### Paso 1: Base de Datos y Modelos

#### Migration
- Define la tabla con índices correctos.
- Usa UUIDs para IDs públicos.
- Incluye `team_id` o `workspace_id` para multi-tenancy.
- Agrega timestamps y soft deletes si aplica.

**Ejemplo:**
```php
Schema::create('comments', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('team_id')->constrained()->onDelete('cascade');
    $table->foreignUuid('task_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('body');
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['team_id', 'task_id']);
    $table->index('user_id');
});
```

#### Model
- Define `$fillable`, relaciones (`belongsTo`, `hasMany`) y Casts (ej. convertir un JSON de la DB en un array de PHP automáticamente).
- Usa scopes para queries comunes.

**Ejemplo:**
```php
class Comment extends Model
{
    use HasUuids;
    
    protected $fillable = ['body', 'task_id', 'user_id'];
    
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'metadata' => 'array', // JSON a array automático
        ];
    }
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }
}
```

#### Factory & Seeder
- Crea datos de prueba inmediatamente.
- No puedes probar bien sin datos.
- Usa factories para generar datos realistas.

**Ejemplo:**
```php
CommentFactory::new()
    ->for(Task::factory())
    ->for(User::factory())
    ->create(['body' => 'Este es un comentario de prueba']);
```

---

### Paso 2: Validación y Autorización

#### Form Request
- Crea la clase de validación (`php artisan make:request StoreTaskRequest`).
- Centraliza todas las reglas de validación aquí.

**Ejemplo:**
```php
class StoreCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
            'task_id' => ['required', 'exists:tasks,id'],
        ];
    }
    
    public function authorize(): bool
    {
        return $this->user()->can('create', Comment::class);
    }
}
```

#### Policy
- Define quién puede hacer qué (`php artisan make:policy TaskPolicy`).
- Usa Gates para acciones más complejas.

**Ejemplo:**
```php
class CommentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return $user->belongsToTeam($task->team);
    }
    
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id 
            || $user->hasTeamRole($comment->team, 'admin');
    }
}
```

---

### Paso 3: Lógica de Negocio (Service Layer)

#### Service
- Crea un Service (ej. `app/Services/TaskService.php`).
- Escribe el método para procesar la lógica (ej. guardar archivo, asignar puntos de historia, disparar notificación).
- El Service puede usar múltiples Actions si es necesario.

**Ejemplo:**
```php
class CommentService
{
    public function __construct(
        private CreateCommentAction $createAction,
        private NotifyCommentCreatedAction $notifyAction
    ) {}
    
    public function create(CreateCommentDTO $dto, User $user): Comment
    {
        $comment = $this->createAction->execute($dto, $user);
        $this->notifyAction->execute($comment);
        
        return $comment;
    }
}
```

---

### Paso 4: Punto de Unión (Controller & Routes)

#### Controller
- El controlador llama al Service y retorna una Inertia Response o un Resource.
- Mantén los controladores delgados.

**Ejemplo:**
```php
class CommentController extends Controller
{
    public function __construct(
        private CommentService $service
    ) {}
    
    public function store(StoreCommentRequest $request)
    {
        $dto = CreateCommentDTO::fromRequest($request);
        $comment = $this->service->create($dto, $request->user());
        
        return redirect()->back()->with('success', 'Comentario creado');
    }
}
```

#### Routes
- Define la ruta en `routes/web.php` protegida por middleware de autenticación.
- Usa route model binding cuando sea posible.

**Ejemplo:**
```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])
        ->name('comments.store');
});
```

---

### Paso 5: Frontend (Vue)

#### Componente UI
- Crea la interfaz en Vue.
- Usa componentes atómicos cuando sea posible.

**Ejemplo:**
```vue
<template>
    <form @submit.prevent="submit">
        <BaseTextarea
            v-model="form.body"
            :error="form.errors.body"
            placeholder="Escribe un comentario..."
        />
        <BaseButton type="submit" :disabled="form.processing">
            Comentar
        </BaseButton>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    body: '',
})

const submit = () => {
    form.post(route('comments.store', task.id))
}
</script>
```

#### Integration
- Conecta el formulario con Inertia (`useForm`) y escucha los eventos de tiempo real si aplica.
- Usa composables para lógica reutilizable.

---

## 📂 3. Estructura de Carpetas Recomendada

Para evitar archivos gigantes, organiza tu proyecto así:

### Backend (Laravel)

```
app/
├── Actions/              # Lógica de una sola acción
│   ├── Task/
│   │   ├── CreateTaskAction.php
│   │   ├── UpdateTaskAction.php
│   │   └── DeleteTaskAction.php
│   └── Comment/
│       └── CreateCommentAction.php
│
├── Services/            # Lógica de negocio compleja
│   ├── TaskService.php
│   ├── ProjectService.php
│   └── CommentService.php
│
├── DTOs/                # Data Transfer Objects
│   ├── CreateTaskDTO.php
│   └── UpdateTaskDTO.php
│
├── Http/
│   ├── Requests/       # Validaciones
│   │   ├── Task/
│   │   └── Comment/
│   │
│   ├── Resources/      # Transformación de datos para el Front
│   │   ├── TaskResource.php
│   │   └── CommentResource.php
│   │
│   └── Controllers/    # Solo orquestación
│       ├── TaskController.php
│       └── CommentController.php
│
├── Models/
│   ├── Task.php
│   ├── Project.php
│   └── Comment.php
│
├── Policies/            # Autorización
│   ├── TaskPolicy.php
│   └── CommentPolicy.php
│
└── Traits/              # Código reutilizable entre modelos
    ├── Commentable.php
    ├── Assignable.php
    └── HasUuids.php
```

### Frontend (Vue 3)

```
resources/js/
├── Components/         # Componentes atómicos reutilizables
│   ├── Base/
│   │   ├── BaseButton.vue
│   │   ├── BaseModal.vue
│   │   ├── BaseInput.vue
│   │   └── BaseTextarea.vue
│   │
│   ├── Task/
│   │   ├── TaskCard.vue
│   │   ├── TaskForm.vue
│   │   └── TaskList.vue
│   │
│   └── Comment/
│       ├── CommentCard.vue
│       └── CommentForm.vue
│
├── Features/           # Componentes grandes (páginas completas)
│   ├── KanbanBoard.vue
│   ├── ProjectList.vue
│   └── Dashboard.vue
│
├── Composables/        # Lógica de Vue reutilizable
│   ├── useTaskDrag.js
│   ├── useFormValidation.js
│   ├── useRealtime.js
│   └── usePermissions.js
│
├── Stores/             # Estados de Pinia
│   ├── useUserStore.js
│   ├── useProjectStore.js
│   └── useNotificationStore.js
│
├── Layouts/
│   └── AppLayout.vue
│
└── Pages/
    ├── Tasks/
    └── Projects/
```

---

## 🛡️ 4. Reglas de "Código Limpio" (Clean Code)

### DRY (Don't Repeat Yourself)
- Si escribes lo mismo dos veces, conviértelo en un componente o función.
- Extrae lógica común a helpers, traits o composables.

**Ejemplo:**
```php
// ❌ Mal - Código duplicado
if ($task->status === 'completed') {
    $task->completed_at = now();
    $task->save();
}

if ($project->status === 'completed') {
    $project->completed_at = now();
    $project->save();
}

// ✅ Bien - Trait reutilizable
trait Completable
{
    public function markAsCompleted()
    {
        $this->update(['completed_at' => now()]);
    }
}
```

### Early Returns
- En lugar de anidar `if`, sal de la función lo antes posible.
- Esto mejora la legibilidad y reduce la complejidad ciclomática.

**Ejemplo:**
```php
// ❌ Mal - Anidación profunda
public function update(Request $request, Task $task)
{
    if ($user) {
        if ($user->isAdmin()) {
            if ($task->team_id === $user->currentTeam->id) {
                $task->update($request->validated());
                return response()->json($task);
            }
        }
    }
    return abort(403);
}

// ✅ Bien - Early returns
public function update(UpdateTaskRequest $request, Task $task)
{
    if (!$user || !$user->isAdmin()) {
        return abort(403);
    }
    
    if ($task->team_id !== $user->currentTeam->id) {
        return abort(403);
    }
    
    $task->update($request->validated());
    return response()->json($task);
}
```

### Naming Conventions
- No uses abreviaciones.
- `TaskRepository` es mejor que `TaskRepo`.
- `$projectOwner` es mejor que `$po`.
- Usa nombres descriptivos que expliquen la intención.

**Ejemplo:**
```php
// ❌ Mal - Abreviaciones
$tsk = Task::find($id);
$usr = User::find($tsk->usr_id);
$prj = Project::find($tsk->prj_id);

// ✅ Bien - Nombres descriptivos
$task = Task::find($id);
$assignedUser = User::find($task->assigned_user_id);
$project = Project::find($task->project_id);
```

### Otras Reglas Importantes

#### Single Responsibility Principle (SRP)
- Cada clase, función o componente debe tener una sola razón para cambiar.
- Si una clase hace demasiadas cosas, divídela.

#### Open/Closed Principle
- Las clases deben estar abiertas para extensión pero cerradas para modificación.
- Usa interfaces y herencia para extender funcionalidad sin modificar código existente.

#### Dependency Injection
- Inyecta dependencias en lugar de crear instancias dentro de las clases.
- Facilita el testing y hace el código más flexible.

**Ejemplo:**
```php
// ❌ Mal - Crear instancia dentro
class TaskService
{
    public function create($data)
    {
        $notifier = new NotificationService(); // ❌
        // ...
    }
}

// ✅ Bien - Inyección de dependencias
class TaskService
{
    public function __construct(
        private NotificationService $notifier
    ) {}
    
    public function create($data)
    {
        $this->notifier->send(...); // ✅
    }
}
```

#### Type Hints y Return Types
- Siempre usa type hints en parámetros y return types.
- Esto mejora la autocompletado del IDE y previene errores.

**Ejemplo:**
```php
// ❌ Mal - Sin type hints
public function create($data)
{
    return Task::create($data);
}

// ✅ Bien - Con type hints
public function create(array $data): Task
{
    return Task::create($data);
}
```

---

## 📝 Checklist de Código Limpio

Antes de hacer commit, verifica:

- [ ] ¿El código sigue el principio DRY?
- [ ] ¿Hay early returns donde sea apropiado?
- [ ] ¿Los nombres son descriptivos y sin abreviaciones?
- [ ] ¿Cada clase/componente tiene una sola responsabilidad?
- [ ] ¿Se están usando type hints y return types?
- [ ] ¿Las dependencias están inyectadas correctamente?
- [ ] ¿El código está comentado solo donde es necesario (código auto-documentado)?
- [ ] ¿Se siguen las convenciones de Laravel y Vue?
- [ ] ¿No hay código muerto o comentado?

---

**Última actualización**: 2025-12-23
**Versión**: 1.0.0

