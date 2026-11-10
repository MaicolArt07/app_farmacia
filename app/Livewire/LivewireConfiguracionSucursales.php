<?php

namespace App\Livewire;

use App\Models\Caja;
use App\Models\Lote;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Herramienta de configuración inicial: separa los datos actuales (todos en
 * una sola sucursal, mal etiquetada como "Casa Matriz") en dos sucursales
 * reales: "Sucursal La Moscú" (donde está hoy TODO lo cargado) y "Casa
 * Matriz" (nueva, vacía). Solo puede ejecutarla un usuario con el permiso
 * "Super Admin". Es una operación de un solo uso, protegida para no
 * correrse dos veces.
 */
class LivewireConfiguracionSucursales extends Component
{
    public $moscu_nombre = 'Sucursal La Moscú';
    public $moscu_numero_sucursal = 1;
    public $moscu_punto_venta = 0;
    public $moscu_municipio = '';
    public $moscu_direccion = '';
    public $moscu_telefono = '';

    public $matriz_nombre = 'Casa Matriz';
    public $matriz_numero_sucursal = 0;
    public $matriz_punto_venta = 0;
    public $matriz_municipio = '';
    public $matriz_direccion = '';
    public $matriz_telefono = '';

    public $confirming = false;

    public function mount()
    {
        if (!Auth::user()?->can('Super Admin')) {
            abort(403);
        }

        $actual = Sucursal::first();

        if ($actual) {
            $this->moscu_numero_sucursal = $actual->numero_sucursal;
            $this->moscu_punto_venta = $actual->punto_venta;
            $this->moscu_municipio = $actual->municipio;
            $this->moscu_direccion = $actual->direccion;
            $this->moscu_telefono = $actual->telefono;
        }
    }

    public function render()
    {
        $sucursales = Sucursal::withCount(['users', 'lots', 'cashRegisters', 'sales'])->get();

        $yaMigrado = $sucursales->count() >= 2;

        $pendientes = [
            'users' => User::whereNull('id_sucursal')->count(),
            'lotes' => Lote::whereNull('id_sucursal')->count(),
            'cajas' => Caja::whereNull('id_sucursal')->count(),
            'ventas' => Venta::whereNull('id_sucursal')->count(),
        ];

        return view('livewire.configuracion.sucursales', [
            'sucursales' => $sucursales,
            'yaMigrado' => $yaMigrado,
            'pendientes' => $pendientes,
        ]);
    }

    public function askConfirmation()
    {
        $this->validate([
            'moscu_nombre' => 'required|string|max:150',
            'moscu_numero_sucursal' => 'required|integer|min:0',
            'moscu_punto_venta' => 'required|integer|min:0',
            'matriz_nombre' => 'required|string|max:150',
            'matriz_numero_sucursal' => 'required|integer|min:0',
            'matriz_punto_venta' => 'required|integer|min:0',
        ], [], [
            'moscu_nombre' => 'nombre de la sucursal Moscú',
            'matriz_nombre' => 'nombre de la Casa Matriz',
        ]);

        $this->confirming = true;
    }

    public function cancelConfirmation()
    {
        $this->confirming = false;
    }

    public function ejecutarMigracion()
    {
        if (!Auth::user()?->can('Super Admin')) {
            abort(403);
        }

        if (Sucursal::count() >= 2) {
            $this->dispatch('toast', type: 'warning', message: 'La migración ya fue ejecutada antes (ya existen 2 o más sucursales).');
            $this->confirming = false;
            return;
        }

        DB::beginTransaction();

        try {
            $moscu = Sucursal::first();

            if (!$moscu) {
                $this->dispatch('toast', type: 'error', message: 'No se encontró ninguna sucursal base para convertir en "Moscú".');
                DB::rollBack();
                return;
            }

            $moscu->update([
                'nombre' => $this->moscu_nombre,
                'numero_sucursal' => $this->moscu_numero_sucursal,
                'punto_venta' => $this->moscu_punto_venta,
                'municipio' => $this->moscu_municipio ?: $moscu->municipio,
                'direccion' => $this->moscu_direccion ?: $moscu->direccion,
                'telefono' => $this->moscu_telefono ?: $moscu->telefono,
            ]);

            $matriz = Sucursal::create([
                'id_empresa' => $moscu->id_empresa,
                'nombre' => $this->matriz_nombre,
                'numero_sucursal' => $this->matriz_numero_sucursal,
                'punto_venta' => $this->matriz_punto_venta,
                'municipio' => $this->matriz_municipio ?: $moscu->municipio,
                'direccion' => $this->matriz_direccion ?: null,
                'telefono' => $this->matriz_telefono ?: null,
                'estado' => 1,
            ]);

            $usersUpdated = User::whereNull('id_sucursal')->update(['id_sucursal' => $moscu->id]);
            $lotesUpdated = Lote::whereNull('id_sucursal')->update(['id_sucursal' => $moscu->id]);
            $cajasUpdated = Caja::whereNull('id_sucursal')->update(['id_sucursal' => $moscu->id]);
            $ventasUpdated = Venta::whereNull('id_sucursal')->update(['id_sucursal' => $moscu->id]);

            DB::commit();

            $this->confirming = false;

            $this->dispatch(
                'toast',
                type: 'success',
                message: "Migración completada. Moscú (id {$moscu->id}): {$usersUpdated} usuarios, {$lotesUpdated} lotes, {$cajasUpdated} cajas, {$ventasUpdated} ventas. Casa Matriz creada (id {$matriz->id})."
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo ejecutar la migración: ' . $e->getMessage());
        }
    }
}
