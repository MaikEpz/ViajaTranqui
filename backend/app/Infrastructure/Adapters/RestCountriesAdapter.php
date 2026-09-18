<?php

namespace App\Infrastructure\Adapters;

use App\Domain\Contracts\CountryProviderInterface;
use App\Domain\DTOs\CountryData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestCountriesAdapter implements CountryProviderInterface
{
    protected const PRIMARY_API_URL = 'https://restcountries.com/v3.1/all?fields=name,cca2,cca3,region,subregion,flags';
    protected const MIRROR_API_URL = 'https://raw.githubusercontent.com/mledoze/countries/master/countries.json';
    protected const CACHE_KEY = 'rest_countries_dataset_clean_arch';
    protected const CACHE_TTL_SECONDS = 86400; // 24 hours

    /**
     * @return array<CountryData>
     */
    public function getCountries(): array
    {
        $raw = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->fetchCountries();
        });

        return array_map(fn (array $item) => CountryData::fromArray($item), $raw);
    }

    public function findByCode(string $code): ?CountryData
    {
        $countries = $this->getCountries();
        $target = strtoupper(trim($code));

        foreach ($countries as $country) {
            if ($country->code === $target || $country->cca2 === $target) {
                return $country;
            }
        }

        return null;
    }

    protected function fetchCountries(): array
    {
        // 1. Try Primary REST Countries API
        try {
            $response = Http::timeout(5)->get(self::PRIMARY_API_URL);

            if ($response->successful()) {
                $data = $response->json();
                if ($this->isValidCountriesPayload($data)) {
                    return $this->normalizeCountries($data);
                }
            }
        } catch (Throwable $e) {
            Log::warning('RestCountriesAdapter primary API unreachable: ' . $e->getMessage());
        }

        // 2. Try Public Open Mirror
        try {
            $mirrorResponse = Http::timeout(5)->get(self::MIRROR_API_URL);

            if ($mirrorResponse->successful()) {
                $mirrorData = $mirrorResponse->json();
                if ($this->isValidCountriesPayload($mirrorData)) {
                    return $this->normalizeCountries($mirrorData);
                }
            }
        } catch (Throwable $e) {
            Log::warning('RestCountriesAdapter mirror API unreachable: ' . $e->getMessage());
        }

        // 3. Bundled Fallback Dataset
        return $this->getFallbackCountries();
    }

    protected function isValidCountriesPayload(mixed $data): bool
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }

        if (isset($data['success']) && $data['success'] === false) {
            return false;
        }

        $first = reset($data);
        return is_array($first) && isset($first['name']);
    }

    protected function normalizeCountries(array $rawCountries): array
    {
        $countries = [];

        foreach ($rawCountries as $item) {
            $commonName = $item['name']['common'] ?? null;
            if (!$commonName) {
                continue;
            }

            $subregion = $item['subregion'] ?? null;
            $region = $item['region'] ?? 'Other';
            $cca2 = strtolower($item['cca2'] ?? '');

            $effectiveRegion = $this->resolveEffectiveRegion($region, $subregion);

            $flagUrl = $item['flags']['png'] ?? ($item['flags']['svg'] ?? null);
            if (!$flagUrl && !empty($cca2)) {
                $flagUrl = "https://flagcdn.com/w320/{$cca2}.png";
            }

            $countries[] = [
                'name'     => $commonName,
                'code'     => $item['cca3'] ?? ($item['cca2'] ?? strtoupper(substr($commonName, 0, 3))),
                'cca2'     => strtoupper($cca2),
                'region'   => $effectiveRegion,
                'flag_url' => $flagUrl,
            ];
        }

        usort($countries, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $countries;
    }

    protected function resolveEffectiveRegion(string $region, ?string $subregion): string
    {
        if ($subregion === 'South America') {
            return 'South America';
        }
        if ($subregion === 'North America' || $subregion === 'Central America' || $subregion === 'Caribbean') {
            return 'North America';
        }

        if (in_array($region, ['Europe', 'Asia', 'Africa', 'Oceania'])) {
            return $region;
        }

        if ($region === 'Americas') {
            return 'South America';
        }

        return $region ?: 'Other';
    }

    protected function getFallbackCountries(): array
    {
        return [
            ['name' => 'Argentina', 'code' => 'ARG', 'cca2' => 'AR', 'region' => 'South America', 'flag_url' => 'https://flagcdn.com/w320/ar.png'],
            ['name' => 'Australia', 'code' => 'AUS', 'cca2' => 'AU', 'region' => 'Oceania', 'flag_url' => 'https://flagcdn.com/w320/au.png'],
            ['name' => 'Brazil', 'code' => 'BRA', 'cca2' => 'BR', 'region' => 'South America', 'flag_url' => 'https://flagcdn.com/w320/br.png'],
            ['name' => 'Canada', 'code' => 'CAN', 'cca2' => 'CA', 'region' => 'North America', 'flag_url' => 'https://flagcdn.com/w320/ca.png'],
            ['name' => 'Chile', 'code' => 'CHL', 'cca2' => 'CL', 'region' => 'South America', 'flag_url' => 'https://flagcdn.com/w320/cl.png'],
            ['name' => 'China', 'code' => 'CHN', 'cca2' => 'CN', 'region' => 'Asia', 'flag_url' => 'https://flagcdn.com/w320/cn.png'],
            ['name' => 'Colombia', 'code' => 'COL', 'cca2' => 'CO', 'region' => 'South America', 'flag_url' => 'https://flagcdn.com/w320/co.png'],
            ['name' => 'Ecuador', 'code' => 'ECU', 'cca2' => 'EC', 'region' => 'South America', 'flag_url' => 'https://flagcdn.com/w320/ec.png'],
            ['name' => 'Egypt', 'code' => 'EGY', 'cca2' => 'EG', 'region' => 'Africa', 'flag_url' => 'https://flagcdn.com/w320/eg.png'],
            ['name' => 'France', 'code' => 'FRA', 'cca2' => 'FR', 'region' => 'Europe', 'flag_url' => 'https://flagcdn.com/w320/fr.png'],
            ['name' => 'Germany', 'code' => 'DEU', 'cca2' => 'DE', 'region' => 'Europe', 'flag_url' => 'https://flagcdn.com/w320/de.png'],
            ['name' => 'Italy', 'code' => 'ITA', 'cca2' => 'IT', 'region' => 'Europe', 'flag_url' => 'https://flagcdn.com/w320/it.png'],
            ['name' => 'Japan', 'code' => 'JPN', 'cca2' => 'JP', 'region' => 'Asia', 'flag_url' => 'https://flagcdn.com/w320/jp.png'],
            ['name' => 'Mexico', 'code' => 'MEX', 'cca2' => 'MX', 'region' => 'North America', 'flag_url' => 'https://flagcdn.com/w320/mx.png'],
            ['name' => 'New Zealand', 'code' => 'NZL', 'cca2' => 'NZ', 'region' => 'Oceania', 'flag_url' => 'https://flagcdn.com/w320/nz.png'],
            ['name' => 'Peru', 'code' => 'PER', 'cca2' => 'PE', 'region' => 'South America', 'flag_url' => 'https://flagcdn.com/w320/pe.png'],
            ['name' => 'South Africa', 'code' => 'ZAF', 'cca2' => 'ZA', 'region' => 'Africa', 'flag_url' => 'https://flagcdn.com/w320/za.png'],
            ['name' => 'Spain', 'code' => 'ESP', 'cca2' => 'ES', 'region' => 'Europe', 'flag_url' => 'https://flagcdn.com/w320/es.png'],
            ['name' => 'United Kingdom', 'code' => 'GBR', 'cca2' => 'GB', 'region' => 'Europe', 'flag_url' => 'https://flagcdn.com/w320/gb.png'],
            ['name' => 'United States', 'code' => 'USA', 'cca2' => 'US', 'region' => 'North America', 'flag_url' => 'https://flagcdn.com/w320/us.png'],
        ];
    }
}
