# 🚀 Task Manager - Jira/ClickUp Clone

Sistema de gestión de tareas inspirado en Jira y ClickUp, construido con **Laravel 12** y **Vue 3 + Inertia.js**. Implementa multi-tenancy, RBAC, y características avanzadas de gestión de proyectos.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-1.x-9553E9?style=flat)](https://inertiajs.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com)

---

## ✨ Características Principales

### 🏢 Multi-Tenancy
- Sistema multi-inquilino basado en equipos (Teams)
- Aislamiento completo de datos por equipo
- Scopes de seguridad en todos los modelos

### 🔐 Sistema RBAC
- 4 roles predefinidos: Owner, Admin, Member, Viewer
- 16 permisos granulares por recurso
- Políticas de autorización en cada acción

### 📋 Gestión de Proyectos y Tareas
- Tableros Kanban con drag & drop funcional
- Estados: Todo, In Progress, In Review, Done
- Prioridades y tipos de tarea configurables
- Asignación múltiple de usuarios
- Sistema de comentarios
- Etiquetas (Labels) personalizables

### ⚡ Optimistic UI
- Actualizaciones instantáneas en el frontend
- Reversión automática en caso de error
- Experiencia de usuario fluida

### 🎨 Frontend Reactivo
- Vue 3 con Composition API
- Pinia para gestión de estado global
- Componentes atómicos reutilizables
- Diseño responsive y moderno

---

## 🛠 Stack Tecnológico

### Backend
- **Laravel 12** - Framework PHP
- **Laravel Jetstream** - Autenticación y equipos
- **Laravel Sanctum** - Autenticación API/SPA
- **MySQL** - Base de datos
- **UUIDs** - Identificadores públicos

### Frontend
- **Vue 3** - Framework JavaScript
- **Inertia.js** - SPA sin API
- **Pinia** - Gestión de estado
- **Tailwind CSS** - Estilos
- **Vite** - Build tool

---

## 📦 Instalación

```bash
# Clonar repositorio
git clone [repository-url]
cd task_manager_jira

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
DB_CONNECTION=mysql
DB_DATABASE=task_manager

# Ejecutar migraciones y seeders
php artisan migrate
php artisan db:seed

# Compilar assets
npm run build
# o para desarrollo
npm run dev

# Iniciar servidor
php artisan serve
```

---

## 🏗 Arquitectura

### Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/     # Thin Controllers
│   ├── Requests/        # Form Requests (validación)
│   ├── Resources/       # API Resources (transformación)
│   └── Policies/        # Autorización
├── Models/              # Eloquent Models
├── Services/            # Lógica de negocio
└── Traits/              # HasUuids, BelongsToTeam, Commentable

resources/js/
├── Components/          # Componentes atómicos
├── Features/            # Componentes complejos (Kanban)
├── Stores/              # Pinia stores
├── Composables/         # useOptimisticUI, etc.
└── Pages/               # Páginas Inertia
```

### Principios de Diseño

- **Thin Controllers**: Lógica de negocio en Services
- **Form Requests**: Validación centralizada
- **Policies**: Autorización granular
- **API Resources**: Transformación de datos
- **Multi-tenancy**: Aislamiento por `team_id`
- **UUIDs**: Seguridad en IDs públicos

---

## ✅ Estado de Implementación

### ✅ Fase 1: Arquitectura y Base de Datos
- Esquema completo con multi-tenancy
- Sistema RBAC (4 roles, 16 permisos)
- UUIDs en entidades públicas
- Factories y Seeders determinísticos

### ✅ Fase 2: Backend API & Authorization
- Form Requests para validación
- Policies para autorización
- API Resources para transformación
- Services para lógica de negocio
- Rutas API y Web configuradas

### ✅ Fase 3: Frontend Reactivo
- Pinia para estado global
- Componentes atómicos reutilizables
- Kanban con drag & drop funcional
- Optimistic UI implementado
- Formularios reactivos

### ⏳ Fase 4: Real-Time (Pendiente)
- Laravel Reverb
- Broadcast Events
- Presence Channels

---

## 🎯 Características Destacadas

### Drag & Drop en Kanban
- Arrastrar y soltar tareas entre columnas
- Actualización optimista instantánea
- Feedback visual durante el arrastre
- Validación de estado antes de mover

### Gestión de Estado
- **useNotificationStore**: Notificaciones globales
- **useSidebarStore**: Estado del sidebar
- **useProjectStore**: Proyectos activos
- **useDragStore**: Estado compartido para drag & drop

### Seguridad
- Scopes de Eloquent para multi-tenancy
- Validación de permisos en cada acción
- Sanitización de inputs
- CSRF protection

---

## 📚 Documentación

- [Estrategia de Implementación](./docs/estrategia_implementacion.md)
- [Fase 1 Implementada](./docs/fase_1_implementada.md)
- [Fase 3 Implementada](./docs/fase_3_implementada.md)
- [Reglas de Implementación](./docs/reglas_implementacion.md)

---

## 👤 Credenciales por Defecto

Después de ejecutar los seeders:

- **Email**: `test@example.com`
- **Password**: `password`
- **Team**: "Test User's Team"

---

## 🚀 Próximos Pasos

- [ ] Implementar Real-Time con Laravel Reverb
- [ ] Sistema de auditoría y logs
- [ ] Rate limiting avanzado
- [ ] Optimizaciones de rendimiento
- [ ] Tests automatizados

---

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la [MIT License](LICENSE).

---

**Desarrollado por Leranuva usando Laravel y Vue.js**
