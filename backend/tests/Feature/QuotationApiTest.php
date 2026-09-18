<?php

use App\Models\Quotation;

test('puede previsualizar el cálculo de la tarifa de una cotización vía API', function () {
    $response = $this->postJson('/api/quotes/calculate', [
        'destination_region' => 'Europe',
        'start_date'         => now()->addDays(2)->format('Y-m-d'),
        'end_date'           => now()->addDays(11)->format('Y-m-d'), // 10 días
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data'    => [
                'days_count'           => 10,
                'base_rate_per_day'    => 3.00,
                'base_amount'          => 30.00,
                'surcharge_percentage' => 20.00,
                'surcharge_amount'     => 6.00,
                'total_amount'         => 36.00,
            ],
        ]);
});

test('valida los campos obligatorios al registrar una cotización', function () {
    $response = $this->postJson('/api/quotes', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'identification_number',
            'email',
            'birth_date',
            'destination_country',
            'destination_region',
            'start_date',
            'end_date',
        ]);
});

test('crea y almacena una cotización con estado inicial Cotizado', function () {
    $payload = [
        'first_name'               => 'Laura',
        'last_name'                => 'Gomez',
        'identification_number'    => '1720394851',
        'email'                    => 'laura.gomez@test.com',
        'birth_date'               => '1996-03-12',
        'destination_country'      => 'France',
        'destination_country_code' => 'FRA',
        'destination_region'       => 'Europe',
        'destination_flag_url'     => 'https://flagcdn.com/w320/fr.png',
        'start_date'               => now()->addDays(5)->format('Y-m-d'),
        'end_date'                 => now()->addDays(9)->format('Y-m-d'), // 5 días: $15 base + 20% ($3) = $18
    ];

    $response = $this->postJson('/api/quotes', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data'    => [
                'first_name'   => 'Laura',
                'last_name'    => 'Gomez',
                'status'       => 'Cotizado',
                'days_count'   => 5,
                'total_amount' => '18.00',
            ],
        ]);

    $this->assertDatabaseHas('quotations', [
        'identification_number' => '1720394851',
        'status'                => 'Cotizado',
    ]);
});

test('puede confirmar y transicionar una cotización al estado Contratado', function () {
    $quotation = Quotation::factory()->create([
        'status'        => 'Cotizado',
        'contracted_at' => null,
    ]);

    $response = $this->patchJson("/api/quotes/{$quotation->id}/contract");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data'    => [
                'id'     => $quotation->id,
                'status' => 'Contratado',
            ],
        ]);

    $quotation->refresh();
    expect($quotation->status)->toBe('Contratado')
        ->and($quotation->contracted_at)->not->toBeNull();
});

test('permite descargar el comprobante de cotización en formato PDF', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->get("/api/quotes/{$quotation->id}/pdf");

    $response->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');
});

test('retorna el catálogo de países desde el endpoint de la API', function () {
    $response = $this->getJson('/api/countries');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'count',
            'data' => [
                '*' => ['name', 'code', 'region', 'flag_url'],
            ],
        ]);
});
