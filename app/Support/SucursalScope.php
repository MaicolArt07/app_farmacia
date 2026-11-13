<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Misma regla que App\Support\ScopedToSucursal (ese es para componentes
 * Livewire), pero como helper estático para usar desde clases que no son
 * componentes: exports, reportes, comandos.
 */
class SucursalScope
{
    public static function apply(Builder $query, string $column = 'id_sucursal'): Builder
    {
        $user = Auth::user();

        if (! $user || $user->can('Super Admin')) {
            return $query;
        }

        return $query->where($column, $user->id_sucursal);
    }

    public static function applyToRelation(Builder $query, string $relation, string $column = 'id_sucursal'): Builder
    {
        $user = Auth::user();

        if (! $user || $user->can('Super Admin')) {
            return $query;
        }

        return $query->whereHas($relation, fn ($q) => $q->where($column, $user->id_sucursal));
    }
}
