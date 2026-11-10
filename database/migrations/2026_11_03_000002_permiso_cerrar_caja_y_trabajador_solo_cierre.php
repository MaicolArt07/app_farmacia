<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * - Nuevo permiso "Cerrar Caja" (antes el cierre dependía de "Editar Caja",
 *   que también habilita "Ampliar vigencia").
 * - El rol Trabajador, sobre las cajas, solo puede: verlas, abrir la suya
 *   (Crear Caja) y cerrarla (Cerrar Caja). Se le quitan: ver detalle
 *   (Ver Movimientos Caja), registrar movimientos, ampliar vigencia
 *   (Editar Caja) y activar/desactivar (Cambiar Estado Caja).
 * - admin conserva todo y recibe también "Cerrar Caja".
 */
return new class extends Migration
{
    private array $quitarTrabajador = [
        'Ver Movimientos Caja',
        'Crear Movimientos Caja',
        'Editar Caja',
        'Cambiar Estado Caja',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $cerrar = Permission::firstOrCreate(['name' => 'Cerrar Caja', 'guard_name' => 'web']);

        if ($admin = Role::where('name', 'admin')->first()) {
            $admin->givePermissionTo($cerrar);
        }

        if ($trabajador = Role::where('name', 'Trabajador')->first()) {
            $trabajador->givePermissionTo($cerrar);

            foreach ($this->quitarTrabajador as $permiso) {
                if (Permission::where('name', $permiso)->exists()) {
                    $trabajador->revokePermissionTo($permiso);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($trabajador = Role::where('name', 'Trabajador')->first()) {
            foreach ($this->quitarTrabajador as $permiso) {
                if (Permission::where('name', $permiso)->exists()) {
                    $trabajador->givePermissionTo($permiso);
                }
            }
        }

        Permission::where('name', 'Cerrar Caja')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
