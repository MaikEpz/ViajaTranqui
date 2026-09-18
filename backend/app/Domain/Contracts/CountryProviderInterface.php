<?php

namespace App\Domain\Contracts;

use App\Domain\DTOs\CountryData;

interface CountryProviderInterface
{
    /**
     * Retrieve a list of available destination countries.
     *
     * @return array<CountryData>
     */
    public function getCountries(): array;

    /**
     * Find country by its ISO code (alpha-2 or alpha-3).
     */
    public function findByCode(string $code): ?CountryData;
}
