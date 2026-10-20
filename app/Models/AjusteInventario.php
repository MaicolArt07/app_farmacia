<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AjusteInventario extends Model
{
    use HasFactory;

    protected $table = 'ajustes_inventario';

    protected $fillable = [
        'id_product',
        'id_lot',
        'id_user',
        'type',
        'quantity',
        'motivo',
        'stock_before',
        'stock_after',
        'estado',
    ];

    public function product()
    {
        return $this->belongsTo(Producto::class, 'id_product');
    }

    public function lot()
    {
        return $this->belongsTo(Lote::class, 'id_lot');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
