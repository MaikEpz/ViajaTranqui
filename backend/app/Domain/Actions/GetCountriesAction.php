<?php

namespace App\Domain\Actions;

use App\Domain\Contracts\CountryProviderInterface;

/**
 * Caso de uso: Consulta del catálogo de países disponibles.
 * Comunica el dominio con la infraestructura mediante el contrato CountryProviderInterface.
 */
class GetCountriesAction
{
    public function __construct(
        protected CountryProviderInterface $countryProvider
    ) {}

    /**
     * Obtiene el listado normalizado de países para cotizaciones.
     *
     * @return array<\App\Domain\DTOs\CountryData>
     */
    public function execute(): array
    {
        return $this->countryProvider->getCountries();
    }
}
