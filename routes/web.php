<?php

use App\Livewire\LivewireHome;
use App\Livewire\LivewirePermissions;
use App\Livewire\LivewirePermissionForm;
use App\Livewire\LivewirePerson;
use App\Livewire\LivewirePersonForm;
use App\Livewire\LivewireUsers;
use App\Livewire\LivewireUserForm;
use App\Livewire\LivewireClient;
use App\Livewire\LivewireClientForm;
use App\Livewire\LivewireSupplier;
use App\Livewire\LivewireSupplierForm;
use App\Livewire\LivewireLaboratory;
use App\Livewire\LivewireLaboratoryForm;
use App\Livewire\LivewireCategory;
use App\Livewire\LivewireCategoryForm;
use App\Livewire\LivewirePresentation;
use App\Livewire\LivewirePresentationForm;
use App\Livewire\LivewireProduct;
use App\Livewire\LivewireProductForm;
use App\Livewire\LivewireLot;
use App\Livewire\LivewireLotForm;
use App\Livewire\LivewirePurchase;
use App\Livewire\LivewirePurchaseForm;
use App\Livewire\LivewireSale;
use App\Livewire\LivewireSaleForm;
use App\Livewire\LivewireInventoryAdjustment;
use App\Livewire\LivewireInventoryAdjustmentForm;
use App\Livewire\LivewireCashRegister;
use App\Livewire\LivewireCashRegisterForm;
use App\Livewire\LivewireInventoryAlerts;
use App\Livewire\LivewireKardex;
use App\Livewire\LivewireInvoice;
use App\Livewire\LivewireReports;
use App\Models\Invoice;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectToFirstPermission;
use App\Http\Controllers\ReportExportController;
use Barryvdh\DomPDF\Facade\Pdf;


// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

Route::get('/', LivewireHome::class)
    ->middleware(['auth', RedirectToFirstPermission::class])
    ->name('home');
    
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Route::get('/', function () {
    //     return view('dashboard');
    // })->name('home');

    Route::get('/', LivewireHome::class)->middleware('can:Ver Inicio')->name('home');

    Route::get('/clients', LivewireClient::class)
    ->middleware('can:Ver Clientes')
    ->name('clients');

    Route::get('/clients/create', LivewireClientForm::class)
    ->middleware('can:Ver Clientes')
    ->name('clients.create');

    Route::get('/clients/{client}/edit', LivewireClientForm::class)
    ->middleware('can:Ver Clientes')
    ->name('clients.edit');

    Route::get('/suppliers', LivewireSupplier::class)
    ->middleware('can:Ver Proveedores')
    ->name('suppliers');

    Route::get('/suppliers/create', LivewireSupplierForm::class)
    ->middleware('can:Ver Proveedores')
    ->name('suppliers.create');

    Route::get('/suppliers/{supplier}/edit', LivewireSupplierForm::class)
    ->middleware('can:Ver Proveedores')
    ->name('suppliers.edit');

    Route::get('/laboratories', LivewireLaboratory::class)
    ->middleware('can:Ver Laboratorios')
    ->name('laboratories');

    Route::get('/laboratories/create', LivewireLaboratoryForm::class)
    ->middleware('can:Ver Laboratorios')
    ->name('laboratories.create');

    Route::get('/laboratories/{laboratory}/edit', LivewireLaboratoryForm::class)
    ->middleware('can:Ver Laboratorios')
    ->name('laboratories.edit');

    Route::get('/categories', LivewireCategory::class)
    ->middleware('can:Ver Categorias')
    ->name('categories');

    Route::get('/categories/create', LivewireCategoryForm::class)
    ->middleware('can:Ver Categorias')
    ->name('categories.create');

    Route::get('/categories/{category}/edit', LivewireCategoryForm::class)
    ->middleware('can:Ver Categorias')
    ->name('categories.edit');

    Route::get('/presentations', LivewirePresentation::class)
    ->middleware('can:Ver Presentaciones')
    ->name('presentations');

    Route::get('/presentations/create', LivewirePresentationForm::class)
    ->middleware('can:Ver Presentaciones')
    ->name('presentations.create');

    Route::get('/presentations/{presentation}/edit', LivewirePresentationForm::class)
    ->middleware('can:Ver Presentaciones')
    ->name('presentations.edit');

    Route::get('/products', LivewireProduct::class)
    ->middleware('can:Ver Productos')
    ->name('products');

    Route::get('/products/create', LivewireProductForm::class)
    ->middleware('can:Ver Productos')
    ->name('products.create');

    Route::get('/products/{product}/edit', LivewireProductForm::class)
    ->middleware('can:Ver Productos')
    ->name('products.edit');

    Route::get('/lots', LivewireLot::class)
    ->middleware('can:Ver Lotes')
    ->name('lots');

    Route::get('/lots/create', LivewireLotForm::class)
    ->middleware('can:Ver Lotes')
    ->name('lots.create');

    Route::get('/lots/{lot}/edit', LivewireLotForm::class)
    ->middleware('can:Ver Lotes')
    ->name('lots.edit');

    Route::get('/purchases', LivewirePurchase::class)
    ->middleware('can:Ver Compras')
    ->name('purchases');

    Route::get('/purchases/create', LivewirePurchaseForm::class)
    ->middleware('can:Ver Compras')
    ->name('purchases.create');

    Route::get('/purchases/{purchase}/edit', LivewirePurchaseForm::class)
    ->middleware('can:Ver Compras')
    ->name('purchases.edit');

    Route::get('/purchases/{purchase}/view', LivewirePurchaseForm::class)
    ->middleware('can:Ver Compras')
    ->name('purchases.view');

    Route::get('/sales', LivewireSale::class)
    ->middleware('can:Ver Ventas')
    ->name('sales');

    Route::get('/sales/create', LivewireSaleForm::class)
    ->middleware('can:Crear Ventas')
    ->name('sales.create');

    Route::get('/sales/{sale}/view', LivewireSaleForm::class)
    ->middleware('can:Ver Ventas')
    ->name('sales.view');

    Route::get('/cash-registers', LivewireCashRegister::class)
    ->middleware('can:Ver Caja')
    ->name('cash-registers');

    Route::get('/cash-registers/create', LivewireCashRegisterForm::class)
    ->middleware('can:Crear Caja')
    ->name('cash-registers.create');

    Route::get('/inventory-adjustments', LivewireInventoryAdjustment::class)
        ->middleware('can:Ver Ajustes Inventario')
        ->name('inventory-adjustments');

    Route::get('/inventory-adjustments/create', LivewireInventoryAdjustmentForm::class)
        ->middleware('can:Crear Ajustes Inventario')
        ->name('inventory-adjustments.create');

    Route::get('/inventory-alerts', LivewireInventoryAlerts::class)
        ->middleware('can:Ver Alertas Inventario')
        ->name('inventory-alerts');

    Route::get('/kardex', LivewireKardex::class)
        ->middleware('can:Ver Kardex')
        ->name('kardex');

    Route::get('/invoices', LivewireInvoice::class)
        ->middleware('can:Ver Facturas')
        ->name('invoices');

    Route::get('/invoices/{invoice}/pdf', function (Invoice $invoice) {
        $invoice->load(['sale.details.product', 'sale.client.person']);

        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice])
            ->stream('factura-' . ($invoice->numero_factura ?? $invoice->id) . '.pdf');
    })->middleware('can:Ver Facturas')->name('invoices.pdf');

    Route::get('/reports', LivewireReports::class)
        ->middleware('can:Ver Reportes')
        ->name('reports');

    Route::middleware('can:Exportar Reportes')->group(function () {
        Route::get('/reports/daily-book/export', [ReportExportController::class, 'dailyBook'])->name('reports.daily-book.export');
        Route::get('/reports/sales/export', [ReportExportController::class, 'sales'])->name('reports.sales.export');
        Route::get('/reports/purchases/export', [ReportExportController::class, 'purchases'])->name('reports.purchases.export');
        Route::get('/reports/kardex/export', [ReportExportController::class, 'kardex'])->name('reports.kardex.export');
        Route::get('/reports/stock/export', [ReportExportController::class, 'stock'])->name('reports.stock.export');
        Route::get('/reports/inventory-alerts/export', [ReportExportController::class, 'inventoryAlerts'])->name('reports.inventory-alerts.export');
    });

    Route::get('/users', LivewireUsers::class)->middleware('can:Ver Usuarios')->name('users');
    Route::get('/users/create', LivewireUserForm::class)->middleware('can:Ver Usuarios')->name('users.create');
    Route::get('/users/{user}/edit', LivewireUserForm::class)->middleware('can:Ver Usuarios')->name('users.edit');

    Route::get('/permissions', LivewirePermissions::class)->middleware('can:Ver Permisos')->name('permissions');
    Route::get('/permissions/create', LivewirePermissionForm::class)->middleware('can:Ver Permisos')->name('permissions.create');
    Route::get('/permissions/{permission}/edit', LivewirePermissionForm::class)->middleware('can:Ver Permisos')->name('permissions.edit');

    Route::get('/person', LivewirePerson::class)->middleware('can:Ver Persona')->name('person');
    Route::get('/person/create', LivewirePersonForm::class)->middleware('can:Ver Persona')->name('person.create');
    Route::get('/person/{person}/edit', LivewirePersonForm::class)->middleware('can:Ver Persona')->name('person.edit');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

});

require __DIR__.'/auth.php';
