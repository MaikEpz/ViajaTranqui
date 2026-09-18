<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestCountriesService
{
    /**
     * Primary REST Countries endpoint (as requested in technical test).
     */
    protected const PRIMARY_API_URL = 'https://restcountries.com/v3.1/all?fields=name,cca2,cca3,region,subregion,flags';

    /**
     * High-reliability public mirror (standard mledoze/countries open dataset).
     */
    protected const MIRROR_API_URL = 'https://raw.githubusercontent.com/mledoze/countries/master/countries.json';

    protected const CACHE_KEY = 'rest_countries_dataset';
    protected const CACHE_TTL_SECONDS = 86400; // 24 hours

    /**
     * Get list of countries, cached and resilient to API outages.
     */
    public function getCountries(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->fetchCountriesWithGracefulDegradation();
        });
    }

    /**
     * Fetch countries with graceful degradation:
     * 1. Attempt Primary REST Countries API
     * 2. If timed out or unexpected response (e.g. deprecation message), attempt Open Public Mirror
     * 3. If still failing, return bundled offline fallback countries
     */
    protected function fetchCountriesWithGracefulDegradation(): array
    {
        // 1. Try Primary REST Countries API
        try {
            $response = Http::timeout(5)->get(self::PRIMARY_API_URL);

            if ($response->successful()) {
                $data = $response->json();
                if ($this->isValidCountriesPayload($data)) {
                    Log::info('RestCountries: loaded from primary API');
                    return $this->normalizeCountries($data);
                }

                Log::warning('RestCountries primary API returned unexpected non-country payload (possible API deprecation or rate limit)', [
                    'payload_snippet' => is_array($data) ? array_slice($data, 0, 3) : $data,
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('RestCountries primary API connection issue: ' . $e->getMessage());
        }

        // 2. Fallback to Open Public Mirror
        try {
            $mirrorResponse = Http::timeout(5)->get(self::MIRROR_API_URL);

            if ($mirrorResponse->successful()) {
                $mirrorData = $mirrorResponse->json();
                if ($this->isValidCountriesPayload($mirrorData)) {
                    Log::info('RestCountries: fallback to public mirror succeeded');
                    return $this->normalizeCountries($mirrorData);
                }
            }
        } catch (Throwable $e) {
            Log::warning('RestCountries public mirror connection issue: ' . $e->getMessage());
        }

        // 3. Final safety net: bundled offline dataset
        Log::warning('RestCountries: using offline fallback dataset');
        return $this->getFallbackCountries();
    }

    /**
     * Validate that the payload is actually an array of country records.
     */
    protected function isValidCountriesPayload(mixed $data): bool
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }

        // Check if associative error response like ['success' => false, 'errors' => ...]
        if (isset($data['success']) && $data['success'] === false) {
            return false;
        }

        // First item must be an array with 'name' property
        $first = reset($data);
        return is_array($first) && isset($first['name']);
    }

    /**
     * Normalize API raw response into a clean, unified structure.
     */
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

            // Flag image URL (SVG/PNG or fallback flagcdn)
            $flagUrl = $item['flags']['png'] ?? ($item['flags']['svg'] ?? null);
            if (!$flagUrl && !empty($cca2)) {
                $flagUrl = "https://flagcdn.com/w320/{$cca2}.png";
            }

            $countries[] = [
                'name'      => $commonName,
                'code'      => $item['cca3'] ?? ($item['cca2'] ?? strtoupper(substr($commonName, 0, 3))),
                'cca2'      => strtoupper($cca2),
                'region'    => $effectiveRegion,
                'flag_url'  => $flagUrl,
            ];
        }

        // Sort alphabetically by country name
        usort($countries, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $countries;
    }

    /**
     * Maps regions and subregions into quotation surcharge categories.
     */
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

    /**
     * Find country by ISO code.
     */
    public function findByCode(string $code): ?array
    {
        $countries = $this->getCountries();
        $target = strtoupper(trim($code));

        foreach ($countries as $country) {
            if ($country['code'] === $target || ($country['cca2'] ?? '') === $target) {
                return $country;
            }
        }

        return null;
    }

    /**
     * High-reliability fallback countries dataset.
     */
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
