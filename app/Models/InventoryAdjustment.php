<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $table = 'inventory_adjustments';

    protected $fillable = [
        'id_product',
        'id_lot',
        'id_user',
        'type',
        'quantity',
        'reason',
        'stock_before',
        'stock_after',
        'state',
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