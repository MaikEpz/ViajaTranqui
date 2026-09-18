<?php

namespace App\Domain\Actions;

use App\Domain\DTOs\QuotationData;
use App\Models\Quotation;

class CreateQuotationAction
{
    public function __construct(
        protected CalculateQuotationAction $calculateAction
    ) {}

    /**
     * Execute quotation creation use case.
     */
    public function execute(QuotationData $data): Quotation
    {
        $pricing = $this->calculateAction->execute(
            $data->destinationRegion,
            $data->startDate,
            $data->endDate
        );

        return Quotation::create([
            'first_name'               => $data->firstName,
            'last_name'                => $data->lastName,
            'identification_number'    => $data->identificationNumber,
            'email'                    => $data->email,
            'birth_date'               => $data->birthDate,
            'destination_country'      => $data->destinationCountry,
            'destination_country_code' => $data->destinationCountryCode,
            'destination_region'       => $data->destinationRegion,
            'destination_flag_url'     => $data->destinationFlagUrl,
            'start_date'               => $data->startDate,
            'end_date'                 => $data->endDate,
            'days_count'               => $pricing->daysCount,
            'base_rate_per_day'        => $pricing->baseRatePerDay,
            'base_amount'              => $pricing->baseAmount,
            'surcharge_percentage'     => $pricing->surchargePercentage,
            'surcharge_amount'         => $pricing->surchargeAmount,
            'total_amount'             => $pricing->totalAmount,
            'status'                   => 'Cotizado',
        ]);
    }
}
