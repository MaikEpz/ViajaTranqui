<?php

namespace App\Domain\Actions;

use App\Domain\Exceptions\QuotationAlreadyContractedException;
use App\Models\Quotation;
use Illuminate\Support\Facades\Log;

/**
 * Caso de uso: Confirmación y contratación definitiva del seguro.
 * Valida que la cotización no haya sido contratada previamente y actualiza su estado.
 */
class ContractQuotationAction
{
    /**
     * Ejecuta la contratación de la póliza de seguro.
     *
     * @param Quotation $quotation Registro de la cotización a contratar
     * @return Quotation Registro actualizado en estado 'Contratado'
     * @throws QuotationAlreadyContractedException Si el seguro ya fue contratado
     */
    public function execute(Quotation $quotation): Quotation
    {
        // Validación de invariante de dominio: no se permite doble contratación
        if ($quotation->status === 'Contratado') {
            Log::warning('Intento de doble contratación detectado', [
                'quotation_id'   => $quotation->id,
                'client_dni'     => $quotation->identification_number,
                'previous_state' => $quotation->status,
                'contracted_at'  => $quotation->contracted_at?->toIso8601String(),
            ]);

            throw new QuotationAlreadyContractedException();
        }

        // Transición de estado a 'Contratado' con timestamp oficial
        $quotation->status = 'Contratado';
        $quotation->contracted_at = now();
        $quotation->save();

        // Registro estructurado del evento de contratación (Bonus)
        Log::info('Póliza de seguro contratada exitosamente', [
            'quotation_id'     => $quotation->id,
            'reference'        => '#VTQ-' . str_pad((string)$quotation->id, 5, '0', STR_PAD_LEFT),
            'client_name'      => $quotation->full_name,
            'destination'      => $quotation->destination_country,
            'total_amount_usd' => (float)$quotation->total_amount,
            'status'           => $quotation->status,
            'contracted_at'    => $quotation->contracted_at->toIso8601String(),
        ]);

        return $quotation;
    }
}
