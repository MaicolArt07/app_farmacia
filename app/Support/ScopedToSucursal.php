<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Filtra una consulta a la sucursal del usuario logueado, salvo que tenga el
 * permiso "Super Admin" (ve todas). El catálogo de productos NO usa esto:
 * solo lotes, cajas, ventas y usuarios se separan por sucursal.
 */
trait ScopedToSucursal
{
    protected function scopeToUserSucursal(Builder $query, string $column = 'id_sucursal'): Builder
    {
        $user = Auth::user();

        if (! $user || $user->can('Super Admin')) {
            return $query;
        }

        return $query->where($column, $user->id_sucursal);
    }
}
