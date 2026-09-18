<?php

namespace App\Domain\Actions;

use App\Domain\DTOs\PricingBreakdownData;
use App\Domain\Exceptions\InvalidTravelDatesException;
use Carbon\Carbon;

class CalculateQuotationAction
{
    public const BASE_RATE_PER_DAY = 3.00;

    public const REGION_SURCHARGES = [
        'South America' => 0.0,
        'North America' => 15.0,
        'Europe'        => 20.0,
        'Asia'          => 25.0,
        'Africa'        => 20.0,
        'Oceania'       => 25.0,
    ];

    /**
     * Execute quotation pricing calculation use case.
     */
    public function execute(string $region, string|Carbon $startDate, string|Carbon $endDate): PricingBreakdownData
    {
        $start = is_string($startDate) ? Carbon::parse($startDate)->startOfDay() : $startDate->copy()->startOfDay();
        $end = is_string($endDate) ? Carbon::parse($endDate)->startOfDay() : $endDate->copy()->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidTravelDatesException();
        }

        $daysCount = (int) $start->diffInDays($end) + 1;
        $baseRate = self::BASE_RATE_PER_DAY;
        $baseAmount = round($daysCount * $baseRate, 2);

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

    public function resolveSurchargePercentage(string $region): float
    {
        $normalized = trim($region);

        foreach (self::REGION_SURCHARGES as $key => $percentage) {
            if (strcasecmp($key, $normalized) === 0) {
                return (float) $percentage;
            }
        }

        if (strcasecmp($normalized, 'Americas') === 0) {
            return 0.0;
        }

        return 0.0;
    }
}
