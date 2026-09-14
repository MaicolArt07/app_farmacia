<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'name',
        'description',
        'state',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'id_brand');
    }
}