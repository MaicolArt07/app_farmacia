<?php

namespace App\Providers;

use App\Services\Facturacion\FacturacionServiceInterface;
use App\Services\Facturacion\SimulatedFacturacionService;
use App\Services\Facturacion\SinFacturacionService;
use Illuminate\Support\ServiceProvider;

class FacturacionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FacturacionServiceInterface::class, function ($app) {
            return config('facturacion.modo') === 'real'
                ? $app->make(SinFacturacionService::class)
                : $app->make(SimulatedFacturacionService::class);
        });
    }
}
