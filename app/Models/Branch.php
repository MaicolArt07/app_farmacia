<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'id_company',
        'numero_sucursal',
        'punto_venta',
        'name',
        'municipio',
        'address',
        'phone',
        'state',
    ];

    protected $casts = [
        'numero_sucursal' => 'integer',
        'punto_venta' => 'integer',
        'state' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'id_company');
    }
}
