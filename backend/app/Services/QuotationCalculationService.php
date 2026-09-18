<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class QuotationCalculationService
{
    /**
     * Daily base rate in USD.
     */
    public const BASE_RATE_PER_DAY = 3.00;

    /**
     * Surcharges by geographical region.
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
     * Calculate quotation pricing details.
     *
     * @param string $region Geographical region (e.g., Europe, South America)
     * @param string|Carbon $startDate Departure date
     * @param string|Carbon $endDate Return date
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

        // Calculate travel days (inclusive: departure day + return day)
        $daysCount = (int) $start->diffInDays($end) + 1;

        // Base calculations
        $baseRate = self::BASE_RATE_PER_DAY;
        $baseAmount = round($daysCount * $baseRate, 2);

        // Region surcharge lookup with case-insensitive / normalized fallback
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
     * Resolve surcharge percentage for a region.
     */
    public function getSurchargePercentageForRegion(string $region): float
    {
        $normalized = trim($region);

        foreach (self::REGION_SURCHARGES as $key => $percentage) {
            if (strcasecmp($key, $normalized) === 0) {
                return (float) $percentage;
            }
        }

        // Americas subregion fallback checks if needed
        if (strcasecmp($normalized, 'Americas') === 0) {
            return 0.0; // Default to South America rate if unspecified
        }

        return 0.0;
    }
}
