<?php

namespace App\Domain\Actions;

use App\Domain\Contracts\CountryProviderInterface;

class GetCountriesAction
{
    public function __construct(
        protected CountryProviderInterface $countryProvider
    ) {}

    /**
     * Retrieve all destination countries through inverted contract.
     *
     * @return array<\App\Domain\DTOs\CountryData>
     */
    public function execute(): array
    {
        return $this->countryProvider->getCountries();
    }
}
