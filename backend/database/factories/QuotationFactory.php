<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Services\QuotationCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fábrica para generar registros de prueba de cotizaciones y seguros de viaje.
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        $countries = [
            ['name' => 'Spain', 'code' => 'ESP', 'region' => 'Europe', 'flag_url' => 'https://flagcdn.com/w320/es.png'],
            ['name' => 'France', 'code' => 'FRA', 'region' => 'Europe', 'flag_url' => 'https://flagcdn.com/w320/fr.png'],
            ['name' => 'Argentina', 'code' => 'ARG', 'region' => 'South America', 'flag_url' => 'https://flagcdn.com/w320/ar.png'],
            ['name' => 'United States', 'code' => 'USA', 'region' => 'North America', 'flag_url' => 'https://flagcdn.com/w320/us.png'],
            ['name' => 'Japan', 'code' => 'JPN', 'region' => 'Asia', 'flag_url' => 'https://flagcdn.com/w320/jp.png'],
            ['name' => 'Australia', 'code' => 'AUS', 'region' => 'Oceania', 'flag_url' => 'https://flagcdn.com/w320/au.png'],
            ['name' => 'Egypt', 'code' => 'EGY', 'region' => 'Africa', 'flag_url' => 'https://flagcdn.com/w320/eg.png'],
        ];

        $country = $countries[array_rand($countries)];

        $startDate = Carbon::today()->addDays($this->faker->numberBetween(1, 45));
        $duration = $this->faker->numberBetween(4, 25);
        $endDate = (clone $startDate)->addDays($duration - 1);

        $calcService = new QuotationCalculationService();
        $pricing = $calcService->calculate($country['region'], $startDate, $endDate);

        $status = $this->faker->randomElement(['Cotizado', 'Contratado']);
        $createdAt = $this->faker->dateTimeBetween('-20 days', '-2 days');
        $contractedAt = $status === 'Contratado' 
            ? Carbon::instance($createdAt)->addHours($this->faker->numberBetween(1, 24))
            : null;

        return [
            'first_name'               => $this->faker->firstName(),
            'last_name'                => $this->faker->lastName(),
            'identification_number'    => (string) $this->faker->numerify('##########'),
            'email'                    => $this->faker->unique()->safeEmail(),
            'birth_date'               => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'destination_country'      => $country['name'],
            'destination_country_code' => $country['code'],
            'destination_region'       => $country['region'],
            'destination_flag_url'     => $country['flag_url'],
            'start_date'               => $startDate->format('Y-m-d'),
            'end_date'                 => $endDate->format('Y-m-d'),
            'days_count'               => $pricing['days_count'],
            'base_rate_per_day'        => $pricing['base_rate_per_day'],
            'base_amount'              => $pricing['base_amount'],
            'surcharge_percentage'     => $pricing['surcharge_percentage'],
            'surcharge_amount'         => $pricing['surcharge_amount'],
            'total_amount'             => $pricing['total_amount'],
            'status'                   => $status,
            'contracted_at'            => $contractedAt,
            'created_at'               => $createdAt,
            'updated_at'               => $createdAt,
        ];
    }

    /**
     * Estado para generar la cotización ya confirmada como contratada.
     */
    public function contracted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => 'Contratado',
            'contracted_at' => now(),
        ]);
    }
}
