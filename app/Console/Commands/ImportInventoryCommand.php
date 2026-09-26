<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Laboratory;
use App\Models\Lot;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportInventoryCommand extends Command
{
    protected $signature = 'pharmacy:import-inventory
        {path=Inventario.csv : Ruta del archivo CSV, relativa a la raíz del proyecto}
        {--dry-run : Analiza el archivo e imprime el resumen sin escribir en la base de datos}
        {--user-id= : ID del usuario a registrar como autor de los movimientos de kardex (por defecto, el primer usuario)}';

    protected $description = 'Vacía el catálogo/operación de farmacia y lo recarga desde un CSV de inventario (carga única, autoritativa).';

    private const FALLBACK_CATEGORY = 'SIN CATEGORIA';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (!file_exists($path)) {
            $this->error("No se encontró el archivo: {$path}");
            return self::FAILURE;
        }

        $rows = $this->readCsv($path);

        if ($rows === null) {
            return self::FAILURE;
        }

        $this->info(count($rows) . ' filas leídas de ' . basename($path));

        $userId = $this->option('user-id') ?: User::query()->orderBy('id')->value('id');

        if (!$userId) {
            $this->error('No existe ningún usuario en el sistema para registrar como autor de los movimientos.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $summary = [
            'products_created' => 0,
            'lots_created' => 0,
            'categories_created' => [],
            'laboratories_created' => [],
            'presentations_created' => [],
            'fallback_category_rows' => 0,
            'negative_quantity_rows' => [],
            'reserva_nonzero_rows' => [],
            'extra_price_rows' => 0,
        ];

        if ($dryRun) {
            // Nada se escribe en modo dry-run: ni el truncado ni las filas se aplican.
            foreach ($rows as $row) {
                $this->importRow($row, (int) $userId, $summary, true);
            }
        } else {
            // TRUNCATE hace commit implícito en MySQL, por lo que no puede ir dentro
            // de la misma transacción que la carga de filas.
            $this->truncateCatalog();

            DB::beginTransaction();

            try {
                foreach ($rows as $row) {
                    $this->importRow($row, (int) $userId, $summary, false);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Error durante la carga de filas (el catálogo ya fue vaciado): ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        $this->printSummary($summary, $dryRun);

        return self::SUCCESS;
    }

    private function readCsv(string $path): ?array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->error('No se pudo abrir el archivo.');
            return null;
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            $this->error('El archivo está vacío.');
            return null;
        }

        $header = array_map(fn ($h) => trim((string) $h, "\xEF\xBB\xBF "), $header);

        $rows = [];
        $lineNumber = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (count($line) !== count($header)) {
                $this->warn("Fila {$lineNumber} ignorada: número de columnas no coincide con el encabezado.");
                continue;
            }

            $row = array_combine($header, $line);

            if (trim((string) ($row['Clave'] ?? '')) === '') {
                $this->warn("Fila {$lineNumber} ignorada: sin código (Clave).");
                continue;
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function truncateCatalog(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'sale_details', 'sales',
            'purchase_details', 'purchases',
            'cash_movements', 'cash_registers',
            'inventory_movements', 'inventory_adjustments',
            'lots', 'products',
            'clients', 'suppliers',
            'categories', 'presentations', 'laboratories',
        ] as $table) {
            DB::table($table)->truncate();
        }

        DB::table('person')
            ->whereNotIn('id', DB::table('users')->whereNotNull('id_person')->pluck('id_person'))
            ->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function importRow(array $row, int $userId, array &$summary, bool $dryRun): void
    {
        $clave = trim($row['Clave']);
        $name = trim($row['Nombre del producto'] ?? '');
        $unidad = trim($row['Unidad'] ?? '');
        $categoriaNombre = trim($row['Categoría'] ?? '');
        $laboratorioNombre = trim($row['Información Adicional'] ?? '');
        $cantidad = (int) ($row['Cantidad'] ?? 0);
        $precioCompra = (float) ($row['Precio de Compra'] ?? 0);
        $precioVenta = (float) ($row['Precio de Venta'] ?? 0);
        $reserva = (float) ($row['Reserva'] ?? 0);
        $precioAdicional = trim((string) ($row['Precios de venta adicionales (opcional)'] ?? ''));

        if ($reserva != 0) {
            $summary['reserva_nonzero_rows'][] = "{$clave} ({$reserva})";
        }

        if ($precioAdicional !== '') {
            $summary['extra_price_rows']++;
        }

        $category = $this->firstOrCreateCached(
            Category::class,
            $categoriaNombre !== '' ? $categoriaNombre : self::FALLBACK_CATEGORY,
            $summary['categories_created']
        );

        if ($categoriaNombre === '') {
            $summary['fallback_category_rows']++;
        }

        $presentation = $this->firstOrCreateCached(Presentation::class, $unidad, $summary['presentations_created']);

        $laboratory = null;
        if ($laboratorioNombre !== '') {
            $laboratory = $this->firstOrCreateCached(Laboratory::class, $laboratorioNombre, $summary['laboratories_created']);
        }

        $quantity = max($cantidad, 0);

        if ($cantidad < 0) {
            $summary['negative_quantity_rows'][] = "{$clave} ({$cantidad})";
        }

        if ($dryRun) {
            $summary['products_created']++;
            $summary['lots_created']++;
            return;
        }

        $product = Product::create([
            'id_category' => $category->id,
            'id_laboratory' => $laboratory?->id,
            'id_presentation' => $presentation->id,
            'code' => $clave,
            'barcode' => $clave,
            'name' => $name,
            'generic_name' => null,
            'concentration' => null,
            'description' => null,
            'sale_price' => $precioVenta,
            'minimum_stock' => 0,
            'requires_prescription' => false,
            'state' => 1,
        ]);

        $summary['products_created']++;

        $lot = Lot::create([
            'id_product' => $product->id,
            'batch_code' => 'CARGA-INICIAL-' . $clave,
            'purchase_price' => $precioCompra,
            'sale_price' => $precioVenta,
            'quantity_in' => $quantity,
            'quantity_available' => $quantity,
            'expiration_date' => null,
            'location' => null,
            'state' => 1,
        ]);

        $summary['lots_created']++;

        $description = 'Carga inicial (importación CSV) - Clave: ' . $clave;

        if ($cantidad < 0) {
            $description .= " | Cantidad original en CSV: {$cantidad} (corregida a 0)";
        }

        InventoryMovement::create([
            'id_product' => $product->id,
            'id_lot' => $lot->id,
            'id_user' => $userId,
            'movement_type' => 'IN',
            'reference_type' => 'INVENTORY_IMPORT',
            'reference_id' => null,
            'quantity' => $quantity,
            'stock_before' => 0,
            'stock_after' => $quantity,
            'description' => $description,
        ]);
    }

    private function firstOrCreateCached(string $modelClass, string $name, array &$createdTracker)
    {
        static $cache = [];

        $cacheKey = $modelClass . '|' . $name;

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $existing = $modelClass::where('name', $name)->first();

        if ($existing) {
            $cache[$cacheKey] = $existing;
            return $existing;
        }

        $created = $modelClass::create(['name' => $name, 'state' => 1]);
        $createdTracker[] = $name;
        $cache[$cacheKey] = $created;

        return $created;
    }

    private function printSummary(array $summary, bool $dryRun): void
    {
        $this->newLine();
        $this->info($dryRun ? 'Resumen (dry-run, no se aplicaron cambios):' : 'Resumen de la importación:');

        $this->table(['Métrica', 'Valor'], [
            ['Productos creados', $summary['products_created']],
            ['Lotes creados', $summary['lots_created']],
            ['Categorías nuevas', count($summary['categories_created'])],
            ['Laboratorios nuevos', count($summary['laboratories_created'])],
            ['Presentaciones nuevas', count($summary['presentations_created'])],
            ['Filas sin categoría (asignadas a "' . self::FALLBACK_CATEGORY . '")', $summary['fallback_category_rows']],
            ['Filas con cantidad negativa (corregida a 0)', count($summary['negative_quantity_rows'])],
            ['Filas con "Reserva" distinta de 0 (no se importa)', count($summary['reserva_nonzero_rows'])],
            ['Filas con "Precios de venta adicionales" (no se importa, sin columna en el esquema)', $summary['extra_price_rows']],
        ]);

        if ($summary['presentations_created']) {
            $this->line('Presentaciones creadas: ' . implode(', ', $summary['presentations_created']));
            if (count($summary['presentations_created']) > 1) {
                $this->warn('Nota: revise si alguna de estas presentaciones representa la misma unidad (p.ej. G/GR/GRAMO o L/LITRO) y fusiónelas manualmente desde el módulo de Presentaciones si corresponde.');
            }
        }

        if ($summary['negative_quantity_rows']) {
            $this->warn('Códigos con cantidad negativa corregida: ' . implode(', ', $summary['negative_quantity_rows']));
        }

        if ($summary['reserva_nonzero_rows']) {
            $this->warn('Códigos con reserva distinta de 0 (ignorada): ' . implode(', ', $summary['reserva_nonzero_rows']));
        }
    }
}
