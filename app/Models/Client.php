<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'id_person',
        'code',
        'type',
        'nit',
        'email',
        'state',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'id_person');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'id_client');
    }
}