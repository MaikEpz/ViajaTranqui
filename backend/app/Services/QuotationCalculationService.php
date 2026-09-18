<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class QuotationCalculationService
{
    /**
     * Tarifa base diaria obligatoria en USD.
     */
    public const BASE_RATE_PER_DAY = 3.00;

    /**
     * Matriz de recargos porcentuales por región geográfica.
     */
    public const REGION_SURCHARGES = [
        'South America' => 0.0,
        'North America' => 15.0,
        'Europe'        => 20.0,
        'Asia'          => 25.0,
        'Africa'        => 20.0,
        'Oceania'       => 25.0,
    ];

    /**
     * Calcula los detalles financieros y desglose de la cotización.
     *
     * @param string $region Región geográfica (ej. Europe, South America)
     * @param string|Carbon $startDate Fecha de salida
     * @param string|Carbon $endDate Fecha de regreso
     * @return array
     * @throws InvalidArgumentException
     */
    public function calculate(string $region, string|Carbon $startDate, string|Carbon $endDate): array
    {
        $start = is_string($startDate) ? Carbon::parse($startDate)->startOfDay() : $startDate->copy()->startOfDay();
        $end = is_string($endDate) ? Carbon::parse($endDate)->startOfDay() : $endDate->copy()->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidArgumentException('La fecha de regreso no puede ser anterior a la fecha de salida.');
        }

        // Cálculo de días de viaje (inclusivo: día de salida + día de retorno)
        $daysCount = (int) $start->diffInDays($end) + 1;

        // Cálculos base
        $baseRate = self::BASE_RATE_PER_DAY;
        $baseAmount = round($daysCount * $baseRate, 2);

        // Búsqueda de recargo regional con normalización
        $surchargePercentage = $this->getSurchargePercentageForRegion($region);
        $surchargeAmount = round($baseAmount * ($surchargePercentage / 100), 2);

        $totalAmount = round($baseAmount + $surchargeAmount, 2);

        return [
            'days_count'           => $daysCount,
            'base_rate_per_day'    => $baseRate,
            'base_amount'          => $baseAmount,
            'surcharge_percentage' => $surchargePercentage,
            'surcharge_amount'     => $surchargeAmount,
            'total_amount'         => $totalAmount,
            'formatted'            => [
                'base_rate'        => '$' . number_format($baseRate, 2),
                'base_amount'      => '$' . number_format($baseAmount, 2),
                'surcharge_amount' => '$' . number_format($surchargeAmount, 2),
                'total_amount'     => '$' . number_format($totalAmount, 2),
            ],
        ];
    }

    /**
     * Determina el porcentaje de recargo aplicable a una región.
     */
    public function getSurchargePercentageForRegion(string $region): float
    {
        $normalized = trim($region);

        foreach (self::REGION_SURCHARGES as $key => $percentage) {
            if (strcasecmp($key, $normalized) === 0) {
                return (float) $percentage;
            }
        }

        // Si la región viene como 'Americas' genérico, se aplica la tarifa base de Sudamérica (0%)
        if (strcasecmp($normalized, 'Americas') === 0) {
            return 0.0;
        }

        return 0.0;
    }
}
