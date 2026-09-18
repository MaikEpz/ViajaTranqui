<?php

namespace App\Domain\DTOs;

/**
 * Objeto de Transferencia de Datos (DTO) inmutable para el desglose financiero de una cotización.
 */
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

    /**
     * Retorna los datos estructurados y formateados para respuesta API.
     */
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
