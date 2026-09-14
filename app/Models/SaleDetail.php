<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleDetail extends Model
{
    use HasFactory;

    protected $table = 'sale_details';

    protected $fillable = [
        'id_sale',
        'id_product',
        'id_lot',
        'quantity',
        'sale_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sale_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'id_sale');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class, 'id_lot');
    }
}