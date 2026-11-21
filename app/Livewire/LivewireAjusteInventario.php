<?php

namespace App\Livewire;

use App\Models\Lote;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use App\Models\AjusteInventario;
use App\Livewire\Form\AjusteInventarioForm;
use Illuminate\Support\Facades\Auth;

class LivewireAjusteInventario extends Component
{
    use WithPagination;

    public $search = '';

    public AjusteInventarioForm $form;

    public $modalVisible = false;

    public $productSearch = '';
    public $productResults = [];
    public $selectedProductLabel = '';

    public $lotSearch = '';
    public $lotResults = [];
    public $selectedLotLabel = '';

    public $viewingId = null;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = AjusteInventario::with(['product', 'lot', 'user']);

        $user = Auth::user();
        if ($user && ! $user->can('Super Admin')) {
            $query->whereHas('lot', fn ($q) => $q->where('id_sucursal', $user->id_sucursal));
        }

        if ($this->search) {
            // Agrupado para que el "o" no se salte el filtro de sucursal de arriba.
            $query->where(fn ($w) => $w
                ->whereHas('product', fn ($q) => $q->buscarNombre($this->search, ['code']))
                ->orWhereHas('lot', function ($q) {
                    $q->where('codigo_lote', 'like', "%{$this->search}%");
                }));
        }

        return view('livewire.ajuste-inventario.index', [
            'adjustments' => $query->orderBy('id', 'desc')->paginate(10),
            'viewing' => $this->viewingId ? AjusteInventario::with(['product', 'lot', 'user'])->find($this->viewingId) : null,
        ]);
    }

    public function view($id)
    {
        $this->viewingId = $id;
    }

    public function closeView()
    {
        $this->viewingId = null;
    }

    public function create()
    {
        $this->form->resetForm();
        $this->selectedProductLabel = '';
        $this->productSearch = '';
        $this->productResults = [];
        $this->selectedLotLabel = '';
        $this->lotSearch = '';
        $this->lotResults = [];
        $this->modalVisible = true;
    }

    public function updatedProductSearch()
    {
        if (strlen(trim($this->productSearch)) < 2) {
            $this->productResults = [];
            return;
        }

        $this->productResults = Producto::where('estado', 1)
            ->buscarNombre($this->productSearch, ['code', 'nombre_generico'])
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'label' => $item->code . ' - ' . $item->nombre_completo,
            ])
            ->toArray();
    }

    public function selectProduct($id)
    {
        $product = Producto::findOrFail($id);

        $this->form->id_producto = $product->id;
        $this->selectedProductLabel = $product->code . ' - ' . $product->nombre_completo;

        $this->productSearch = '';
        $this->productResults = [];

        $this->clearLot();
    }

    public function resetProduct()
    {
        $this->form->id_producto = null;
        $this->selectedProductLabel = '';
        $this->productSearch = '';
        $this->productResults = [];
        $this->clearLot();
    }

    public function updatedLotSearch()
    {
        if (!$this->form->id_producto || strlen(trim($this->lotSearch)) < 1) {
            $this->lotResults = [];
            return;
        }

        $user = Auth::user();

        $this->lotResults = Lote::where('id_producto', $this->form->id_producto)
            ->where('estado', 1)
            ->when($user && ! $user->can('Super Admin'), fn ($q) => $q->where('id_sucursal', $user->id_sucursal))
            ->where(function ($q) {
                $q->where('codigo_lote', 'like', "%{$this->lotSearch}%")
                  ->orWhere('fecha_vencimiento', 'like', "%{$this->lotSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($lot) => [
                'id' => $lot->id,
                'label' => $lot->codigo_lote . ' | Vence: ' . (optional($lot->fecha_vencimiento)->format('d/m/Y') ?? 'Sin vencimiento') . ' | Stock: ' . $lot->cantidad_disponible,
            ])
            ->toArray();
    }

    public function selectLot($id)
    {
        $lot = Lote::findOrFail($id);

        $this->form->id_lote = $lot->id;
        $this->selectedLotLabel = $lot->codigo_lote . ' | Vence: ' . (optional($lot->fecha_vencimiento)->format('d/m/Y') ?? 'Sin vencimiento') . ' | Stock: ' . $lot->cantidad_disponible;

        $this->lotSearch = '';
        $this->lotResults = [];
    }

    public function clearLot()
    {
        $this->form->id_lote = null;
        $this->selectedLotLabel = '';
        $this->lotSearch = '';
        $this->lotResults = [];
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        DB::beginTransaction();

        try {
            $lot = Lote::where('id', $this->form->id_lote)
                ->where('id_producto', $this->form->id_producto)
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = $lot->cantidad_disponible;

            if ($this->form->type === 'DECREASE' && $this->form->quantity > $lot->cantidad_disponible) {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'No puede disminuir más stock del disponible.');
                return;
            }

            if ($this->form->type === 'INCREASE') {
                $lot->cantidad_disponible += (int) $this->form->quantity;
                $lot->cantidad_ingresada += (int) $this->form->quantity;
                $movementType = 'ADJUSTMENT';
                $description = 'Ajuste positivo de inventario.';
            } else {
                $lot->cantidad_disponible -= (int) $this->form->quantity;
                $movementType = 'ADJUSTMENT';
                $description = 'Ajuste negativo de inventario.';
            }

            $lot->save();

            $adjustment = AjusteInventario::create([
                'id_producto' => $this->form->id_producto,
                'id_lote' => $this->form->id_lote,
                'id_usuario' => auth()->id(),
                'type' => $this->form->type,
                'quantity' => $this->form->quantity,
                'motivo' => $this->form->reason,
                'stock_before' => $stockBefore,
                'stock_after' => $lot->cantidad_disponible,
                'estado' => 1,
            ]);

            MovimientoInventario::create([
                'id_producto' => $this->form->id_producto,
                'id_lote' => $this->form->id_lote,
                'id_usuario' => auth()->id(),
                'tipo_movimiento' => $movementType,
                'reference_type' => 'INVENTORY_ADJUSTMENT',
                'reference_id' => $adjustment->id,
                'quantity' => (int) $this->form->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $lot->cantidad_disponible,
                'descripcion' => $description . ' Motivo: ' . $this->form->reason,
            ]);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Ajuste registrado correctamente.');
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo registrar el ajuste.');
        }
    }

    public function cancel()
    {
        $this->modalVisible = false;
        $this->form->resetForm();
    }
}
