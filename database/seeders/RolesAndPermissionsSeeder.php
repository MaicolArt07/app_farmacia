<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            'ver dashboard',
            'gestionar usuarios',
            'editar perfil',
            'publicar contenido',
            'eliminar contenido',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $cliente = Role::firstOrCreate(['name' => 'cliente']);
        $developer = Role::firstOrCreate(['name' => 'developer']);

        // Asignar permisos a roles
        $admin->givePermissionTo($permissions); // admin tiene todos los permisos

        $cliente->givePermissionTo([
            'ver dashboard',
            'editar perfil',
        ]);

        $developer->givePermissionTo([
            'ver dashboard',
            'gestionar usuarios',
            'publicar contenido',
        ]);
    }
}
