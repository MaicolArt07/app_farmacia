<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'id_category',
        'id_laboratory',
        'id_presentation',
        'code',
        'barcode',
        'name',
        'generic_name',
        'concentration',
        'description',
        'sale_price',
        'minimum_stock',
        'requires_prescription',
        'state',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'requires_prescription' => 'boolean',
        'minimum_stock' => 'integer',
        'state' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category');
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'id_laboratory');
    }

    public function presentation()
    {
        return $this->belongsTo(Presentation::class, 'id_presentation');
    }

    public function lots()
    {
        return $this->hasMany(Lot::class, 'id_product');
    }
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'id_product');
    }
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class, 'id_product');
    }
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'id_product');
    }
}