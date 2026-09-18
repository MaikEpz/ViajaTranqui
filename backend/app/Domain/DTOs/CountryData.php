<?php

namespace App\Domain\DTOs;

readonly class CountryData
{
    public function __construct(
        public string $name,
        public string $code,
        public string $cca2,
        public string $region,
        public ?string $flagUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            code: $data['code'],
            cca2: $data['cca2'] ?? '',
            region: $data['region'],
            flagUrl: $data['flag_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'code'     => $this->code,
            'cca2'     => $this->cca2,
            'region'   => $this->region,
            'flag_url' => $this->flagUrl,
        ];
    }
}
