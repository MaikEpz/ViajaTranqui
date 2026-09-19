<?php

namespace Database\Seeders;

use App\Models\Quotation;
use Illuminate\Database\Seeder;

/**
 * Seeder para poblar la base de datos con cotizaciones y seguros de prueba.
 */
class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Generación de 14 cotizaciones aleatorias con fechas históricas para poblar la tabla
        Quotation::factory()->count(14)->create();

        // 2. Caso de prueba oficial especificado en el documento de requerimientos:
        // "Viaje de 10 días a España: Tarifa base $30 + Recargo Europa 20% ($6) = Total $36"
        Quotation::create([
            'first_name'               => 'Carlos',
            'last_name'                => 'Mendoza',
            'identification_number'    => '0928172635',
            'email'                    => 'carlos.mendoza@example.com',
            'birth_date'               => '1992-05-14',
            'destination_country'      => 'Spain',
            'destination_country_code' => 'ESP',
            'destination_region'       => 'Europe',
            'destination_flag_url'     => 'https://flagcdn.com/w320/es.png',
            'start_date'               => now()->addDays(5)->format('Y-m-d'),
            'end_date'                 => now()->addDays(14)->format('Y-m-d'), // 10 días inclusivos
            'days_count'               => 10,
            'base_rate_per_day'        => 3.00,
            'base_amount'              => 30.00,
            'surcharge_percentage'     => 20.00,
            'surcharge_amount'         => 6.00,
            'total_amount'             => 36.00,
            'status'                   => 'Cotizado',
            'created_at'               => now()->subDay(),
            'updated_at'               => now()->subDay(),
        ]);

        // 3. Cotización interactiva emitida por el usuario (Michael Peñaloza - España $36)
        // Al tener la fecha más reciente e ID superior, aparece en la primera posición de la lista
        Quotation::create([
            'first_name'               => 'Michael',
            'last_name'                => 'Peñaloza',
            'identification_number'    => '0302064944',
            'email'                    => 'michael.penaloza@ucuenca.edu.ec',
            'birth_date'               => '1998-03-04',
            'destination_country'      => 'Spain',
            'destination_country_code' => 'ESP',
            'destination_region'       => 'Europe',
            'destination_flag_url'     => 'https://flagcdn.com/w320/es.png',
            'start_date'               => '2026-09-26',
            'end_date'                 => '2026-10-05',
            'days_count'               => 10,
            'base_rate_per_day'        => 3.00,
            'base_amount'              => 30.00,
            'surcharge_percentage'     => 20.00,
            'surcharge_amount'         => 6.00,
            'total_amount'             => 36.00,
            'status'                   => 'Cotizado',
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);
    }
}
