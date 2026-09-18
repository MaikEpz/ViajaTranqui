<?php

namespace App\Domain\Actions;

use App\Domain\DTOs\PricingBreakdownData;
use App\Domain\Exceptions\InvalidTravelDatesException;
use Carbon\Carbon;

/**
 * Caso de uso: Cálculo de cotización de seguro de viaje.
 * Aplica la tarifa base diaria y la matriz de recargos por región geográfica.
 */
class CalculateQuotationAction
{
    // Tarifa base obligatoria en USD por día de cobertura
    public const BASE_RATE_PER_DAY = 3.00;

    // Matriz de recargos porcentuales según el continente de destino
    public const REGION_SURCHARGES = [
        'South America' => 0.0,
        'North America' => 15.0,
        'Europe'        => 20.0,
        'Asia'          => 25.0,
        'Africa'        => 20.0,
        'Oceania'       => 25.0,
    ];

    /**
     * Ejecuta el cálculo financiero de la cotización.
     *
     * @param string $region Región geográfica del país de destino
     * @param string|Carbon $startDate Fecha de salida del viaje
     * @param string|Carbon $endDate Fecha de retorno del viaje
     * @return PricingBreakdownData DTO inmutable con el desglose económico
     * @throws InvalidTravelDatesException Si la fecha de retorno es anterior a la de salida
     */
    public function execute(string $region, string|Carbon $startDate, string|Carbon $endDate): PricingBreakdownData
    {
        $start = is_string($startDate) ? Carbon::parse($startDate)->startOfDay() : $startDate->copy()->startOfDay();
        $end = is_string($endDate) ? Carbon::parse($endDate)->startOfDay() : $endDate->copy()->startOfDay();

        // Validación de regla de negocio: el regreso debe ser igual o posterior a la salida
        if ($end->lt($start)) {
            throw new InvalidTravelDatesException();
        }

        // Cálculo de días de cobertura (inclusivo: día de salida + día de retorno)
        $daysCount = (int) $start->diffInDays($end) + 1;
        $baseRate = self::BASE_RATE_PER_DAY;
        $baseAmount = round($daysCount * $baseRate, 2);

        // Resolución de porcentaje de recargo regional
        $surchargePercentage = $this->resolveSurchargePercentage($region);
        $surchargeAmount = round($baseAmount * ($surchargePercentage / 100), 2);
        $totalAmount = round($baseAmount + $surchargeAmount, 2);

        return new PricingBreakdownData(
            daysCount: $daysCount,
            baseRatePerDay: $baseRate,
            baseAmount: $baseAmount,
            surchargePercentage: $surchargePercentage,
            surchargeAmount: $surchargeAmount,
            totalAmount: $totalAmount,
        );
    }

    /**
     * Resuelve el porcentaje de recargo aplicable según la región geográfica.
     */
    public function resolveSurchargePercentage(string $region): float
    {
        $normalized = trim($region);

        foreach (self::REGION_SURCHARGES as $key => $percentage) {
            if (strcasecmp($key, $normalized) === 0) {
                return (float) $percentage;
            }
        }

        // Si la región viene como 'Americas' genérico, se asume tarifa base sudamericana (0%)
        if (strcasecmp($normalized, 'Americas') === 0) {
            return 0.0;
        }

        return 0.0;
    }
}
