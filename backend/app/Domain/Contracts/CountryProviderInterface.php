<?php

namespace App\Domain\Contracts;

use App\Domain\DTOs\CountryData;

/**
 * Contrato de dominio para proveedores de información de países.
 * Aplica el principio de Inversión de Dependencias (DIP).
 */
interface CountryProviderInterface
{
    /**
     * Obtiene el listado completo de países habilitados para cotización.
     *
     * @return array<CountryData>
     */
    public function getCountries(): array;

    /**
     * Busca un país específico por su código ISO (alpha-2 o alpha-3).
     */
    public function findByCode(string $code): ?CountryData;
}
