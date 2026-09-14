<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_details';

    protected $fillable = [
        'id_purchase',
        'id_product',
        'batch_code',
        'expiration_date',
        'quantity',
        'purchase_price',
        'sale_price',
        'subtotal',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'id_purchase');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
}