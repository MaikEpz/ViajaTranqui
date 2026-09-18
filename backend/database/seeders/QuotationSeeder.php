<?php

namespace Database\Seeders;

use App\Models\Quotation;
use Illuminate\Database\Seeder;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        // Example test record matching the exact business test case:
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
            'end_date'                 => now()->addDays(14)->format('Y-m-d'), // 10 days inclusive
            'days_count'               => 10,
            'base_rate_per_day'        => 3.00,
            'base_amount'              => 30.00,
            'surcharge_percentage'     => 20.00,
            'surcharge_amount'         => 6.00,
            'total_amount'             => 36.00,
            'status'                   => 'Cotizado',
        ]);

        // Generate 15 diverse sample quotations for the listing screen
        Quotation::factory()->count(15)->create();
    }
}
