<?php

namespace App\Domain\DTOs;

readonly class PricingBreakdownData
{
    public function __construct(
        public int $daysCount,
        public float $baseRatePerDay,
        public float $baseAmount,
        public float $surchargePercentage,
        public float $surchargeAmount,
        public float $totalAmount,
    ) {}

    public function toArray(): array
    {
        return [
            'days_count'           => $this->daysCount,
            'base_rate_per_day'    => $this->baseRatePerDay,
            'base_amount'          => $this->baseAmount,
            'surcharge_percentage' => $this->surchargePercentage,
            'surcharge_amount'     => $this->surchargeAmount,
            'total_amount'         => $this->totalAmount,
            'formatted'            => [
                'base_rate'        => '$' . number_format($this->baseRatePerDay, 2),
                'base_amount'      => '$' . number_format($this->baseAmount, 2),
                'surcharge_amount' => '$' . number_format($this->surchargeAmount, 2),
                'total_amount'     => '$' . number_format($this->totalAmount, 2),
            ],
        ];
    }
}
