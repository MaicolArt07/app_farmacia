<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presentation extends Model
{
    use HasFactory;

    protected $table = 'presentations';

    protected $fillable = [
        'name',
        'description',
        'state',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'id_presentation');
    }
}