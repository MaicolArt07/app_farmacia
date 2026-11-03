<?php

use App\Livewire\LivewireCaja;
use App\Livewire\LivewireVenta;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Lote;
use App\Models\MetodoPago;
use App\Models\MovimientoInventario;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function crearProductoConStock(int $cantidad = 20, float $precioVenta = 15.00): Producto
{
    $categoria = Categoria::create(['nombre' => 'Analgesicos', 'estado' => 1]);
    $presentacion = Presentacion::create(['nombre' => 'Caja', 'estado' => 1]);

    $producto = Producto::create([
        'id_categoria' => $categoria->id,
        'id_presentacion' => $presentacion->id,
        'code' => 'TEST-'.uniqid(),
        'nombre' => 'Paracetamol 500mg',
        'precio_venta' => $precioVenta,
        'stock_minimo' => 5,
        'requiere_receta' => false,
        'estado' => 1,
    ]);

    Lote::create([
        'id_producto' => $producto->id,
        'codigo_lote' => 'LOTE-'.uniqid(),
        'precio_compra' => $precioVenta * 0.6,
        'precio_venta' => $precioVenta,
        'cantidad_ingresada' => $cantidad,
        'cantidad_disponible' => $cantidad,
        'fecha_vencimiento' => now()->addYear(),
        'estado' => 1,
    ]);

    return $producto;
}

function trabajadorAutenticado(): User
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
        'Ver Ventas', 'Crear Ventas', 'Ver Productos', 'Ver Clientes',
    ] as $permission) {
        Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    MetodoPago::firstOrCreate(['nombre' => 'Efectivo'], ['estado' => 1]);
});

test('a worker can open a cash register', function () {
    $worker = trabajadorAutenticado();
    $this->actingAs($worker);

    Livewire::test(LivewireCaja::class)
        ->call('openOpenModal')
        ->set('openForm.opening_amount', 100)
        ->set('openForm.validity_period', 'DAILY')
        ->call('saveOpen')
        ->assertHasNoErrors();

    $cash = Caja::where('id_usuario', $worker->id)->first();

    expect($cash)->not->toBeNull();
    expect($cash->status)->toBe('OPEN');
    expect((float) $cash->monto_apertura)->toBe(100.0);
});

test('a worker cannot open two cash registers at the same time', function () {
    $worker = trabajadorAutenticado();
    $this->actingAs($worker);

    Caja::create([
        'id_usuario' => $worker->id,
        'fecha_apertura' => now()->toDateString(),
        'monto_apertura' => 50,
        'periodo_validez' => 'DAILY',
        'expires_at' => Caja::calculateExpiration('DAILY'),
        'monto_esperado' => 50,
        'status' => 'OPEN',
        'estado' => 1,
    ]);

    Livewire::test(LivewireCaja::class)
        ->call('openOpenModal')
        ->set('openForm.opening_amount', 999)
        ->set('openForm.validity_period', 'DAILY')
        ->call('saveOpen');

    expect(Caja::where('id_usuario', $worker->id)->where('status', 'OPEN')->count())->toBe(1);
});

test('a worker can complete a full sale once the cash register is open, and stock is discounted', function () {
    $worker = trabajadorAutenticado();
    $this->actingAs($worker);
    $producto = crearProductoConStock(cantidad: 20, precioVenta: 15.00);
    $lote = $producto->lots()->first();

    $cash = Caja::create([
        'id_usuario' => $worker->id,
        'fecha_apertura' => now()->toDateString(),
        'monto_apertura' => 100,
        'periodo_validez' => 'DAILY',
        'expires_at' => Caja::calculateExpiration('DAILY'),
        'monto_esperado' => 100,
        'status' => 'OPEN',
        'estado' => 1,
    ]);

    $metodoPago = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();

    $component = Livewire::test(LivewireVenta::class)
        ->set('details', [[
            'id_producto' => $producto->id,
            'id_lote' => $lote->id,
            'product_name' => $producto->nombre,
            'quantity' => 3,
            'available' => $lote->cantidad_disponible,
            'sale_price' => 15.00,
            'subtotal' => 45.00,
        ]])
        ->set('form.fecha_venta', now()->format('Y-m-d'))
        ->set('form.id_metodo_pago', $metodoPago->id)
        ->set('form.monto_pagado', 50)
        ->call('calculateTotals')
        ->call('save');

    $component->assertHasNoErrors();

    $sale = Venta::where('id_usuario', $worker->id)->first();

    expect($sale)->not->toBeNull();
    expect((float) $sale->total)->toBe(45.0);
    expect($sale->details)->toHaveCount(1);

    $lote->refresh();
    expect($lote->cantidad_disponible)->toBe(17);

    expect(
        MovimientoInventario::where('id_producto', $producto->id)
            ->where('tipo_movimiento', 'OUT')
            ->where('reference_type', 'SALE')
            ->where('reference_id', $sale->id)
            ->exists()
    )->toBeTrue();
});

test('a sale is rejected when there is no open cash register', function () {
    $worker = trabajadorAutenticado();
    $this->actingAs($worker);
    $producto = crearProductoConStock();
    $lote = $producto->lots()->first();
    $metodoPago = MetodoPago::where('nombre', 'Efectivo')->firstOrFail();

    Livewire::test(LivewireVenta::class)
        ->set('details', [[
            'id_producto' => $producto->id,
            'id_lote' => $lote->id,
            'quantity' => 1,
            'available' => $lote->cantidad_disponible,
            'sale_price' => 15.00,
            'subtotal' => 15.00,
        ]])
        ->set('form.fecha_venta', now()->format('Y-m-d'))
        ->set('form.id_metodo_pago', $metodoPago->id)
        ->set('form.monto_pagado', 20)
        ->call('save');

    expect(Venta::count())->toBe(0);
    expect($lote->fresh()->cantidad_disponible)->toBe($lote->cantidad_disponible);
});

test('a worker without the Ventas permission cannot open the sales screen', function () {
    $role = Role::firstOrCreate(['name' => 'solo-caja', 'guard_name' => 'web']);
    $role->givePermissionTo(['Ver Inicio', 'Ver Caja']);

    $worker = User::factory()->create();
    $worker->assignRole($role);
    $this->actingAs($worker);

    $this->get('/ventas')->assertForbidden();
});
