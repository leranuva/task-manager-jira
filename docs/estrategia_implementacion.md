# 🛠 Estrategia de Implementación - Task Manager Jira/ClickUp

Esta guía define la estrategia de implementación del proyecto, dividida en fases para asegurar una base sólida y escalable.

---

## 🛠 Fase 1: Arquitectura y Base de Datos (El Cimiento)

Antes de tocar Vue, el backend debe ser sólido. Un error en el diseño de la DB en un sistema de tareas es fatal a largo plazo.

### Modelo de Datos Multi-inquilino (Multi-tenancy)
- Decide si usarás una DB por cliente o (recomendado para este caso) identificadores de Team/Workspace en cada tabla.
- **Recomendación**: Usar identificadores de Team/Workspace en cada tabla para simplificar la arquitectura y reducir costos.

### Estructura de Permisos (RBAC)
- Implementa un sistema de Roles y Permisos.
- Definir roles: Owner, Admin, Member, Viewer
- Permisos granulares por recurso (Proyecto, Tarea, Comentario, etc.)

### Seguridad
- No confíes en el ID del usuario que viene del frontend.
- Usa Scopes de Eloquent para asegurar que un usuario solo vea tareas de su `team_id`.
- Implementar middleware de verificación de pertenencia al equipo.

### UUIDs en lugar de IDs auto-incrementales
- Para los IDs públicos (proyectos, tareas).
- Evita que alguien adivine el ID de una tarea ajena simplemente cambiando el número en la URL.
- Usar `Str::uuid()` o el trait `HasUuids` de Laravel.

---

## 🚀 Fase 2: Backend API & Authorization (Laravel 12)

Laravel 12 ofrece mejoras en el rendimiento de tipos y servicios.

### Form Requests
- Centraliza la validación.
- Nunca valides dentro del Controlador.
- Crear Form Requests específicos: `StoreTaskRequest`, `UpdateTaskRequest`, etc.

### Policies de Laravel
- Cada acción (editar tarea, borrar proyecto) debe pasar por una Policy.
- Práctica: `Gate::authorize('update', $task);`
- Implementar políticas para: Task, Project, Comment, Team, etc.

### API Resources
- Usa `JsonResource` para transformar tus modelos.
- Esto evita exponer columnas sensibles de la DB (como `password` o `deleted_at`) al frontend de Vue.
- Crear recursos: `TaskResource`, `ProjectResource`, `UserResource`, etc.

### Logging y Auditoría
- Implementa un sistema que registre quién movió qué tarea (crucial para sistemas tipo Jira).
- Puedes usar el paquete `spatie/laravel-activitylog`.
- Registrar: creación, actualización, eliminación, cambios de estado, asignaciones, etc.

---

## 🎨 Fase 3: Frontend Reactivo (Vue 3 + Inertia.js)

Para este proyecto, Inertia.js es superior a una SPA separada porque permite manejar rutas y auth desde Laravel, manteniendo la velocidad de Vue.

### Gestión de Estado (Pinia)
- Úsalo para datos que no cambian en cada página, como:
  - Lista de miembros del equipo
  - Notificaciones pendientes
  - Configuración del usuario
  - Estado global de la aplicación

### Componentes Atómicos
- Crea componentes reutilizables:
  - `BaseButton`
  - `BaseModal`
  - `TaskCard`
  - `StatusBadge`
  - `UserAvatar`
  - `DatePicker`
  - `RichTextEditor`

### Optimistic UI
- Cuando un usuario mueve una tarea en el Kanban, actualiza la posición visualmente antes de recibir la respuesta del servidor.
- Si la petición falla, reviertes el cambio.
- Esto da sensación de "velocidad instantánea".

### Estructura de Componentes
```
resources/js/
├── Components/
│   ├── Base/          # Componentes base reutilizables
│   ├── Tasks/         # Componentes específicos de tareas
│   ├── Projects/      # Componentes específicos de proyectos
│   └── Teams/         # Componentes específicos de equipos
├── Layouts/
├── Pages/
└── Stores/            # Stores de Pinia
```

---

## 📡 Fase 4: Real-Time con Laravel Reverb

Esta es la característica "Estrella".

### Broadcast Events
- Crear eventos como:
  - `TaskMoved`
  - `CommentAdded`
  - `TaskAssigned`
  - `StatusChanged`
  - `UserJoinedProject`

### Private Channels
- Asegúrate de que los eventos de un Proyecto A solo se transmitan a los usuarios que tienen permiso en ese Proyecto A.
- Usar canales privados: `private-project.{projectId}`
- Implementar autorización en `routes/channels.php`

### Presencia
- Usa `PresenceChannels` para mostrar avatares de "quién está conectado ahora" en un tablero específico.
- Mostrar indicadores de actividad en tiempo real.
- Implementar notificaciones push para eventos importantes.

### Configuración
- Instalar y configurar Laravel Reverb
- Configurar broadcasting en `.env`
- Implementar listeners en el frontend con Laravel Echo

---

## 🔒 Fase 5: Estrategia de Seguridad Avanzada

### Sanitización de contenido
- Las descripciones de tareas suelen admitir HTML/Markdown.
- Usa librerías como `Purifier` para evitar ataques XSS.
- Validar y sanitizar todo el contenido generado por el usuario.

### Rate Limiting
- Protege tus endpoints de creación de tareas para evitar spam o ataques DoS mediante los middleware de Laravel.
- Implementar límites por usuario y por IP.
- Configurar diferentes límites según el tipo de acción.

### Escaneo de Archivos
- Si permites adjuntos, usa validaciones de tipo MIME estrictas.
- Considera integrar un escáner de virus (como ClamAV) en el proceso de subida a S3/Local.
- Validar tamaño máximo de archivos.
- Generar thumbnails para imágenes.

### Otras Consideraciones de Seguridad
- Implementar CSRF protection (ya incluido en Laravel).
- Validar permisos en cada request.
- Usar HTTPS en producción.
- Implementar Content Security Policy (CSP).
- Sanitizar inputs SQL (Laravel lo hace automáticamente con Eloquent).

---

## 📊 Fase 6: Análisis y Monitoreo

### Consultas Eficientes
- Usa `with()` (Eager Loading) para evitar el problema de N+1 al cargar tareas con sus etiquetas y responsables.
- Implementar índices en la base de datos para campos frecuentemente consultados.
- Usar `select()` para limitar columnas cuando no necesites todas.

### Cache Inteligente
- Almacena los conteos de los Dashboards en Redis y revalídalos solo cuando una tarea cambie de estado.
- Cachear:
  - Conteos de tareas por estado
  - Lista de proyectos del usuario
  - Configuraciones de equipos
  - Metadatos frecuentemente accedidos

### Monitoreo y Logging
- Implementar logging estructurado.
- Monitorear:
  - Tiempo de respuesta de queries
  - Errores y excepciones
  - Uso de memoria
  - Rendimiento de endpoints críticos

### Optimizaciones Adicionales
- Implementar paginación en listados grandes.
- Usar lazy loading para imágenes.
- Optimizar assets con compresión y minificación.
- Implementar CDN para assets estáticos.

---

## 📋 Checklist de Implementación

### Fase 1: Base de Datos
- [x] Diseñar esquema de base de datos
- [x] Crear migraciones con UUIDs
- [x] Implementar multi-tenancy con team_id
- [x] Crear sistema de roles y permisos
- [x] Implementar scopes de seguridad
- [x] Crear modelos con relaciones Eloquent
- [x] Crear traits reutilizables (HasUuids, BelongsToTeam, Commentable)
- [x] Crear factories para todos los modelos
- [x] Crear seeders para roles, permisos y datos de prueba
- [x] Configurar base de datos MySQL

### Fase 2: Backend
- [x] Crear modelos (Task, Project, Comment, etc.)
- [x] Implementar Form Requests
- [x] Crear Policies
- [x] Implementar API Resources
- [x] Crear Services (ProjectService, TaskService)
- [x] Crear Controllers (Thin Controllers)
- [x] Configurar rutas API
- [x] Configurar rutas web para Inertia
- [x] Manejo de peticiones Inertia vs JSON en controladores
- [x] Carga de relaciones anidadas en Services
- [x] Correcciones en API Resources para relaciones anidadas
- [x] Middleware Sanctum configurado para SPA
- [ ] Integrar sistema de auditoría (opcional para Fase 2)

### Fase 3: Frontend
- [x] Configurar Pinia
- [x] Crear stores (notifications, sidebar, projects, drag & drop)
- [x] Crear componentes base (Button, Input, Badges)
- [x] Crear AppLayout mejorado con sidebar dinámico
- [x] Crear KanbanColumn como feature
- [x] Implementar Optimistic UI (composable)
- [x] Crear TaskCard componente
- [x] Crear ProjectSelector componente
- [x] Drag & drop completamente funcional
- [x] Store compartido para drag & drop (useDragStore)
- [x] Validaciones robustas en componentes
- [x] Integración correcta con Inertia router
- [x] Manejo de respuestas Inertia vs JSON
- [x] Correcciones en autenticación (rutas web)
- [x] Crear páginas principales (Projects Index, Tasks Show, Projects Create)
- [x] Implementar formularios reactivos
- [x] Editor de comentarios
- [x] Selector de asignados
- [x] Animaciones en drag & drop

### Fase 4: Real-Time
- [ ] Instalar Laravel Reverb
- [ ] Crear eventos de broadcast
- [ ] Implementar canales privados
- [ ] Configurar Presence Channels
- [ ] Integrar Laravel Echo en frontend

### Fase 5: Seguridad
- [ ] Implementar sanitización de contenido
- [ ] Configurar rate limiting
- [ ] Implementar validación de archivos
- [ ] Configurar escaneo de archivos (opcional)

### Fase 6: Optimización
- [ ] Optimizar queries con eager loading
- [ ] Implementar sistema de cache
- [ ] Configurar logging estructurado
- [ ] Implementar monitoreo

---

## 🎯 Prioridades

1. **Crítico**: Fase 1 (Base de Datos) y Fase 2 (Backend) - Sin esto, no hay aplicación
2. **Importante**: Fase 3 (Frontend) - La experiencia de usuario
3. **Valor Agregado**: Fase 4 (Real-Time) - Diferencia competitiva
4. **Necesario**: Fase 5 (Seguridad) - Protección de datos
5. **Optimización**: Fase 6 (Análisis) - Escalabilidad

---

## 📝 Notas Adicionales

- Esta estrategia es iterativa: puedes trabajar en múltiples fases simultáneamente según el equipo.
- Prioriza siempre la seguridad y la integridad de los datos.
- Documenta cada decisión arquitectónica importante.
- Realiza pruebas unitarias y de integración en cada fase.
- Mantén el código limpio y siguiendo las mejores prácticas de Laravel y Vue.

---

**Última actualización**: 2025-12-23
**Versión**: 1.2.0

### Changelog

**v1.2.0 (2025-12-23)**
- ✅ Fase 3 completamente funcional con drag & drop
- ✅ Correcciones en autenticación (Inertia router vs axios)
- ✅ Manejo correcto de respuestas Inertia vs JSON
- ✅ Validaciones robustas implementadas
- ✅ Correcciones en API Resources para relaciones anidadas
- ✅ Código limpio y listo para producción

**v1.1.0 (2025-12-23)**
- ✅ Fase 1 y Fase 2 completadas
- ✅ Estructura base de Fase 3 implementada

---

## ✅ Estado de Implementación

### Fase 1: Completada ✅
- ✅ Esquema de base de datos diseñado e implementado
- ✅ Migraciones con UUIDs creadas y ejecutadas
- ✅ Multi-tenancy implementado con `team_id` en todas las tablas
- ✅ Sistema RBAC completo (4 roles, 16 permisos)
- ✅ Scopes de seguridad implementados en todos los modelos
- ✅ Modelos con relaciones Eloquent completas
- ✅ Traits reutilizables creados (HasUuids, BelongsToTeam, Commentable)
- ✅ Factories para todos los modelos
- ✅ Seeders para roles, permisos y datos de prueba
- ✅ Base de datos MySQL configurada y funcionando

### Fase 2: Completada ✅
- ✅ Modelos creados
- ✅ Form Requests implementados (5 requests)
- ✅ Policies implementadas (3 policies)
- ✅ API Resources implementados (5 resources)
- ✅ Services implementados (2 services)
- ✅ Controllers implementados (3 controllers)
- ✅ Rutas API configuradas (18 rutas)
- ✅ Rutas web configuradas para Inertia
- ✅ Manejo de peticiones Inertia vs JSON en controladores
- ✅ Carga de relaciones anidadas en Services (project.team, project.owner)
- ✅ Correcciones en API Resources para relaciones anidadas
- ✅ Middleware Sanctum configurado para SPA
- ⏳ Sistema de auditoría pendiente (opcional)

### Fase 3: Completada ✅
- ✅ Pinia configurado y funcionando
- ✅ Stores implementados (4 stores: notifications, sidebar, projects, drag & drop)
- ✅ Componentes atómicos base (5 componentes con validaciones robustas)
- ✅ AppLayout mejorado con sidebar dinámico
- ✅ KanbanColumn feature con drag & drop completamente funcional
- ✅ Optimistic UI composable con integración Inertia router
- ✅ ProjectSelector y TaskCard con validaciones
- ✅ Páginas principales (Index, Show, Create)
- ✅ Formularios reactivos implementados
- ✅ Editor de comentarios
- ✅ Selector de asignados
- ✅ Animaciones mejoradas en drag & drop
- ✅ Correcciones en autenticación (Inertia router vs axios)
- ✅ Manejo correcto de respuestas Inertia vs JSON
- ✅ Validaciones robustas (arrays, valores undefined, fallbacks)
- ✅ Código limpio y listo para producción

