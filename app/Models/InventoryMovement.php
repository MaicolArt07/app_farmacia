<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'id_product',
        'id_lot',
        'id_user',
        'movement_type',
        'reference_type',
        'reference_id',
        'quantity',
        'stock_before',
        'stock_after',
        'description',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class, 'id_lot');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}