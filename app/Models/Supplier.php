<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'id_person',
        'company_name',
        'contact_position',
        'nit',
        'email',
        'state',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'id_person');
    }
    public function purchases()
{
    return $this->hasMany(Purchase::class, 'id_supplier');
}
}