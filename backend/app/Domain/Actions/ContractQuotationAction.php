<?php

namespace App\Domain\Actions;

use App\Domain\Exceptions\QuotationAlreadyContractedException;
use App\Models\Quotation;

class ContractQuotationAction
{
    /**
     * Execute contract quotation use case.
     *
     * @throws QuotationAlreadyContractedException
     */
    public function execute(Quotation $quotation): Quotation
    {
        if ($quotation->status === 'Contratado') {
            throw new QuotationAlreadyContractedException();
        }

        $quotation->status = 'Contratado';
        $quotation->contracted_at = now();
        $quotation->save();

        return $quotation;
    }
}
