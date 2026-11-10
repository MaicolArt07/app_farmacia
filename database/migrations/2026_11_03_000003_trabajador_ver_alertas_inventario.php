<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * El rol Trabajador ve las notificaciones de inventario (campana + página de
 * alertas): productos con stock bajo y lotes vencidos o por vencer.
 * Es solo lectura; no da acceso a modificar inventario.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permiso = Permission::firstOrCreate(['name' => 'Ver Alertas Inventario', 'guard_name' => 'web']);

        Role::where('name', 'Trabajador')->first()?->givePermissionTo($permiso);
        Role::where('name', 'admin')->first()?->givePermissionTo($permiso);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::where('name', 'Trabajador')->first()?->revokePermissionTo('Ver Alertas Inventario');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
