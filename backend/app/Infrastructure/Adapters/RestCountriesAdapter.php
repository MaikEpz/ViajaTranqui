<?php

namespace App\Infrastructure\Adapters;

use App\Domain\Contracts\CountryProviderInterface;
use App\Domain\DTOs\CountryData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Adaptador de infraestructura para la obtención del catálogo de países.
 * Implementa CountryProviderInterface y gestiona tolerancia a fallos, caché y fallbacks.
 */
class RestCountriesAdapter implements CountryProviderInterface
{
    // Endpoint primario oficial de REST Countries
    protected const PRIMARY_API_URL = 'https://restcountries.com/v3.1/all?fields=name,cca2,cca3,region,subregion,flags';

    // Espejo público abierto de alta disponibilidad (dataset oficial mledoze/countries)
    protected const MIRROR_API_URL = 'https://raw.githubusercontent.com/mledoze/countries/master/countries.json';

    protected const CACHE_KEY = 'rest_countries_dataset_clean_arch';
    protected const CACHE_TTL_SECONDS = 86400; // Caché de 24 horas

    /**
     * Retorna la lista de países normalizados, utilizando caché y degradación elegante.
     *
     * @return array<CountryData>
     */
    public function getCountries(): array
    {
        $raw = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->fetchCountries();
        });

        return array_map(fn (array $item) => CountryData::fromArray($item), $raw);
    }

    /**
     * Busca un país por su código ISO alpha-2 o alpha-3.
     */
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

    /**
     * Mecanismo de degradación elegante (Graceful Degradation):
     * 1. Intenta la API primaria de REST Countries.
     * 2. Si falla por timeout o formato inesperado, conmuta al espejo público.
     * 3. Como última línea de defensa, utiliza el catálogo empaquetado offline.
     */
    protected function fetchCountries(): array
    {
        // Nivel 1: Intento con la API primaria
        try {
            $response = Http::timeout(5)->get(self::PRIMARY_API_URL);

            if ($response->successful()) {
                $data = $response->json();
                if ($this->isValidCountriesPayload($data)) {
                    Log::info('RestCountriesAdapter: catálogo cargado exitosamente desde API primaria');
                    return $this->normalizeCountries($data);
                }

                Log::warning('RestCountriesAdapter: respuesta inesperada en API primaria, activando contingencia', [
                    'status' => $response->status(),
                    'snippet' => is_array($data) ? array_slice($data, 0, 2) : $data,
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('RestCountriesAdapter: timeout o error de conexión con API primaria: ' . $e->getMessage());
        }

        // Nivel 2: Intento con el espejo público abierto
        try {
            $mirrorResponse = Http::timeout(5)->get(self::MIRROR_API_URL);

            if ($mirrorResponse->successful()) {
                $mirrorData = $mirrorResponse->json();
                if ($this->isValidCountriesPayload($mirrorData)) {
                    Log::info('RestCountriesAdapter: contingencia exitosa con espejo público abierto');
                    return $this->normalizeCountries($mirrorData);
                }
            }
        } catch (Throwable $e) {
            Log::warning('RestCountriesAdapter: fallo de conexión con espejo público: ' . $e->getMessage());
        }

        // Nivel 3: Catálogo empaquetado offline para garantizar 100% de disponibilidad
        Log::warning('RestCountriesAdapter: utilizando catálogo de contingencia empaquetado offline');
        return $this->getFallbackCountries();
    }

    /**
     * Valida que la respuesta sea un arreglo con registros válidos de países.
     */
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

    /**
     * Normaliza la estructura heterogénea de la API externa en campos limpios.
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

        // Ordenar alfabéticamente por nombre común
        usort($countries, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $countries;
    }

    /**
     * Homologa las regiones y subregiones de la API a las categorías tarifarias del negocio.
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
     * Catálogo empaquetado offline de respaldo inmediato.
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
