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
        $this->addForeignKeys();
        $this->seedInvoicingPermissions();
        $this->seedReportsPermissions();
        $this->seedMissingKardexAndAlertsPermissions();
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
        $this->line('Verificando país huérfano en person.id_country...');

        $bolivia = DB::table('country')->where('name', 'Bolivia')->first();

        if (!$bolivia) {
            $this->warnings[] = 'No existe un país "Bolivia" en la tabla country; no se corrigió person.id_country.';
            return;
        }

        $validIds = DB::table('country')->pluck('id')->all();

        $affected = DB::table('person')
            ->whereNotIn('id_country', $validIds)
            ->update(['id_country' => $bolivia->id]);

        if ($affected > 0) {
            $this->info("  -> {$affected} fila(s) de person reapuntadas a country '{$bolivia->name}' (id={$bolivia->id}).");
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
        $this->line('Agregando users.id_person...');

        if (Schema::hasColumn('users', 'id_person')) {
            $this->line('  -> ya existe.');
            return;
        }

        DB::statement('ALTER TABLE `users` ADD COLUMN `id_person` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `id`');
        $this->info('  -> columna creada.');
    }

    private function fixPersonCreatedAtTypo(): void
    {
        $this->line('Corrigiendo person.createad_at -> created_at...');

        if (!Schema::hasColumn('person', 'createad_at')) {
            $this->line('  -> ya está corregido.');
            return;
        }

        DB::statement('ALTER TABLE `person` CHANGE `createad_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->info('  -> columna renombrada.');
    }

    private function fixPersonIdType(): void
    {
        $this->line('Normalizando tipo de person.id a BIGINT UNSIGNED...');

        $column = DB::selectOne(
            "SELECT DATA_TYPE, COLUMN_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'person' AND COLUMN_NAME = 'id'",
            [DB::getDatabaseName()]
        );

        if ($column && str_contains(strtolower($column->COLUMN_TYPE), 'bigint')) {
            $this->line('  -> ya es BIGINT UNSIGNED.');
            return;
        }

        DB::statement('ALTER TABLE `person` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        $this->info('  -> tipo corregido (era ' . ($column->COLUMN_TYPE ?? 'desconocido') . ').');
    }

    private function makeLotsExpirationDateNullable(): void
    {
        $this->line('Haciendo lots.expiration_date nullable...');

        DB::statement('ALTER TABLE `lots` MODIFY `expiration_date` DATE NULL DEFAULT NULL');
        $this->info('  -> aplicado.');
    }

    private function createInvoicesTable(): void
    {
        $this->line('Creando tabla invoices...');

        if (Schema::hasTable('invoices')) {
            $this->line('  -> ya existe.');
            return;
        }

        DB::statement(<<<SQL
            CREATE TABLE `invoices` (
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
        $this->line('Agregando UNIQUE en products.code...');

        $exists = DB::select("SHOW INDEX FROM `products` WHERE Key_name = 'products_code_unique'");

        if (!empty($exists)) {
            $this->line('  -> ya existe.');
            return;
        }

        $duplicates = DB::table('products')
            ->select('code')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $this->warnings[] = 'products.code tiene valores duplicados; no se agregó la restricción UNIQUE. Códigos: '
                . $duplicates->pluck('code')->implode(', ');
            return;
        }

        DB::statement('ALTER TABLE `products` ADD UNIQUE KEY `products_code_unique` (`code`)');
        $this->info('  -> aplicado.');
    }

    private function addForeignKeys(): void
    {
        $this->line('Agregando foreign keys reales...');

        $definitions = [
            ['person', 'id_country', 'country', 'id', 'RESTRICT'],
            ['clients', 'id_person', 'person', 'id', 'RESTRICT'],
            ['suppliers', 'id_person', 'person', 'id', 'RESTRICT'],
            ['users', 'id_person', 'person', 'id', 'SET NULL'],
            ['products', 'id_category', 'categories', 'id', 'RESTRICT'],
            ['products', 'id_laboratory', 'laboratories', 'id', 'RESTRICT'],
            ['products', 'id_presentation', 'presentations', 'id', 'RESTRICT'],
            ['lots', 'id_product', 'products', 'id', 'RESTRICT'],
            ['purchases', 'id_supplier', 'suppliers', 'id', 'RESTRICT'],
            ['purchases', 'id_user', 'users', 'id', 'RESTRICT'],
            ['purchases', 'cancelled_by', 'users', 'id', 'SET NULL'],
            ['purchase_details', 'id_purchase', 'purchases', 'id', 'CASCADE'],
            ['purchase_details', 'id_product', 'products', 'id', 'RESTRICT'],
            ['sales', 'id_client', 'clients', 'id', 'RESTRICT'],
            ['sales', 'id_user', 'users', 'id', 'RESTRICT'],
            ['sales', 'cancelled_by', 'users', 'id', 'SET NULL'],
            ['sale_details', 'id_sale', 'sales', 'id', 'CASCADE'],
            ['sale_details', 'id_product', 'products', 'id', 'RESTRICT'],
            ['sale_details', 'id_lot', 'lots', 'id', 'RESTRICT'],
            ['cash_registers', 'id_user', 'users', 'id', 'RESTRICT'],
            ['cash_movements', 'id_cash_register', 'cash_registers', 'id', 'CASCADE'],
            ['cash_movements', 'id_user', 'users', 'id', 'RESTRICT'],
            ['inventory_movements', 'id_product', 'products', 'id', 'RESTRICT'],
            ['inventory_movements', 'id_lot', 'lots', 'id', 'SET NULL'],
            ['inventory_movements', 'id_user', 'users', 'id', 'RESTRICT'],
            ['inventory_adjustments', 'id_product', 'products', 'id', 'RESTRICT'],
            ['inventory_adjustments', 'id_lot', 'lots', 'id', 'RESTRICT'],
            ['inventory_adjustments', 'id_user', 'users', 'id', 'RESTRICT'],
            ['invoices', 'id_sale', 'sales', 'id', 'RESTRICT'],
            ['invoices', 'id_user', 'users', 'id', 'RESTRICT'],
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
