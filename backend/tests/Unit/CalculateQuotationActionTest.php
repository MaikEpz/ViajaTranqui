<?php

use App\Domain\Actions\CalculateQuotationAction;
use App\Domain\Exceptions\InvalidTravelDatesException;
use Carbon\Carbon;

beforeEach(function () {
    $this->action = new CalculateQuotationAction();
});

test('calcula correctamente la tarifa base de USD 3 por día retornando el DTO PricingBreakdownData', function () {
    $start = Carbon::parse('2026-10-01');
    $end = Carbon::parse('2026-10-10'); // 10 días inclusivos

    $pricing = $this->action->execute('South America', $start, $end);

    expect($pricing->daysCount)->toBe(10)
        ->and($pricing->baseRatePerDay)->toBe(3.00)
        ->and($pricing->baseAmount)->toBe(30.00)
        ->and($pricing->surchargePercentage)->toBe(0.0)
        ->and($pricing->surchargeAmount)->toBe(0.00)
        ->and($pricing->totalAmount)->toBe(30.00);
});

test('calcula la cotización para España con 20% de recargo de Europa según el enunciado de la prueba', function () {
    // Ejemplo del enunciado: Viaje de 10 días a España: Tarifa base $30 + Recargo Europa 20% ($6) = Total $36
    $start = Carbon::parse('2026-10-01');
    $end = Carbon::parse('2026-10-10');

    $pricing = $this->action->execute('Europe', $start, $end);

    expect($pricing->daysCount)->toBe(10)
        ->and($pricing->baseAmount)->toBe(30.00)
        ->and($pricing->surchargePercentage)->toBe(20.0)
        ->and($pricing->surchargeAmount)->toBe(6.00)
        ->and($pricing->totalAmount)->toBe(36.00);
});

test('aplica correctamente los recargos porcentuales en todas las regiones geográficas definidas', function (string $region, float $expectedSurcharge) {
    $start = Carbon::parse('2026-10-01');
    $end = Carbon::parse('2026-10-10');

    $pricing = $this->action->execute($region, $start, $end);

    $expectedSurchargeAmount = round(30.00 * ($expectedSurcharge / 100), 2);
    $expectedTotal = round(30.00 + $expectedSurchargeAmount, 2);

    expect($pricing->surchargePercentage)->toBe($expectedSurcharge)
        ->and($pricing->surchargeAmount)->toBe($expectedSurchargeAmount)
        ->and($pricing->totalAmount)->toBe($expectedTotal);
})->with([
    ['South America', 0.0],
    ['North America', 15.0],
    ['Europe',        20.0],
    ['Asia',          25.0],
    ['Africa',        20.0],
    ['Oceania',       25.0],
]);

test('lanza la excepción de dominio InvalidTravelDatesException cuando la fecha de regreso es anterior a la salida', function () {
    $start = Carbon::parse('2026-10-10');
    $end = Carbon::parse('2026-10-05');

    $this->action->execute('Europe', $start, $end);
})->throws(InvalidTravelDatesException::class, 'La fecha de regreso no puede ser anterior a la fecha de salida.');
