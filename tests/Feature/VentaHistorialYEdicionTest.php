<?php

use App\Livewire\LivewireVenta;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Lote;
use App\Models\MetodoPago;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function crearProducto(float $precioVenta = 15.00): Producto
{
    $categoria = Categoria::create(['nombre' => 'Analgesicos-'.uniqid(), 'estado' => 1]);
    $presentacion = Presentacion::create(['nombre' => 'Caja-'.uniqid(), 'estado' => 1]);

    return Producto::create([
        'id_categoria' => $categoria->id,
        'id_presentacion' => $presentacion->id,
        'code' => 'TEST-'.uniqid(),
        'nombre' => 'Paracetamol 500mg',
        'precio_venta' => $precioVenta,
        'stock_minimo' => 5,
        'requiere_receta' => false,
        'estado' => 1,
    ]);
}

function crearCaja(User $user, string $status = 'OPEN', float $apertura = 100): Caja
{
    return Caja::create([
        'id_usuario' => $user->id,
        'fecha_apertura' => now()->toDateString(),
        'monto_apertura' => $apertura,
        'periodo_validez' => 'DAILY',
        'expires_at' => Caja::calculateExpiration('DAILY'),
        'monto_esperado' => $apertura,
        'status' => $status,
        'estado' => 1,
    ]);
}

/**
 * Crea una venta ACTIVA ya registrada (sin pasar por el formulario), con un
 * detalle simple, para probar el listado/edición sobre datos existentes.
 */
function crearVenta(Caja $cash, MetodoPago $metodo, array $overrides = []): Venta
{
    $producto = crearProducto();
    $lote = Lote::create([
        'id_producto' => $producto->id,
        'codigo_lote' => 'LOTE-'.uniqid(),
        'precio_compra' => 9,
        'precio_venta' => 15,
        'cantidad_ingresada' => 10,
        'cantidad_disponible' => 10,
        'fecha_vencimiento' => now()->addYear(),
        'estado' => 1,
    ]);

    $sale = Venta::create(array_merge([
        'id_usuario' => $cash->id_usuario,
        'id_caja' => $cash->id,
        'fecha_venta' => now()->toDateString(),
        'id_metodo_pago' => $metodo->id,
        'metodo_pago' => $metodo->nombre,
        'subtotal' => 15,
        'descuento' => 0,
        'total' => 15,
        'monto_efectivo' => 15,
        'monto_qr' => 0,
        'monto_pagado' => 15,
        'monto_cambio' => 0,
        'estado' => 1,
        'status' => 'ACTIVE',
    ], $overrides));

    \App\Models\DetalleVenta::create([
        'id_venta' => $sale->id,
        'id_producto' => $producto->id,
        'id_lote' => $lote->id,
        'quantity' => 1,
        'precio_venta' => 15,
        'subtotal' => 15,
    ]);

    return $sale;
}

function adminAutenticado(): User
{
    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $role->givePermissionTo([
        'Ver Inicio', 'Ver Caja', 'Crear Caja', 'Editar Caja', 'Cambiar Estado Caja',
        'Ver Ventas', 'Crear Ventas', 'Cambiar Estado Ventas', 'Ver Productos', 'Ver Clientes',
    ]);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function trabajadorSoloVentas(): User
{
    $role = Role::firstOrCreate(['name' => 'trabajador', 'guard_name' => 'web']);
    $role->givePermissionTo([
        'Ver Inicio', 'Ver Caja', 'Crear Caja', 'Editar Caja', 'Cambiar Estado Caja',
        'Ver Ventas', 'Crear Ventas', 'Ver Productos', 'Ver Clientes',
    ]);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

beforeEach(function () {
    foreach ([
        'Ver Inicio', 'Ver Caja', 'Crear Caja', 'Editar Caja', 'Cambiar Estado Caja',
        'Ver Ventas', 'Crear Ventas', 'Cambiar Estado Ventas', 'Ver Productos', 'Ver Clientes',
    ] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    MetodoPago::firstOrCreate(['nombre' => 'Efectivo'], ['estado' => 1]);
    MetodoPago::firstOrCreate(['nombre' => 'QR'], ['estado' => 1]);
    MetodoPago::firstOrCreate(['nombre' => 'Mixto'], ['estado' => 1]);
});

// ---------------------------------------------------------------------------
// Historial + filtro de fechas (solo administrador)
// ---------------------------------------------------------------------------

test('el administrador ve ventas de una caja ya cerrada y de otro día (historial completo)', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cajaCerrada = crearCaja($admin, status: 'CLOSED');
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $venta = crearVenta($cajaCerrada, $efectivo, [
        'fecha_venta' => now()->subDays(5)->toDateString(),
    ]);

    Livewire::test(LivewireVenta::class)
        ->assertSee('Venta #'.$venta->id);
});

test('un trabajador NO ve ventas de una caja ya cerrada ni de días anteriores', function () {
    $worker = trabajadorSoloVentas();
    $this->actingAs($worker);

    $cajaCerrada = crearCaja($worker, status: 'CLOSED');
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $venta = crearVenta($cajaCerrada, $efectivo, [
        'fecha_venta' => now()->subDays(5)->toDateString(),
    ]);

    Livewire::test(LivewireVenta::class)
        ->assertDontSee('Venta #'.$venta->id);
});

test('el filtro de fechas del administrador excluye ventas fuera del rango', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cash = crearCaja($admin);
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();

    $ventaDentro = crearVenta($cash, $efectivo, ['fecha_venta' => '2026-01-10']);
    $ventaFuera = crearVenta($cash, $efectivo, ['fecha_venta' => '2026-02-20']);

    Livewire::test(LivewireVenta::class)
        ->set('dateFrom', '2026-01-01')
        ->set('dateTo', '2026-01-31')
        ->assertSee('Venta #'.$ventaDentro->id)
        ->assertDontSee('Venta #'.$ventaFuera->id);
});

test('limpiar el filtro de fechas vuelve a mostrar todas las ventas', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cash = crearCaja($admin);
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $venta = crearVenta($cash, $efectivo, ['fecha_venta' => '2026-02-20']);

    Livewire::test(LivewireVenta::class)
        ->set('dateFrom', '2026-01-01')
        ->set('dateTo', '2026-01-31')
        ->assertDontSee('Venta #'.$venta->id)
        ->call('clearDateFilter')
        ->assertSee('Venta #'.$venta->id);
});

// ---------------------------------------------------------------------------
// Acceso a la edición (ruta + permisos)
// ---------------------------------------------------------------------------

test('un trabajador no puede entrar a la pantalla de editar una venta', function () {
    $worker = trabajadorSoloVentas();
    $this->actingAs($worker);

    $cash = crearCaja($worker);
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $venta = crearVenta($cash, $efectivo);

    $this->get("/ventas/{$venta->id}/edit")->assertForbidden();
});

test('el administrador puede abrir la pantalla de editar cuando la caja sigue abierta', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cash = crearCaja($admin, status: 'OPEN');
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $venta = crearVenta($cash, $efectivo);

    $this->get("/ventas/{$venta->id}/edit")
        ->assertOk()
        ->assertSee('Guardar cambios')
        ->assertSee('Editar pago de venta');
});

test('el administrador es redirigido si intenta editar una venta cuya caja ya está cerrada', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cash = crearCaja($admin, status: 'CLOSED');
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $venta = crearVenta($cash, $efectivo);

    $this->get("/ventas/{$venta->id}/edit")
        ->assertRedirect(route('ventas.view', $venta->id));
});

// ---------------------------------------------------------------------------
// Validaciones y efectos de guardar la edición
// ---------------------------------------------------------------------------

/**
 * Arma el componente ya "posicionado" sobre una venta para editar, sin pasar
 * por el mount() basado en ruta (Livewire::test() no asigna una ruta con
 * nombre). El acceso real vía URL se prueba aparte, por HTTP, más arriba.
 */
function componenteEditando(Venta $venta): \Livewire\Features\SupportTesting\Testable
{
    return Livewire::test(LivewireVenta::class)
        ->set('mode', 'form')
        ->set('editMode', true)
        ->set('viewMode', true)
        ->set('form.id', $venta->id)
        ->set('form.fecha_venta', $venta->fecha_venta->format('Y-m-d'))
        ->set('form.id_metodo_pago', $venta->id_metodo_pago)
        ->set('form.monto_pagado', (string) $venta->monto_pagado)
        ->set('form.monto_qr', (string) $venta->monto_qr);
}

test('saveEdit rechaza un monto recibido menor al total cuando el método es Efectivo', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cash = crearCaja($admin);
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $venta = crearVenta($cash, $efectivo);

    componenteEditando($venta)
        ->set('form.monto_pagado', 5) // el total de la venta es 15
        ->call('saveEdit')
        ->assertHasErrors('form.monto_pagado');

    expect((float) $venta->fresh()->monto_pagado)->toEqual(15.00);
});

test('saveEdit rechaza un monto QR fuera de rango cuando el método es Mixto', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cash = crearCaja($admin);
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $mixto = MetodoPago::where('nombre', 'Mixto')->firstOrFail();
    $venta = crearVenta($cash, $efectivo);

    componenteEditando($venta)
        ->set('form.id_metodo_pago', $mixto->id)
        ->set('form.monto_qr', 15) // igual al total: inválido, debe ser < total
        ->set('form.monto_pagado', 0)
        ->call('saveEdit')
        ->assertHasErrors('form.monto_qr');

    expect($venta->fresh()->metodo_pago)->toBe('Efectivo');
});

test('saveEdit actualiza el método de pago y recalcula el efectivo esperado de la caja', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cash = crearCaja($admin, apertura: 100);
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $qr = MetodoPago::where('nombre', 'QR')->firstOrFail();
    $venta = crearVenta($cash, $efectivo);

    // Antes de editar: la venta en efectivo entra al monto esperado del cajón.
    $cash->recalculateExpected();
    expect((float) $cash->fresh()->monto_esperado)->toBe(115.0); // 100 apertura + 15 efectivo

    componenteEditando($venta)
        ->set('form.id_metodo_pago', $qr->id)
        ->set('form.monto_pagado', 0)
        ->call('saveEdit')
        ->assertHasNoErrors();

    $venta->refresh();
    expect($venta->metodo_pago)->toBe('QR');
    expect((float) $venta->monto_efectivo)->toBe(0.0);
    expect((float) $venta->monto_qr)->toBe(15.0);

    // Al pasar a QR, ese dinero ya no cuenta como efectivo en el cajón.
    expect((float) $cash->fresh()->monto_esperado)->toBe(100.0);
});

test('saveEdit no permite editar una venta si su caja se cerró justo antes de guardar', function () {
    $admin = adminAutenticado();
    $this->actingAs($admin);

    $cash = crearCaja($admin);
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $qr = MetodoPago::where('nombre', 'QR')->firstOrFail();
    $venta = crearVenta($cash, $efectivo);

    $component = componenteEditando($venta);

    // Simula que, mientras el admin tenía el formulario abierto, alguien cerró la caja.
    $cash->update(['status' => 'CLOSED']);

    $component->set('form.id_metodo_pago', $qr->id)
        ->set('form.monto_pagado', 0)
        ->call('saveEdit');

    expect($venta->fresh()->metodo_pago)->toBe('Efectivo');
});

test('un trabajador no puede ejecutar saveEdit aunque invoque el método directamente', function () {
    $worker = trabajadorSoloVentas();
    $this->actingAs($worker);

    $cash = crearCaja($worker);
    $efectivo = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();
    $qr = MetodoPago::where('nombre', 'QR')->firstOrFail();
    $venta = crearVenta($cash, $efectivo);

    componenteEditando($venta)
        ->set('form.id_metodo_pago', $qr->id)
        ->set('form.monto_pagado', 0)
        ->call('saveEdit');

    expect($venta->fresh()->metodo_pago)->toBe('Efectivo');
});
