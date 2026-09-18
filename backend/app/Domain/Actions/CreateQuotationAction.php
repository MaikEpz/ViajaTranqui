<?php

namespace App\Domain\Actions;

use App\Domain\DTOs\QuotationData;
use App\Models\Quotation;
use Illuminate\Support\Facades\Log;

/**
 * Caso de uso: Creación y registro de una nueva cotización.
 * Orquesta el cálculo mediante CalculateQuotationAction y persiste el registro en estado 'Cotizado'.
 */
class CreateQuotationAction
{
    public function __construct(
        protected CalculateQuotationAction $calculateAction
    ) {}

    /**
     * Ejecuta el registro de la cotización a partir de un DTO validado.
     *
     * @param QuotationData $data DTO con los datos del asegurado y del viaje
     * @return Quotation Modelo persistido con estado 'Cotizado'
     */
    public function execute(QuotationData $data): Quotation
    {
        // 1. Invoca el caso de uso de cálculo financiero
        $pricing = $this->calculateAction->execute(
            $data->destinationRegion,
            $data->startDate,
            $data->endDate
        );

        // 2. Persiste la cotización en la base de datos
        $quotation = Quotation::create([
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

        // 3. Registro de auditoría con logs estructurados (Bonus)
        Log::info('Cotización generada exitosamente', [
            'quotation_id'     => $quotation->id,
            'reference'        => '#VTQ-' . str_pad((string)$quotation->id, 5, '0', STR_PAD_LEFT),
            'client_dni'       => $quotation->identification_number,
            'client_name'      => $quotation->full_name,
            'destination'      => $quotation->destination_country,
            'region'           => $quotation->destination_region,
            'days_count'       => $quotation->days_count,
            'total_amount_usd' => (float)$quotation->total_amount,
            'status'           => $quotation->status,
            'timestamp'        => now()->toIso8601String(),
        ]);

        return $quotation;
    }
}
