<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpgradeSchemaCommand extends Command
{
    protected $signature = 'pharmacy:upgrade-schema';

    protected $description = 'Endurece el esquema de la base de datos: motor InnoDB, foreign keys reales, correcciones de tipos y columnas nuevas (idempotente, se puede volver a ejecutar).';

    private array $warnings = [];

    public function handle(): int
    {
        $database = DB::getDatabaseName();

        $this->info("Base de datos: {$database}");
        $this->newLine();

        $this->fixOrphanCountry();
        $this->convertEnginesToInnoDb();
        $this->addUsersIdPersonColumn();
        $this->fixPersonCreatedAtTypo();
        $this->fixPersonIdType();
        $this->makeLotsExpirationDateNullable();
        $this->createInvoicesTable();
        $this->addProductsCodeUnique();
        $this->addUsersSessionTokenColumn();
        $this->createLoginLogsTable();
        $this->renameForeignKeyColumnsToSpanish();
        $this->addForeignKeys();
        $this->seedInvoicingPermissions();
        $this->seedReportsPermissions();
        $this->seedMissingKardexAndAlertsPermissions();
        $this->seedLoginLogsPermission();
        $this->seedRolesPermission();
        $this->seedCorePermissions();

        $this->newLine();
        $this->info('Listo.');

        if ($this->warnings) {
            $this->newLine();
            $this->warn('Advertencias (revisar manualmente):');
            foreach ($this->warnings as $warning) {
                $this->line(" - {$warning}");
            }
        }

        return self::SUCCESS;
    }

    private function fixOrphanCountry(): void
    {
        $column = Schema::hasColumn('personas', 'id_pais') ? 'id_pais' : 'id_country';

        $this->line("Verificando país huérfano en personas.{$column}...");

        $bolivia = DB::table('paises')->where('nombre', 'Bolivia')->first();

        if (!$bolivia) {
            $this->warnings[] = "No existe un país \"Bolivia\" en la tabla paises; no se corrigió personas.{$column}.";
            return;
        }

        $validIds = DB::table('paises')->pluck('id')->all();

        $affected = DB::table('personas')
            ->whereNotIn($column, $validIds)
            ->update([$column => $bolivia->id]);

        if ($affected > 0) {
            $this->info("  -> {$affected} fila(s) de personas reapuntadas a pais '{$bolivia->nombre}' (id={$bolivia->id}).");
        } else {
            $this->line('  -> sin filas huérfanas.');
        }
    }

    private function convertEnginesToInnoDb(): void
    {
        $this->line('Convirtiendo tablas de MyISAM a InnoDB...');

        $tables = DB::select("SHOW TABLE STATUS WHERE Engine = 'MyISAM'");

        if (empty($tables)) {
            $this->line('  -> todas las tablas ya son InnoDB.');
            return;
        }

        foreach ($tables as $table) {
            $name = $table->Name;

            try {
                DB::statement("ALTER TABLE `{$name}` ENGINE=InnoDB");
                $this->info("  -> {$name}: OK");
            } catch (\Throwable $e) {
                $this->warnings[] = "No se pudo convertir '{$name}' a InnoDB: " . $e->getMessage();
            }
        }
    }

    private function addUsersIdPersonColumn(): void
    {
        $this->line('Agregando users.id_persona...');

        if (Schema::hasColumn('users', 'id_persona') || Schema::hasColumn('users', 'id_person')) {
            $this->line('  -> ya existe.');
            return;
        }

        DB::statement('ALTER TABLE `users` ADD COLUMN `id_person` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `id`');
        $this->info('  -> columna creada (se renombrará a id_persona en el siguiente paso).');
    }

    private function fixPersonCreatedAtTypo(): void
    {
        $this->line('Corrigiendo personas.createad_at -> created_at...');

        if (!Schema::hasColumn('personas', 'createad_at')) {
            $this->line('  -> ya está corregido.');
            return;
        }

        DB::statement('ALTER TABLE `personas` CHANGE `createad_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->info('  -> columna renombrada.');
    }

    private function fixPersonIdType(): void
    {
        $this->line('Normalizando tipo de personas.id a BIGINT UNSIGNED...');

        $column = DB::selectOne(
            "SELECT DATA_TYPE, COLUMN_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'personas' AND COLUMN_NAME = 'id'",
            [DB::getDatabaseName()]
        );

        if ($column && str_contains(strtolower($column->COLUMN_TYPE), 'bigint')) {
            $this->line('  -> ya es BIGINT UNSIGNED.');
            return;
        }

        DB::statement('ALTER TABLE `personas` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        $this->info('  -> tipo corregido (era ' . ($column->COLUMN_TYPE ?? 'desconocido') . ').');
    }

    private function makeLotsExpirationDateNullable(): void
    {
        $this->line('Haciendo lotes.fecha_vencimiento nullable...');

        DB::statement('ALTER TABLE `lotes` MODIFY `fecha_vencimiento` DATE NULL DEFAULT NULL');
        $this->info('  -> aplicado.');
    }

    private function createInvoicesTable(): void
    {
        $this->line('Creando tabla facturas...');

        if (Schema::hasTable('facturas')) {
            $this->line('  -> ya existe.');
            return;
        }

        DB::statement(<<<SQL
            CREATE TABLE `facturas` (
              `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
              `id_sale` bigint UNSIGNED NOT NULL,
              `id_user` bigint UNSIGNED NOT NULL,
              `modo` enum('SIMULADO','REAL') NOT NULL DEFAULT 'SIMULADO',
              `ambiente` enum('PRUEBA','PRODUCCION') NOT NULL DEFAULT 'PRUEBA',
              `tipo_documento_identidad` varchar(20) DEFAULT NULL,
              `nit_cliente` varchar(30) DEFAULT NULL,
              `razon_social` varchar(150) NOT NULL,
              `numero_factura` bigint UNSIGNED DEFAULT NULL,
              `cuf` varchar(100) DEFAULT NULL,
              `cuis` varchar(50) DEFAULT NULL,
              `cufd` varchar(50) DEFAULT NULL,
              `codigo_control` varchar(50) DEFAULT NULL,
              `total` decimal(12,2) NOT NULL DEFAULT '0.00',
              `estado` enum('PENDIENTE','ENVIADA','OBSERVADA','ERROR','ANULADA') NOT NULL DEFAULT 'PENDIENTE',
              `observaciones` text,
              `raw_request` longtext,
              `raw_response` longtext,
              `anulado_at` timestamp NULL DEFAULT NULL,
              `anulado_reason` text,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `invoices_id_sale_unique` (`id_sale`),
              UNIQUE KEY `invoices_cuf_unique` (`cuf`),
              KEY `invoices_id_user_foreign` (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->info('  -> tabla creada.');
    }

    private function addProductsCodeUnique(): void
    {
        $this->line('Agregando UNIQUE en productos.code...');

        $exists = DB::select("SHOW INDEX FROM `productos` WHERE Key_name = 'products_code_unique'");

        if (!empty($exists)) {
            $this->line('  -> ya existe.');
            return;
        }

        $duplicates = DB::table('productos')
            ->select('code')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $this->warnings[] = 'productos.code tiene valores duplicados; no se agregó la restricción UNIQUE. Códigos: '
                . $duplicates->pluck('code')->implode(', ');
            return;
        }

        DB::statement('ALTER TABLE `productos` ADD UNIQUE KEY `products_code_unique` (`code`)');
        $this->info('  -> aplicado.');
    }

    private function addForeignKeys(): void
    {
        $this->line('Agregando foreign keys reales...');

        $definitions = [
            ['personas', 'id_pais', 'paises', 'id', 'RESTRICT'],
            ['clientes', 'id_persona', 'personas', 'id', 'RESTRICT'],
            ['proveedores', 'id_persona', 'personas', 'id', 'RESTRICT'],
            ['users', 'id_persona', 'personas', 'id', 'SET NULL'],
            ['productos', 'id_categoria', 'categorias', 'id', 'RESTRICT'],
            ['productos', 'id_laboratorio', 'laboratorios', 'id', 'RESTRICT'],
            ['productos', 'id_presentacion', 'presentaciones', 'id', 'RESTRICT'],
            ['lotes', 'id_producto', 'productos', 'id', 'RESTRICT'],
            ['compras', 'id_proveedor', 'proveedores', 'id', 'RESTRICT'],
            ['compras', 'id_usuario', 'users', 'id', 'RESTRICT'],
            ['compras', 'cancelled_by', 'users', 'id', 'SET NULL'],
            ['detalles_compra', 'id_compra', 'compras', 'id', 'CASCADE'],
            ['detalles_compra', 'id_producto', 'productos', 'id', 'RESTRICT'],
            ['ventas', 'id_cliente', 'clientes', 'id', 'RESTRICT'],
            ['ventas', 'id_usuario', 'users', 'id', 'RESTRICT'],
            ['ventas', 'cancelled_by', 'users', 'id', 'SET NULL'],
            ['detalles_venta', 'id_venta', 'ventas', 'id', 'CASCADE'],
            ['detalles_venta', 'id_producto', 'productos', 'id', 'RESTRICT'],
            ['detalles_venta', 'id_lote', 'lotes', 'id', 'RESTRICT'],
            ['cajas', 'id_usuario', 'users', 'id', 'RESTRICT'],
            ['movimientos_caja', 'id_caja', 'cajas', 'id', 'CASCADE'],
            ['movimientos_caja', 'id_usuario', 'users', 'id', 'RESTRICT'],
            ['movimientos_inventario', 'id_producto', 'productos', 'id', 'RESTRICT'],
            ['movimientos_inventario', 'id_lote', 'lotes', 'id', 'SET NULL'],
            ['movimientos_inventario', 'id_usuario', 'users', 'id', 'RESTRICT'],
            ['ajustes_inventario', 'id_producto', 'productos', 'id', 'RESTRICT'],
            ['ajustes_inventario', 'id_lote', 'lotes', 'id', 'RESTRICT'],
            ['ajustes_inventario', 'id_usuario', 'users', 'id', 'RESTRICT'],
            ['facturas', 'id_venta', 'ventas', 'id', 'RESTRICT'],
            ['facturas', 'id_usuario', 'users', 'id', 'RESTRICT'],
            ['login_logs', 'id_usuario', 'users', 'id', 'RESTRICT'],
        ];

        $database = DB::getDatabaseName();

        foreach ($definitions as [$table, $column, $refTable, $refColumn, $onDelete]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            $constraintName = "{$table}_{$column}_foreign";

            $existing = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', $database)
                ->where('TABLE_NAME', $table)
                ->where('CONSTRAINT_NAME', $constraintName)
                ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
                ->exists();

            if ($existing) {
                continue;
            }

            try {
                DB::statement(
                    "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` " .
                    "FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) ON DELETE {$onDelete}"
                );
                $this->info("  -> {$constraintName}: OK");
            } catch (\Throwable $e) {
                $this->warnings[] = "No se pudo crear FK {$constraintName} ({$table}.{$column} -> {$refTable}.{$refColumn}): " . $e->getMessage();
            }
        }
    }

    private function seedInvoicingPermissions(): void
    {
        $this->line('Creando permisos de facturación...');

        $permissions = ['Ver Facturas', 'Crear Facturas', 'Cambiar Estado Facturas'];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        if ($admin) {
            $admin->givePermissionTo($permissions);
            $this->info('  -> permisos creados y asignados al rol admin.');
        } else {
            $this->warnings[] = "Rol 'admin' no encontrado; los permisos de facturación se crearon pero no se asignaron a ningún rol.";
        }
    }

    private function seedReportsPermissions(): void
    {
        $this->line('Creando permisos de reportes...');

        $permissions = ['Ver Reportes', 'Exportar Reportes'];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        if ($admin) {
            $admin->givePermissionTo($permissions);
            $this->info('  -> permisos creados y asignados al rol admin.');
        } else {
            $this->warnings[] = "Rol 'admin' no encontrado; los permisos de reportes se crearon pero no se asignaron a ningún rol.";
        }
    }

    private function seedMissingKardexAndAlertsPermissions(): void
    {
        $this->line('Verificando permisos de Kardex y Alertas de Inventario...');

        // Estas rutas ya usaban middleware('can:Ver Kardex') / can:Ver Alertas
        // Inventario' y el sidebar ya las referenciaba, pero el permiso nunca
        // se creó en la base de datos: ambos módulos han sido inaccesibles
        // (403) para todos los usuarios desde que se construyeron.
        $permissions = ['Ver Kardex', 'Ver Alertas Inventario'];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        if ($admin) {
            $admin->givePermissionTo($permissions);
            $this->info('  -> permisos creados y asignados al rol admin.');
        } else {
            $this->warnings[] = "Rol 'admin' no encontrado; los permisos de Kardex/Alertas se crearon pero no se asignaron a ningún rol.";
        }
    }

    private function addUsersSessionTokenColumn(): void
    {
        $this->line('Agregando users.session_token (sesión única por usuario)...');

        if (Schema::hasColumn('users', 'session_token')) {
            $this->line('  -> ya existe.');
            return;
        }

        DB::statement('ALTER TABLE `users` ADD COLUMN `session_token` VARCHAR(64) NULL DEFAULT NULL AFTER `remember_token`');
        $this->info('  -> columna creada.');
    }

    private function createLoginLogsTable(): void
    {
        $this->line('Creando tabla login_logs...');

        if (Schema::hasTable('login_logs')) {
            $this->line('  -> ya existe.');
            return;
        }

        DB::statement(<<<SQL
            CREATE TABLE `login_logs` (
              `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
              `id_user` bigint UNSIGNED NOT NULL,
              `session_token` varchar(64) DEFAULT NULL,
              `action` enum('login','logout','forced_logout') NOT NULL DEFAULT 'login',
              `ip_address` varchar(45) DEFAULT NULL,
              `user_agent` text,
              `browser` varchar(100) DEFAULT NULL,
              `platform` varchar(100) DEFAULT NULL,
              `device` varchar(50) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `login_logs_id_user_foreign` (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->info('  -> tabla creada.');
    }

    private function seedLoginLogsPermission(): void
    {
        $this->line('Creando permiso de Logs de Acceso...');

        $permission = Permission::firstOrCreate(['name' => 'Ver Logs Acceso', 'guard_name' => 'web']);

        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        if ($admin) {
            $admin->givePermissionTo($permission);
            $this->info('  -> permiso creado y asignado al rol admin.');
        } else {
            $this->warnings[] = "Rol 'admin' no encontrado; el permiso 'Ver Logs Acceso' se creó pero no se asignó a ningún rol.";
        }
    }

    private function seedRolesPermission(): void
    {
        $this->line('Creando permiso de Roles...');

        $permission = Permission::firstOrCreate(['name' => 'Ver Roles', 'guard_name' => 'web']);

        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        if ($admin) {
            $admin->givePermissionTo($permission);
            $this->info('  -> permiso creado y asignado al rol admin.');
        } else {
            $this->warnings[] = "Rol 'admin' no encontrado; el permiso 'Ver Roles' se creó pero no se asignó a ningún rol.";
        }
    }

    private function renameForeignKeyColumnsToSpanish(): void
    {
        $this->line('Renombrando columnas FK a español (id_person -> id_persona, etc.)...');

        $renames = [
            ['users', 'id_person', 'id_persona', 'BIGINT UNSIGNED NULL DEFAULT NULL'],
            ['clientes', 'id_person', 'id_persona', 'BIGINT UNSIGNED NOT NULL'],
            ['proveedores', 'id_person', 'id_persona', 'BIGINT UNSIGNED NOT NULL'],
            ['personas', 'id_country', 'id_pais', 'INT NOT NULL'],
            ['productos', 'id_category', 'id_categoria', 'BIGINT UNSIGNED NOT NULL'],
            ['productos', 'id_laboratory', 'id_laboratorio', 'BIGINT UNSIGNED NULL DEFAULT NULL'],
            ['productos', 'id_presentation', 'id_presentacion', 'BIGINT UNSIGNED NOT NULL'],
            ['productos', 'id_sin_product_code', 'id_codigo_producto_sin', 'BIGINT UNSIGNED NULL DEFAULT NULL'],
            ['lotes', 'id_product', 'id_producto', 'BIGINT UNSIGNED NOT NULL'],
            ['movimientos_inventario', 'id_product', 'id_producto', 'BIGINT UNSIGNED NOT NULL'],
            ['movimientos_inventario', 'id_lot', 'id_lote', 'BIGINT UNSIGNED NULL DEFAULT NULL'],
            ['movimientos_inventario', 'id_user', 'id_usuario', 'BIGINT UNSIGNED NOT NULL'],
            ['ajustes_inventario', 'id_product', 'id_producto', 'BIGINT UNSIGNED NOT NULL'],
            ['ajustes_inventario', 'id_lot', 'id_lote', 'BIGINT UNSIGNED NOT NULL'],
            ['ajustes_inventario', 'id_user', 'id_usuario', 'BIGINT UNSIGNED NOT NULL'],
            ['compras', 'id_supplier', 'id_proveedor', 'BIGINT UNSIGNED NOT NULL'],
            ['compras', 'id_user', 'id_usuario', 'BIGINT UNSIGNED NOT NULL'],
            ['detalles_compra', 'id_purchase', 'id_compra', 'BIGINT UNSIGNED NOT NULL'],
            ['detalles_compra', 'id_product', 'id_producto', 'BIGINT UNSIGNED NOT NULL'],
            ['cajas', 'id_user', 'id_usuario', 'BIGINT UNSIGNED NOT NULL'],
            ['movimientos_caja', 'id_cash_register', 'id_caja', 'BIGINT UNSIGNED NOT NULL'],
            ['movimientos_caja', 'id_user', 'id_usuario', 'BIGINT UNSIGNED NOT NULL'],
            ['ventas', 'id_client', 'id_cliente', 'BIGINT UNSIGNED NULL DEFAULT NULL'],
            ['ventas', 'id_user', 'id_usuario', 'BIGINT UNSIGNED NOT NULL'],
            ['ventas', 'id_cash_register', 'id_caja', 'BIGINT UNSIGNED NULL DEFAULT NULL'],
            ['ventas', 'id_payment_method', 'id_metodo_pago', 'BIGINT UNSIGNED NULL DEFAULT NULL'],
            ['detalles_venta', 'id_sale', 'id_venta', 'BIGINT UNSIGNED NOT NULL'],
            ['detalles_venta', 'id_product', 'id_producto', 'BIGINT UNSIGNED NOT NULL'],
            ['detalles_venta', 'id_lot', 'id_lote', 'BIGINT UNSIGNED NOT NULL'],
            ['facturas', 'id_sale', 'id_venta', 'BIGINT UNSIGNED NOT NULL'],
            ['facturas', 'id_user', 'id_usuario', 'BIGINT UNSIGNED NOT NULL'],
            ['sucursales', 'id_company', 'id_empresa', 'BIGINT UNSIGNED NOT NULL'],
            ['login_logs', 'id_user', 'id_usuario', 'BIGINT UNSIGNED NOT NULL'],
        ];

        $renamed = 0;

        foreach ($renames as [$table, $old, $new, $definition]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $old) && !Schema::hasColumn($table, $new)) {
                try {
                    DB::statement("ALTER TABLE `{$table}` CHANGE `{$old}` `{$new}` {$definition}");
                    $renamed++;
                } catch (\Throwable $e) {
                    $this->warnings[] = "No se pudo renombrar {$table}.{$old} -> {$new}: " . $e->getMessage();
                }
            }
        }

        $this->info("  -> {$renamed} columna(s) renombrada(s) (el resto ya estaba en español).");
    }

    private function seedCorePermissions(): void
    {
        $this->line('Creando catálogo completo de permisos...');

        $permissions = [
            'Ver Inicio',
            'Ver Gestion de Personal',
            'Ver Persona',
            'Ver Clientes',
            'Ver Proveedores',
            'Ver Laboratorios',
            'Ver Empresa',
            'Ver Sucursales',
            'Ver Metodos Pago',
            'Ver Servicios',
            'Crear Servicios',
            'Cambiar Estado Servicios',
            'Ver Categorias',
            'Ver Presentaciones',
            'Ver Productos',
            'Ver Lotes',
            'Ver Compras',
            'Cambiar Estado Compras',
            'Ver Ventas',
            'Crear Ventas',
            'Cambiar Estado Ventas',
            'Ver Caja',
            'Crear Caja',
            'Editar Caja',
            'Cambiar Estado Caja',
            'Ver Movimientos Caja',
            'Crear Movimientos Caja',
            'Ver Ajustes Inventario',
            'Crear Ajustes Inventario',
            'Ver Kardex',
            'Ver Alertas Inventario',
            'Ver Facturas',
            'Crear Facturas',
            'Cambiar Estado Facturas',
            'Ver Reportes',
            'Exportar Reportes',
            'Ver Gestion de Usuarios',
            'Ver Usuarios',
            'Ver Permisos',
            'Ver Logs Acceso',
            'Ver Roles',
        ];

        $created = 0;

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

            if ($permission->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->info("  -> catálogo verificado ({$created} permiso(s) nuevo(s) creados).");
    }
}
