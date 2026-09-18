<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra los enlaces de servicios e interfaces en el contenedor de dependencias.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Domain\Contracts\CountryProviderInterface::class,
            \App\Infrastructure\Adapters\RestCountriesAdapter::class
        );
    }

    /**
     * Inicialización de servicios de la aplicación en el arranque.
     */
    public function boot(): void
    {
        //
    }
}
