<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'person';
    protected $fillable = [
        'id_country',
        'name',
        'lastname',
        'ci',
        'phone',
        'address',
        'state'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'id_country');
    }
    public function client()
    {
        return $this->hasOne(Client::class, 'id_person');
    }
    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'id_person');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id_person');
    }
}
