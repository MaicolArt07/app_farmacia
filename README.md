# Farmacia FarmaFamilia — Sistema Administrativo (Laravel + Livewire)

## Instalación

```bash
git clone <url-del-repo>
cd app_far
composer install
npm install

cp .env.example .env
php artisan key:generate
# Editar .env con los datos de tu base de datos MySQL (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

php artisan migrate --seed
npm run build
php artisan serve
```

El seeder crea dos usuarios iniciales:

- `admin@farmafamilia.com` / `Admin123!` (todos los permisos)
- `empleado@farmafamilia.com` / `Empleado123!` (permisos de operación diaria: ventas, caja, consulta de productos/clientes)

> Cambia estas contraseñas apenas tengas acceso al sistema en producción.

---

# CLAUDE.md — Skill Técnica para Sistema de Farmacia Laravel + Livewire

> Este documento está diseñado para ser entregado a Claude, Cursor, Copilot, Windsurf u otra IA para continuar el desarrollo del sistema de farmacia sin perder contexto.  
> La IA debe respetar esta arquitectura, nombres, convenciones, estructura de carpetas, reglas de negocio e integraciones ya definidas.

---

## 1. Objetivo del sistema

Construir un sistema de farmacia en Laravel + Livewire que permita gestionar:

- Usuarios, roles y permisos.
- Personas, clientes y proveedores.
- Catálogos maestros de farmacia.
- Productos medicinales y comerciales.
- Inventario por lotes y vencimientos.
- Compras con actualización automática de stock.
- Ventas con descuento automático de stock.
- Caja diaria con apertura, movimientos y cierre.
- Kardex de inventario.
- Ajustes manuales de inventario.
- Alertas de stock bajo, productos vencidos y próximos a vencer.
- Importación futura desde Excel.
- Exportación futura a Excel y PDF.

---

## 2. Stack tecnológico

Usar:

- Laravel 12 o superior.
- Livewire 3.
- MySQL.
- TailwindCSS.
- Bootstrap Icons.
- Spatie Laravel Permission.
- AlpineJS si se requiere interactividad menor.
- `maatwebsite/excel` para exportar/importar Excel.
- `barryvdh/laravel-dompdf` para PDF.
- Laravel Filesystem para almacenamiento de archivos.
- Laravel DB Transactions para operaciones críticas.

---

## 3. Convenciones generales

### 3.1 Eliminación lógica

Todos los registros principales deben usar:

```php
state = 1 // activo
state = 0 // inactivo
```

No se debe borrar físicamente información sensible de operación.

### 3.2 Estados de negocio

Para compras y ventas usar:

```php
status = ACTIVE
status = CANCELLED
```

`state` no reemplaza a `status`.

- `state`: visibilidad o activación lógica.
- `status`: estado operativo del documento.

### 3.3 Transacciones

Toda operación que afecte más de una tabla debe usar:

```php
DB::beginTransaction();

try {
    // lógica
    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
}
```

Aplica a:

- Compras.
- Ventas.
- Anulación de compras.
- Anulación de ventas.
- Apertura/cierre de caja.
- Movimientos de caja.
- Ajustes de inventario.
- Importación Excel.

---

## 4. Estructura de carpetas obligatoria

La arquitectura debe mantenerse así:

```text
app/
├── Helpers/
│   ├── MoneyHelper.php
│   ├── DateHelper.php
│   ├── InventoryHelper.php
│   └── ExportHelper.php
│
├── Livewire/
│   ├── Form/
│   │   ├── ClientForm.php
│   │   ├── SupplierForm.php
│   │   ├── ProductForm.php
│   │   ├── LotForm.php
│   │   ├── PurchaseForm.php
│   │   ├── SaleForm.php
│   │   ├── CashRegisterForm.php
│   │   └── InventoryAdjustmentForm.php
│   │
│   ├── LivewireClient.php
│   ├── LivewireSupplier.php
│   ├── LivewireProduct.php
│   ├── LivewireLot.php
│   ├── LivewirePurchase.php
│   ├── LivewireSale.php
│   ├── LivewireCashRegister.php
│   ├── LivewireInventoryAdjustment.php
│   ├── LivewireKardex.php
│   └── LivewireInventoryAlerts.php
│
├── Models/
│   ├── Client.php
│   ├── Supplier.php
│   ├── Laboratory.php
│   ├── Category.php
│   ├── Presentation.php
│   ├── Brand.php
│   ├── Product.php
│   ├── Lot.php
│   ├── Purchase.php
│   ├── PurchaseDetail.php
│   ├── Sale.php
│   ├── SaleDetail.php
│   ├── CashRegister.php
│   ├── CashMovement.php
│   ├── InventoryMovement.php
│   └── InventoryAdjustment.php
│
├── Services/
│   ├── InventoryService.php
│   ├── SaleService.php
│   ├── PurchaseService.php
│   ├── CashService.php
│   ├── KardexService.php
│   ├── ExcelImportService.php
│   ├── ExcelExportService.php
│   └── PdfReportService.php
│
└── Traits/
    ├── WithToast.php
    ├── WithConfirmableActions.php
    ├── WithSearchableSelect.php
    ├── WithLogicalDelete.php
    └── WithDateFilters.php
```

---

## 5. Carpeta `app/Livewire/Form`

Todas las validaciones deben vivir en formularios Livewire dentro de:

```text
app/Livewire/Form
```

Ejemplo:

```php
namespace App\Livewire\Form;

use Livewire\Form;

class ProductForm extends Form
{
    public $id = null;
    public $id_category = null;
    public $id_laboratory = null;
    public $id_presentation = null;
    public $id_brand = null;
    public $code = '';
    public $barcode = '';
    public $name = '';
    public $generic_name = '';
    public $concentration = '';
    public $description = '';
    public $sale_price = '';
    public $minimum_stock = 0;
    public $requires_prescription = false;
    public $state = 1;

    public function rules()
    {
        return [
            'form.id_category' => 'required|exists:categories,id',
            'form.id_laboratory' => 'nullable|exists:laboratories,id',
            'form.id_presentation' => 'required|exists:presentations,id',
            'form.id_brand' => 'nullable|exists:brands,id',
            'form.code' => 'required|string|max:50|unique:products,code,' . $this->id,
            'form.name' => 'required|string|max:150',
            'form.sale_price' => 'required|numeric|min:0',
            'form.minimum_stock' => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'form.code.required' => 'El código es obligatorio.',
            'form.name.required' => 'El nombre del producto es obligatorio.',
        ];
    }

    public function resetForm()
    {
        $this->reset();
        $this->state = 1;
        $this->minimum_stock = 0;
    }

    public function fillForm($product)
    {
        $this->id = $product->id;
        $this->id_category = $product->id_category;
        $this->id_laboratory = $product->id_laboratory;
        $this->id_presentation = $product->id_presentation;
        $this->id_brand = $product->id_brand;
        $this->code = $product->code;
        $this->barcode = $product->barcode;
        $this->name = $product->name;
        $this->generic_name = $product->generic_name;
        $this->concentration = $product->concentration;
        $this->description = $product->description;
        $this->sale_price = $product->sale_price;
        $this->minimum_stock = $product->minimum_stock;
        $this->requires_prescription = (bool) $product->requires_prescription;
        $this->state = $product->state;
    }
}
```

### Regla importante

No escribir validaciones extensas dentro del componente Livewire principal.  
El componente debe usar:

```php
$this->validate($this->form->rules(), $this->form->messages());
```

Cuando existan reglas diferentes, usar:

```php
$this->validate($this->form->rulesOpen(), $this->form->messages());
$this->validate($this->form->rulesClose(), $this->form->messages());
```

---

## 6. Carpeta `app/Traits`

Los traits deben usarse para lógica repetida entre componentes.

### 6.1 `WithToast.php`

```php
namespace App\Traits;

trait WithToast
{
    public function successToast(string $message): void
    {
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function errorToast(string $message): void
    {
        $this->dispatch('toast', type: 'error', message: $message);
    }

    public function warningToast(string $message): void
    {
        $this->dispatch('toast', type: 'warning', message: $message);
    }
}
```

### 6.2 `WithLogicalDelete.php`

```php
namespace App\Traits;

trait WithLogicalDelete
{
    public function toggleState($model, string $successActive, string $successInactive): void
    {
        $model->state = $model->state ? 0 : 1;
        $model->save();

        $this->dispatch(
            'toast',
            type: $model->state ? 'success' : 'warning',
            message: $model->state ? $successActive : $successInactive
        );
    }
}
```

### 6.3 `WithSearchableSelect.php`

Para reutilizar buscadores dinámicos de:

- Cliente.
- Proveedor.
- Producto.
- Lote.
- Persona.

La lógica concreta puede variar, pero el patrón debe ser:

```php
public $search = '';
public $results = [];
public $selectedLabel = '';
public $selectedId = null;
```

---

## 7. Carpeta `app/Helpers`

Los helpers son para funciones reutilizables puras, no para lógica de negocio compleja.

### 7.1 `MoneyHelper.php`

```php
namespace App\Helpers;

class MoneyHelper
{
    public static function format($amount): string
    {
        return number_format((float) $amount, 2, '.', ',');
    }

    public static function normalize($amount): float
    {
        return round((float) $amount, 2);
    }
}
```

### 7.2 `DateHelper.php`

```php
namespace App\Helpers;

class DateHelper
{
    public static function formatDate($date): string
    {
        return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '-';
    }

    public static function mysqlDate($date): ?string
    {
        return $date ? \Carbon\Carbon::parse($date)->format('Y-m-d') : null;
    }
}
```

### 7.3 `InventoryHelper.php`

```php
namespace App\Helpers;

use App\Models\Lot;

class InventoryHelper
{
    public static function productStock(int $productId): int
    {
        return Lot::where('id_product', $productId)
            ->where('state', 1)
            ->where('quantity_available', '>', 0)
            ->sum('quantity_available');
    }

    public static function hasAvailableStock(int $productId, int $quantity): bool
    {
        return self::productStock($productId) >= $quantity;
    }
}
```

---

## 8. Carpeta `app/Services`

La lógica pesada debe ir en servicios. Livewire debe coordinar, no cargar toda la lógica de negocio.

### 8.1 `InventoryService`

Responsable de:

- Crear o actualizar lotes.
- Descontar stock.
- Restaurar stock.
- Registrar Kardex.
- Validar vencimientos.
- Validar stock disponible.

Métodos sugeridos:

```php
createOrUpdateLotFromPurchase($detail, $purchase)
decreaseStockForSale($detail, $sale)
restoreStockFromSaleCancellation($sale)
reverseStockFromPurchaseCancellation($purchase)
registerMovement(...)
```

### 8.2 `CashService`

Responsable de:

- Abrir caja.
- Registrar ingreso por venta.
- Registrar reversión por venta anulada.
- Registrar egreso manual.
- Registrar ingreso manual.
- Cerrar caja.
- Validar caja abierta.

Métodos sugeridos:

```php
getOpenCashForUser($userId)
openCash($userId, $openingAmount)
registerSaleIncome($sale)
registerSaleCancellationExpense($sale)
registerManualMovement($cash, $type, $concept, $amount, $observation)
closeCash($cash, $countedAmount)
```

### 8.3 `PurchaseService`

Responsable de:

- Registrar compra.
- Crear detalle.
- Crear/actualizar lote.
- Registrar Kardex de entrada.
- Anular compra.

### 8.4 `SaleService`

Responsable de:

- Registrar venta.
- Validar caja abierta.
- Validar lotes.
- Descontar stock.
- Registrar ingreso en caja.
- Registrar Kardex.
- Anular venta.
- Restaurar stock.
- Ajustar caja.

---

## 9. Estructura de vistas

Cada módulo debe usar:

```text
resources/views/livewire/<modulo>/index.blade.php
resources/views/livewire/<modulo>/modal/index.blade.php
```

Cuando el módulo tenga varias acciones:

```text
resources/views/livewire/cash-register/modal/open.blade.php
resources/views/livewire/cash-register/modal/movement.blade.php
resources/views/livewire/cash-register/modal/close.blade.php
```

---

## 10. UI y Tailwind

Todas las vistas deben:

- Ser responsive.
- Usar dark mode.
- Usar tablas con `overflow-x-auto`.
- Usar modales centrados.
- Usar `rounded-xl`, `shadow`, `dark:bg-zinc-900`.
- Usar Bootstrap Icons.
- Usar `@can` para botones.
- Usar `wire:navigate` en menú si aplica.

Ejemplo de botón:

```blade
@can('Crear Productos')
<button wire:click="openModal"
    class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
    <i class="bi bi-capsule-pill mr-1"></i> Nuevo producto
</button>
@endcan
```

---

## 11. Permisos

Cada módulo debe tener:

```text
Ver X
Crear X
Editar X
Cambiar Estado X
```

Permisos definidos:

```text
Ver Clientes
Crear Clientes
Editar Clientes
Cambiar Estado Clientes

Ver Proveedores
Crear Proveedores
Editar Proveedores
Cambiar Estado Proveedores

Ver Laboratorios
Crear Laboratorios
Editar Laboratorios
Cambiar Estado Laboratorios

Ver Categorias
Crear Categorias
Editar Categorias
Cambiar Estado Categorias

Ver Presentaciones
Crear Presentaciones
Editar Presentaciones
Cambiar Estado Presentaciones

Ver Marcas
Crear Marcas
Editar Marcas
Cambiar Estado Marcas

Ver Productos
Crear Productos
Editar Productos
Cambiar Estado Productos

Ver Lotes
Crear Lotes
Editar Lotes
Cambiar Estado Lotes

Ver Compras
Crear Compras
Editar Compras
Cambiar Estado Compras

Ver Ventas
Crear Ventas
Editar Ventas
Cambiar Estado Ventas

Ver Caja
Crear Caja
Editar Caja
Cambiar Estado Caja

Ver Movimientos Caja
Crear Movimientos Caja

Ver Ajustes Inventario
Crear Ajustes Inventario

Ver Kardex
Ver Alertas Inventario
Ver Reportes
Exportar Reportes
```

---

## 12. Menú lateral

El menú debe respetar esta clasificación:

```text
Farmacia
├── Maestros
│   ├── Clientes
│   ├── Proveedores
│   ├── Laboratorios
│   ├── Categorías
│   ├── Presentaciones
│   └── Marcas
│
├── Productos e Inventario
│   ├── Productos
│   ├── Lotes
│   └── Ajustes de Inventario
│
├── Operaciones
│   ├── Compras
│   └── Ventas
│
├── Caja
│   └── Caja
│
└── Reportes y Control
    ├── Kardex
    └── Alertas de Inventario
```

Caja y movimientos de caja pueden apuntar al mismo módulo si los movimientos están dentro de la pantalla de caja:

```php
route('cash-registers')
```

No crear ruta `cash-movements` salvo que exista una pantalla separada.

---

## 13. Base de datos principal

### 13.1 Maestros

- `clients`
- `suppliers`
- `laboratories`
- `categories`
- `presentations`
- `brands`

### 13.2 Productos e inventario

- `products`
- `lots`
- `inventory_movements`
- `inventory_adjustments`

### 13.3 Compras

- `purchases`
- `purchase_details`

### 13.4 Ventas

- `sales`
- `sale_details`

### 13.5 Caja

- `cash_registers`
- `cash_movements`

---

## 14. Flujo de compra

Una compra debe:

1. Seleccionar proveedor.
2. Agregar productos al detalle.
3. Por cada producto registrar:
   - lote
   - vencimiento
   - cantidad
   - precio compra
   - precio venta
4. Crear `purchase`.
5. Crear `purchase_details`.
6. Crear o actualizar `lots`.
7. Incrementar `quantity_in`.
8. Incrementar `quantity_available`.
9. Registrar `inventory_movements` con tipo `IN`.

No debe hacerse compra sin detalle.

---

## 15. Anulación de compra

Una compra se puede anular solo si:

- No está anulada.
- Sus lotes no tienen ventas.
- El stock disponible permite revertir la cantidad ingresada.

Debe:

1. Cambiar `purchases.status` a `CANCELLED`.
2. Cambiar `state` a `0`.
3. Guardar:
   - `cancelled_at`
   - `cancelled_by`
   - `cancel_reason`
4. Descontar stock del lote.
5. Registrar `inventory_movements` tipo `CANCEL`.

---

## 16. Flujo de venta

Una venta debe:

1. Validar caja abierta.
2. Seleccionar cliente o consumidor final.
3. Buscar producto.
4. Seleccionar lote disponible.
5. Validar:
   - stock disponible
   - lote activo
   - lote no vencido
   - cantidad menor o igual al stock
6. Crear `sale`.
7. Crear `sale_details`.
8. Descontar `lots.quantity_available`.
9. Registrar `inventory_movements` tipo `OUT`.
10. Registrar ingreso automático en `cash_movements`.
11. Sumar al `cash_registers.expected_amount`.

---

## 17. Anulación de venta

Una venta no debe editarse. Si hay error, se anula.

Debe:

1. Verificar que no esté anulada.
2. Cambiar `sales.status` a `CANCELLED`.
3. Cambiar `state` a `0`.
4. Guardar:
   - `cancelled_at`
   - `cancelled_by`
   - `cancel_reason`
5. Recorrer `sale_details`.
6. Restaurar stock al lote.
7. Registrar movimiento `RETURN` en Kardex.
8. Registrar movimiento `EXPENSE` en caja si existe caja abierta.
9. Restar del `expected_amount`.

---

## 18. Caja

### 18.1 Apertura

Para empezar el día:

1. Ir a Caja.
2. Clic en Abrir caja.
3. Ingresar monto inicial.
4. Guardar.

Validar que el usuario no tenga una caja abierta.

### 18.2 Movimientos manuales

Registrar:

- `INCOME`
- `EXPENSE`

Deben afectar `expected_amount`.

### 18.3 Cierre

Para cerrar caja:

1. Ingresar monto contado.
2. Calcular diferencia:
   ```php
   difference = counted_amount - expected_amount
   ```
3. Cambiar `status` a `CLOSED`.

---

## 19. Kardex

Tabla:

```text
inventory_movements
```

Campos clave:

- `id_product`
- `id_lot`
- `id_user`
- `movement_type`
- `reference_type`
- `reference_id`
- `quantity`
- `stock_before`
- `stock_after`
- `description`

Tipos:

```text
IN
OUT
RETURN
ADJUSTMENT
CANCEL
```

Referencias:

```text
PURCHASE
SALE
SALE_CANCEL
PURCHASE_CANCEL
INVENTORY_ADJUSTMENT
```

---

## 20. Ajustes de inventario

Sirven para corregir stock por:

- Conteo físico.
- Producto dañado.
- Diferencia de inventario.
- Error de registro.
- Pérdida.

Debe:

1. Seleccionar producto.
2. Seleccionar lote.
3. Elegir:
   - `INCREASE`
   - `DECREASE`
4. Ingresar cantidad.
5. Ingresar motivo.
6. Actualizar lote.
7. Crear `inventory_adjustments`.
8. Crear `inventory_movements` tipo `ADJUSTMENT`.

No permitir disminuir más que el stock disponible.

---

## 21. Alertas

Vista:

```text
LivewireInventoryAlerts
```

Debe mostrar:

- Lotes vencidos.
- Lotes por vencer en 30 días.
- Productos con stock bajo.
- Productos sin stock.

Stock bajo:

```php
stock_total <= minimum_stock
```

---

## 22. Exportación Excel

Usar:

```bash
composer require maatwebsite/excel
```

Estructura:

```text
app/Exports/
├── ProductsExport.php
├── SalesExport.php
├── PurchasesExport.php
├── KardexExport.php
└── CashRegisterExport.php
```

Ejemplo:

```php
namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductsExport implements FromCollection
{
    public function collection()
    {
        return Product::with(['category', 'laboratory', 'presentation', 'brand'])->get();
    }
}
```

Uso:

```php
return Excel::download(new ProductsExport, 'productos.xlsx');
```

---

## 23. Importación Excel

Usar una sola hoja para cliente:

```text
Código
Nombre Producto
Nombre Genérico
Categoría
Laboratorio
Marca
Presentación
Concentración
Código Barras
Lote
Fecha Vencimiento
Precio Compra
Precio Venta
Cantidad
Stock Mínimo
Ubicación
```

Reglas:

- Código de producto único.
- Si el producto tiene varios lotes, repetir el producto en varias filas.
- Fecha en formato `YYYY-MM-DD`.
- No usar celdas combinadas.
- No usar filas vacías intermedias.

Importación debe:

1. Validar filas.
2. Crear categorías si no existen.
3. Crear laboratorios si no existen.
4. Crear marcas si no existen.
5. Crear presentaciones si no existen.
6. Crear producto si no existe.
7. Crear o actualizar lote.
8. Registrar movimiento `IN` si se está cargando stock inicial.
9. Mostrar errores por fila.

---

## 24. PDF

Usar:

```bash
composer require barryvdh/laravel-dompdf
```

Estructura:

```text
app/Services/PdfReportService.php
resources/views/pdf/
├── sale-ticket.blade.php
├── purchase-report.blade.php
├── cash-closing.blade.php
└── inventory-report.blade.php
```

Ejemplo:

```php
$pdf = \PDF::loadView('pdf.sale-ticket', [
    'sale' => $sale,
]);

return $pdf->download('venta-' . $sale->id . '.pdf');
```

PDF recomendados:

- Ticket de venta.
- Cierre de caja.
- Reporte de ventas.
- Reporte de compras.
- Kardex.
- Stock actual.
- Productos vencidos.

---

## 25. Reportes mínimos

Crear módulos o servicios para:

- Ventas por fecha.
- Compras por fecha.
- Ventas por usuario.
- Ventas por cliente.
- Compras por proveedor.
- Caja diaria.
- Kardex por producto.
- Stock actual.
- Stock bajo.
- Productos vencidos.
- Productos próximos a vencer.
- Utilidad por producto:
  ```php
  utilidad = precio_venta - precio_compra
  ```

---

## 26. Rutas

Ejemplo:

```php
Route::get('/clients', LivewireClient::class)->middleware('can:Ver Clientes')->name('clients');
Route::get('/suppliers', LivewireSupplier::class)->middleware('can:Ver Proveedores')->name('suppliers');
Route::get('/laboratories', LivewireLaboratory::class)->middleware('can:Ver Laboratorios')->name('laboratories');
Route::get('/categories', LivewireCategory::class)->middleware('can:Ver Categorias')->name('categories');
Route::get('/presentations', LivewirePresentation::class)->middleware('can:Ver Presentaciones')->name('presentations');
Route::get('/brands', LivewireBrand::class)->middleware('can:Ver Marcas')->name('brands');
Route::get('/products', LivewireProduct::class)->middleware('can:Ver Productos')->name('products');
Route::get('/lots', LivewireLot::class)->middleware('can:Ver Lotes')->name('lots');
Route::get('/purchases', LivewirePurchase::class)->middleware('can:Ver Compras')->name('purchases');
Route::get('/sales', LivewireSale::class)->middleware('can:Ver Ventas')->name('sales');
Route::get('/cash-registers', LivewireCashRegister::class)->middleware('can:Ver Caja')->name('cash-registers');
Route::get('/inventory-adjustments', LivewireInventoryAdjustment::class)->middleware('can:Ver Ajustes Inventario')->name('inventory-adjustments');
Route::get('/kardex', LivewireKardex::class)->middleware('can:Ver Kardex')->name('kardex');
Route::get('/inventory-alerts', LivewireInventoryAlerts::class)->middleware('can:Ver Alertas Inventario')->name('inventory-alerts');
```

---

## 27. Reglas críticas

### No vender vencidos

```php
whereDate('expiration_date', '>=', now()->toDateString())
```

### No vender sin stock

```php
where('quantity_available', '>=', $quantity)
```

### Usar bloqueo en stock

```php
lockForUpdate()
```

### No editar ventas aplicadas

La venta se anula, no se edita.

### No anular compra si lote ya fue vendido

```php
SaleDetail::where('id_lot', $lot->id)->exists()
```

---

## 28. Prompt maestro para Claude

Cuando Claude continúe este proyecto, debe respetar lo siguiente:

```text
Eres un experto en Laravel 12, Livewire 3, MySQL, TailwindCSS y sistemas de farmacia. Debes continuar este sistema respetando la arquitectura existente.

Usa:
- app/Livewire/Form para validaciones.
- app/Traits para lógica reutilizable de Livewire.
- app/Helpers para funciones puras reutilizables.
- app/Services para lógica de negocio compleja.
- transacciones DB en compras, ventas, caja, ajustes y anulaciones.
- Spatie Permission para validar permisos.
- Tailwind con dark mode y responsive.
- Bootstrap Icons.
- eliminación lógica con state.
- estado de negocio con status.
- inventario por lotes.
- ventas por FEFO.
- Kardex obligatorio en toda entrada/salida/ajuste/anulación.
- caja obligatoria para ventas.
- movimientos de caja automáticos por venta y anulación.

No edites ventas aplicadas. Anula y revierte stock.
No borres compras o ventas. Usa CANCELLED.
No vendas productos vencidos.
No permitas stock negativo.
No mezcles stock en products; el stock real está en lots.
No pongas validaciones extensas dentro del componente principal; usa Livewire/Form.
No repitas lógica compleja en componentes; usa Services.
```

---

## 29. Checklist para nuevos módulos

Antes de crear un módulo nuevo, verificar:

- [ ] Tabla SQL.
- [ ] Modelo.
- [ ] Relaciones.
- [ ] Form en `app/Livewire/Form`.
- [ ] Componente Livewire.
- [ ] Vista index.
- [ ] Vista modal.
- [ ] Ruta.
- [ ] Permisos.
- [ ] Botones protegidos con `@can`.
- [ ] Toasts.
- [ ] Modo oscuro.
- [ ] Responsive.
- [ ] Transacción si afecta más de una tabla.
- [ ] Kardex si afecta inventario.
- [ ] Caja si afecta dinero.
- [ ] Reporte o exportación si corresponde.

---

## 30. Conclusión

Este sistema no debe verse como simples CRUD.  
La arquitectura correcta es operacional:

```text
Compra → Lote → Kardex
Venta → Lote → Kardex → Caja
Anulación Venta → Stock + Kardex + Caja
Anulación Compra → Stock + Kardex
Ajuste Inventario → Lote + Kardex
Caja → Apertura + Movimientos + Cierre
Alertas → Stock + Vencimientos
Reportes → Decisiones
```

La prioridad es mantener consistencia de stock, dinero y trazabilidad.