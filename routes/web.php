<?php

use App\Livewire\LivewireHome;
use App\Livewire\LivewirePermissions;
use App\Livewire\LivewirePersona;
use App\Livewire\LivewireUsers;
use App\Livewire\LivewireUserForm;
use App\Livewire\LivewireCliente;
use App\Livewire\LivewireProveedor;
use App\Livewire\LivewireLaboratorio;
use App\Livewire\LivewireEmpresaForm;
use App\Livewire\LivewireSucursal;
use App\Livewire\LivewireMetodoPago;
use App\Livewire\LivewireCategoria;
use App\Livewire\LivewirePresentacion;
use App\Livewire\LivewireProducto;
use App\Livewire\LivewireLote;
use App\Livewire\LivewireCompra;
use App\Livewire\LivewireCompraForm;
use App\Livewire\LivewireVenta;
use App\Livewire\LivewireVentaForm;
use App\Livewire\LivewireAjusteInventario;
use App\Livewire\LivewireCaja;
use App\Livewire\LivewireInventoryAlerts;
use App\Livewire\LivewireKardex;
use App\Livewire\LivewireFactura;
use App\Livewire\LivewireReports;
use App\Models\Factura;
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

    Route::get('/clientes', LivewireCliente::class)
    ->middleware('can:Ver Clientes')
    ->name('clientes');


    Route::get('/proveedores', LivewireProveedor::class)
    ->middleware('can:Ver Proveedores')
    ->name('proveedores');


    Route::get('/laboratorios', LivewireLaboratorio::class)
    ->middleware('can:Ver Laboratorios')
    ->name('laboratorios');


    Route::get('/company', LivewireEmpresaForm::class)
    ->middleware('can:Ver Empresa')
    ->name('company.edit');

    Route::get('/branches', LivewireSucursal::class)
    ->middleware('can:Ver Sucursales')
    ->name('branches');


    Route::get('/payment-methods', LivewireMetodoPago::class)
    ->middleware('can:Ver Metodos Pago')
    ->name('payment-methods');


    Route::get('/categorias', LivewireCategoria::class)
    ->middleware('can:Ver Categorias')
    ->name('categorias');

    Route::get('/presentaciones', LivewirePresentacion::class)
    ->middleware('can:Ver Presentaciones')
    ->name('presentaciones');


    Route::get('/productos', LivewireProducto::class)
    ->middleware('can:Ver Productos')
    ->name('productos');


    Route::get('/lotes', LivewireLote::class)
    ->middleware('can:Ver Lotes')
    ->name('lotes');


    Route::get('/compras', LivewireCompra::class)
    ->middleware('can:Ver Compras')
    ->name('compras');

    Route::get('/compras/create', LivewireCompraForm::class)
    ->middleware('can:Ver Compras')
    ->name('compras.create');

    Route::get('/compras/{purchase}/edit', LivewireCompraForm::class)
    ->middleware('can:Ver Compras')
    ->name('compras.edit');

    Route::get('/compras/{purchase}/view', LivewireCompraForm::class)
    ->middleware('can:Ver Compras')
    ->name('compras.view');

    Route::get('/ventas', LivewireVenta::class)
    ->middleware('can:Ver Ventas')
    ->name('ventas');

    Route::get('/ventas/create', LivewireVentaForm::class)
    ->middleware('can:Crear Ventas')
    ->name('ventas.create');

    Route::get('/ventas/{sale}/view', LivewireVentaForm::class)
    ->middleware('can:Ver Ventas')
    ->name('ventas.view');

    Route::get('/cash-registers', LivewireCaja::class)
    ->middleware('can:Ver Caja')
    ->name('cash-registers');


    Route::get('/inventory-adjustments', LivewireAjusteInventario::class)
        ->middleware('can:Ver Ajustes Inventario')
        ->name('inventory-adjustments');


    Route::get('/inventory-alerts', LivewireInventoryAlerts::class)
        ->middleware('can:Ver Alertas Inventario')
        ->name('inventory-alerts');

    Route::get('/kardex', LivewireKardex::class)
        ->middleware('can:Ver Kardex')
        ->name('kardex');

    Route::get('/invoices', LivewireFactura::class)
        ->middleware('can:Ver Facturas')
        ->name('invoices');

    Route::get('/invoices/{invoice}/pdf', function (Factura $invoice) {
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

    Route::get('/personas', LivewirePersona::class)->middleware('can:Ver Persona')->name('personas');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

});

require __DIR__.'/auth.php';
