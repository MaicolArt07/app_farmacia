<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lot extends Model
{
    use HasFactory;

    protected $table = 'lots';

    protected $fillable = [
        'id_product',
        'batch_code',
        'purchase_price',
        'sale_price',
        'quantity_in',
        'quantity_available',
        'expiration_date',
        'location',
        'state',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'quantity_in' => 'integer',
        'quantity_available' => 'integer',
        'expiration_date' => 'date',
        'state' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class, 'id_lot');
    }
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'id_lot');
    }
}