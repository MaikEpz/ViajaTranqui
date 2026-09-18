<?php

use App\Domain\Actions\ContractQuotationAction;
use App\Domain\Exceptions\QuotationAlreadyContractedException;
use App\Models\Quotation;

test('it transitions a quotation from Cotizado to Contratado with timestamp', function () {
    $quotation = Quotation::factory()->create([
        'status'        => 'Cotizado',
        'contracted_at' => null,
    ]);

    $action = new ContractQuotationAction();
    $updated = $action->execute($quotation);

    expect($updated->status)->toBe('Contratado')
        ->and($updated->contracted_at)->not->toBeNull();
});

test('it throws QuotationAlreadyContractedException when quotation is already contracted', function () {
    $quotation = Quotation::factory()->contracted()->create();

    $action = new ContractQuotationAction();
    $action->execute($quotation);
})->throws(QuotationAlreadyContractedException::class, 'Este seguro ya ha sido contratado previamente.');
