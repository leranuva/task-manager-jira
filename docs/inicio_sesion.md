# 🔐 Guía de Inicio de Sesión

**Última actualización**: 2025-12-23

---

## 📋 Opciones para Iniciar Sesión

### Opción 1: Usuario de Prueba (Recomendado)

Si ejecutaste los seeders, puedes usar el usuario de prueba:

**Credenciales:**
- **Email**: `test@example.com`
- **Contraseña**: `password`
- **Nombre**: Test User

### Opción 2: Registrarse como Nuevo Usuario

1. Ve a: http://localhost:8000/register
2. Completa el formulario de registro:
   - Nombre
   - Email
   - Contraseña (mínimo 8 caracteres)
   - Confirmar contraseña
3. Acepta los términos y condiciones
4. Haz clic en "Registrarse"

### Opción 3: Usar Otros Usuarios de Prueba

El seeder `TestDataSeeder` crea múltiples usuarios. Todos tienen la contraseña por defecto: **`password`**

Puedes usar cualquiera de estos emails:
- `test@example.com` (Test User)
- Cualquier otro email de la lista generada por el factory

---

## 🚀 Pasos para Iniciar Sesión

1. **Abre tu navegador** y ve a: http://localhost:8000

2. **Haz clic en "Login"** o ve directamente a: http://localhost:8000/login

3. **Ingresa tus credenciales:**
   - Email: `test@example.com`
   - Contraseña: `password`

4. **Haz clic en "Iniciar Sesión"**

5. Si es tu primera vez, se te pedirá crear un equipo personal (esto es automático con Jetstream)

---

## 🔑 Cambiar Contraseña del Usuario de Prueba

Si necesitas cambiar la contraseña del usuario de prueba:

```bash
php artisan tinker
```

Luego ejecuta:
```php
$user = \App\Models\User::where('email', 'test@example.com')->first();
$user->password = \Hash::make('tu_nueva_contraseña');
$user->save();
```

---

## 👥 Usuarios Disponibles

Después de ejecutar los seeders, tendrás:

- **1 usuario principal**: `test@example.com` (creado por DatabaseSeeder)
- **Múltiples usuarios de prueba**: Creados por TestDataSeeder (todos con contraseña `password`)

---

## 🆘 Solución de Problemas

### Error: "These credentials do not match our records"

**Solución**: 
- Verifica que hayas ejecutado los seeders: `php artisan db:seed`
- Asegúrate de usar la contraseña correcta: `password`

### Error: "Email not verified"

**Solución**: 
- En desarrollo, puedes desactivar la verificación de email en `config/fortify.php`
- O verifica el email manualmente en la base de datos

### No puedo iniciar sesión

**Solución**:
1. Verifica que el servidor esté corriendo: `php artisan serve`
2. Verifica que Vite esté compilando: `npm run dev`
3. Limpia la caché: `php artisan config:clear && php artisan cache:clear`

---

## 📝 Notas

- La contraseña por defecto para todos los usuarios creados con factories es: **`password`**
- El usuario `test@example.com` tiene el rol de **Owner** en su equipo personal
- Todos los usuarios tienen un equipo personal creado automáticamente

---

**Última actualización**: 2025-12-23

