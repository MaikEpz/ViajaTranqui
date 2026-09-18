<?php

use App\Services\QuotationCalculationService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = new QuotationCalculationService();
});

test('calcula correctamente la tarifa básica con USD 3 por día', function () {
    $start = Carbon::parse('2026-10-01');
    $end = Carbon::parse('2026-10-10'); // 10 días inclusivos

    $result = $this->service->calculate('South America', $start, $end);

    expect($result['days_count'])->toBe(10)
        ->and($result['base_rate_per_day'])->toBe(3.00)
        ->and($result['base_amount'])->toBe(30.00)
        ->and($result['surcharge_percentage'])->toBe(0.0)
        ->and($result['surcharge_amount'])->toBe(0.00)
        ->and($result['total_amount'])->toBe(30.00);
});

test('calcula la cotización para España con 20% de recargo Europa según especificación', function () {
    // Ejemplo de la prueba técnica: Viaje de 10 días a España = $30 base + $6 recargo = $36 total
    $start = Carbon::parse('2026-10-01');
    $end = Carbon::parse('2026-10-10');

    $result = $this->service->calculate('Europe', $start, $end);

    expect($result['days_count'])->toBe(10)
        ->and($result['base_amount'])->toBe(30.00)
        ->and($result['surcharge_percentage'])->toBe(20.0)
        ->and($result['surcharge_amount'])->toBe(6.00)
        ->and($result['total_amount'])->toBe(36.00);
});

test('aplica correctamente los recargos en todas las regiones geográficas definidas', function (string $region, float $expectedSurcharge) {
    $start = Carbon::parse('2026-10-01');
    $end = Carbon::parse('2026-10-10'); // 10 días = $30 base

    $result = $this->service->calculate($region, $start, $end);

    $expectedSurchargeAmount = round(30.00 * ($expectedSurcharge / 100), 2);
    $expectedTotal = round(30.00 + $expectedSurchargeAmount, 2);

    expect($result['surcharge_percentage'])->toBe($expectedSurcharge)
        ->and($result['surcharge_amount'])->toBe($expectedSurchargeAmount)
        ->and($result['total_amount'])->toBe($expectedTotal);
})->with([
    ['South America', 0.0],
    ['North America', 15.0],
    ['Europe',        20.0],
    ['Asia',          25.0],
    ['Africa',        20.0],
    ['Oceania',       25.0],
]);

test('lanza InvalidArgumentException cuando la fecha de retorno es anterior a la de salida', function () {
    $start = Carbon::parse('2026-10-10');
    $end = Carbon::parse('2026-10-05');

    $this->service->calculate('Europe', $start, $end);
})->throws(InvalidArgumentException::class, 'La fecha de regreso no puede ser anterior a la fecha de salida.');
