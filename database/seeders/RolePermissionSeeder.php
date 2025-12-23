<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Ejecuta los seeders de la base de datos.
     */
    public function run(): void
    {
        // Create Permissions / Crear Permisos
        $permissions = $this->createPermissions();

        // Create Roles / Crear Roles
        $roles = $this->createRoles();

        // Assign Permissions to Roles / Asignar Permisos a Roles
        $this->assignPermissionsToRoles($roles, $permissions);
    }

    /**
     * Create all permissions from configuration file.
     * Crea todos los permisos desde el archivo de configuración.
     * 
     * @return array<string, Permission>
     */
    private function createPermissions(): array
    {
        $permissionsConfig = config('permissions');
        $permissions = [];

        foreach ($permissionsConfig as $name => $config) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $config['display_name'],
                    'group' => $config['group'],
                    'description' => $config['description'],
                ]
            );

            // Validate permission was created correctly / Validar que el permiso se creó correctamente
            if (!$permission->exists) {
                throw new \RuntimeException("Failed to create permission: {$name}");
            }

            $permissions[$name] = $permission;
        }

        // Validate all permissions exist / Validar que todos los permisos existen
        $expectedCount = count($permissionsConfig);
        $actualCount = Permission::count();
        
        if ($actualCount < $expectedCount) {
            throw new \RuntimeException(
                "Permission count mismatch. Expected: {$expectedCount}, Found: {$actualCount}"
            );
        }

        $this->command->info("✅ {$actualCount} permisos creados/verificados exitosamente / {$actualCount} permissions created/verified successfully");

        return $permissions;
    }

    /**
     * Create all roles from configuration file.
     * Crea todos los roles desde el archivo de configuración.
     * 
     * @return array<string, Role>
     */
    private function createRoles(): array
    {
        $rolesConfig = config('roles');
        $roles = [];

        foreach ($rolesConfig as $name => $config) {
            $role = Role::firstOrCreate(
                ['name' => $name, 'team_id' => null],
                [
                    'display_name' => $config['display_name'],
                    'description' => $config['description'],
                    'is_system' => true,
                ]
            );

            // Validate role was created correctly / Validar que el rol se creó correctamente
            if (!$role->exists) {
                throw new \RuntimeException("Failed to create role: {$name}");
            }

            $roles[$name] = $role;
        }

        // Validate all roles exist / Validar que todos los roles existen
        $expectedCount = count($rolesConfig);
        $actualCount = Role::whereNull('team_id')->where('is_system', true)->count();
        
        if ($actualCount < $expectedCount) {
            throw new \RuntimeException(
                "Role count mismatch. Expected: {$expectedCount}, Found: {$actualCount}"
            );
        }

        $this->command->info("✅ {$actualCount} roles creados/verificados exitosamente / {$actualCount} roles created/verified successfully");

        return $roles;
    }

    /**
     * Assign permissions to roles from configuration.
     * Asigna permisos a los roles desde la configuración.
     * 
     * @param array<string, Role> $roles
     * @param array<string, Permission> $permissions
     */
    private function assignPermissionsToRoles(array $roles, array $permissions): void
    {
        $rolesConfig = config('roles');

        foreach ($rolesConfig as $roleName => $config) {
            $role = $roles[$roleName];
            
            // Get permissions for this role / Obtener permisos para este rol
            if ($config['permissions'] === '*') {
                // Owner: All permissions / Propietario: Todos los permisos
                $permissionIds = array_values(array_map(fn($p) => $p->id, $permissions));
            } else {
                // Get specific permissions / Obtener permisos específicos
                $permissionIds = [];
                foreach ($config['permissions'] as $permissionName) {
                    if (!isset($permissions[$permissionName])) {
                        throw new \RuntimeException(
                            "Permission '{$permissionName}' not found for role '{$roleName}'"
                        );
                    }
                    $permissionIds[] = $permissions[$permissionName]->id;
                }
            }

            // Sync permissions (replace existing) / Sincronizar permisos (reemplazar existentes)
            $role->permissions()->sync($permissionIds);

            // Validate permissions were assigned / Validar que los permisos fueron asignados
            $assignedCount = $role->permissions()->count();
            $expectedCount = count($permissionIds);
            
            if ($assignedCount !== $expectedCount) {
                throw new \RuntimeException(
                    "Permission assignment mismatch for role '{$roleName}'. Expected: {$expectedCount}, Found: {$assignedCount}"
                );
            }

            $this->command->info(
                "  ✓ {$roleName}: {$assignedCount} permisos asignados / {$assignedCount} permissions assigned"
            );
        }

        $this->command->info('✅ Permisos asignados a roles exitosamente / Permissions assigned to roles successfully');
    }
}
