<?php

use App\Livewire\LivewireHome;
use App\Livewire\LivewirePermissions;
use App\Livewire\LivewirePermissionForm;
use App\Livewire\LivewirePersona;
use App\Livewire\LivewirePersonaForm;
use App\Livewire\LivewireUsers;
use App\Livewire\LivewireUserForm;
use App\Livewire\LivewireCliente;
use App\Livewire\LivewireClienteForm;
use App\Livewire\LivewireProveedor;
use App\Livewire\LivewireProveedorForm;
use App\Livewire\LivewireLaboratorio;
use App\Livewire\LivewireLaboratorioForm;
use App\Livewire\LivewireCompanyForm;
use App\Livewire\LivewireBranch;
use App\Livewire\LivewireBranchForm;
use App\Livewire\LivewirePaymentMethod;
use App\Livewire\LivewirePaymentMethodForm;
use App\Livewire\LivewireCategoria;
use App\Livewire\LivewireCategoriaForm;
use App\Livewire\LivewirePresentacion;
use App\Livewire\LivewirePresentacionForm;
use App\Livewire\LivewireProducto;
use App\Livewire\LivewireProductoForm;
use App\Livewire\LivewireLote;
use App\Livewire\LivewireLoteForm;
use App\Livewire\LivewireCompra;
use App\Livewire\LivewireCompraForm;
use App\Livewire\LivewireVenta;
use App\Livewire\LivewireVentaForm;
use App\Livewire\LivewireAjusteInventario;
use App\Livewire\LivewireAjusteInventarioForm;
use App\Livewire\LivewireCaja;
use App\Livewire\LivewireCajaForm;
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

    Route::get('/clientes', LivewireCliente::class)
    ->middleware('can:Ver Clientes')
    ->name('clientes');

    Route::get('/clientes/create', LivewireClienteForm::class)
    ->middleware('can:Ver Clientes')
    ->name('clientes.create');

    Route::get('/clientes/{client}/edit', LivewireClienteForm::class)
    ->middleware('can:Ver Clientes')
    ->name('clientes.edit');

    Route::get('/proveedores', LivewireProveedor::class)
    ->middleware('can:Ver Proveedores')
    ->name('proveedores');

    Route::get('/proveedores/create', LivewireProveedorForm::class)
    ->middleware('can:Ver Proveedores')
    ->name('proveedores.create');

    Route::get('/proveedores/{supplier}/edit', LivewireProveedorForm::class)
    ->middleware('can:Ver Proveedores')
    ->name('proveedores.edit');

    Route::get('/laboratorios', LivewireLaboratorio::class)
    ->middleware('can:Ver Laboratorios')
    ->name('laboratorios');

    Route::get('/laboratorios/create', LivewireLaboratorioForm::class)
    ->middleware('can:Ver Laboratorios')
    ->name('laboratorios.create');

    Route::get('/laboratorios/{laboratorio}/edit', LivewireLaboratorioForm::class)
    ->middleware('can:Ver Laboratorios')
    ->name('laboratorios.edit');

    Route::get('/company', LivewireCompanyForm::class)
    ->middleware('can:Ver Empresa')
    ->name('company.edit');

    Route::get('/branches', LivewireBranch::class)
    ->middleware('can:Ver Sucursales')
    ->name('branches');

    Route::get('/branches/create', LivewireBranchForm::class)
    ->middleware('can:Ver Sucursales')
    ->name('branches.create');

    Route::get('/branches/{branch}/edit', LivewireBranchForm::class)
    ->middleware('can:Ver Sucursales')
    ->name('branches.edit');

    Route::get('/payment-methods', LivewirePaymentMethod::class)
    ->middleware('can:Ver Metodos Pago')
    ->name('payment-methods');

    Route::get('/payment-methods/create', LivewirePaymentMethodForm::class)
    ->middleware('can:Ver Metodos Pago')
    ->name('payment-methods.create');

    Route::get('/payment-methods/{paymentMethod}/edit', LivewirePaymentMethodForm::class)
    ->middleware('can:Ver Metodos Pago')
    ->name('payment-methods.edit');

    Route::get('/categorias', LivewireCategoria::class)
    ->middleware('can:Ver Categorias')
    ->name('categorias');

    Route::get('/categorias/create', LivewireCategoriaForm::class)
    ->middleware('can:Ver Categorias')
    ->name('categorias.create');

    Route::get('/categorias/{categoria}/edit', LivewireCategoriaForm::class)
    ->middleware('can:Ver Categorias')
    ->name('categorias.edit');

    Route::get('/presentaciones', LivewirePresentacion::class)
    ->middleware('can:Ver Presentaciones')
    ->name('presentaciones');

    Route::get('/presentaciones/create', LivewirePresentacionForm::class)
    ->middleware('can:Ver Presentaciones')
    ->name('presentaciones.create');

    Route::get('/presentaciones/{presentacion}/edit', LivewirePresentacionForm::class)
    ->middleware('can:Ver Presentaciones')
    ->name('presentaciones.edit');

    Route::get('/productos', LivewireProducto::class)
    ->middleware('can:Ver Productos')
    ->name('productos');

    Route::get('/productos/create', LivewireProductoForm::class)
    ->middleware('can:Ver Productos')
    ->name('productos.create');

    Route::get('/productos/{product}/edit', LivewireProductoForm::class)
    ->middleware('can:Ver Productos')
    ->name('productos.edit');

    Route::get('/lotes', LivewireLote::class)
    ->middleware('can:Ver Lotes')
    ->name('lotes');

    Route::get('/lotes/create', LivewireLoteForm::class)
    ->middleware('can:Ver Lotes')
    ->name('lotes.create');

    Route::get('/lotes/{lot}/edit', LivewireLoteForm::class)
    ->middleware('can:Ver Lotes')
    ->name('lotes.edit');

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

    Route::get('/cash-registers/create', LivewireCajaForm::class)
    ->middleware('can:Crear Caja')
    ->name('cash-registers.create');

    Route::get('/inventory-adjustments', LivewireAjusteInventario::class)
        ->middleware('can:Ver Ajustes Inventario')
        ->name('inventory-adjustments');

    Route::get('/inventory-adjustments/create', LivewireAjusteInventarioForm::class)
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

    Route::get('/personas', LivewirePersona::class)->middleware('can:Ver Persona')->name('personas');
    Route::get('/personas/create', LivewirePersonaForm::class)->middleware('can:Ver Persona')->name('personas.create');
    Route::get('/personas/{person}/edit', LivewirePersonaForm::class)->middleware('can:Ver Persona')->name('personas.edit');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

});

require __DIR__.'/auth.php';
