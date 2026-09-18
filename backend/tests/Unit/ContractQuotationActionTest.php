<?php

use App\Domain\Actions\ContractQuotationAction;
use App\Domain\Exceptions\QuotationAlreadyContractedException;
use App\Models\Quotation;

test('transiciona una cotización de Cotizado a Contratado registrando la fecha de contratación', function () {
    $quotation = Quotation::factory()->create([
        'status'        => 'Cotizado',
        'contracted_at' => null,
    ]);

    $action = new ContractQuotationAction();
    $updated = $action->execute($quotation);

    expect($updated->status)->toBe('Contratado')
        ->and($updated->contracted_at)->not->toBeNull();
});

test('lanza la excepción QuotationAlreadyContractedException cuando se intenta re-contratar un seguro', function () {
    $quotation = Quotation::factory()->contracted()->create();

    $action = new ContractQuotationAction();
    $action->execute($quotation);
})->throws(QuotationAlreadyContractedException::class, 'Este seguro ya ha sido contratado previamente.');
