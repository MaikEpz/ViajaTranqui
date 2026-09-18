<?php

namespace App\Domain\DTOs;

/**
 * Objeto de Transferencia de Datos (DTO) inmutable con la información validada para crear una cotización.
 */
readonly class QuotationData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $identificationNumber,
        public string $email,
        public string $birthDate,
        public string $destinationCountry,
        public string $destinationCountryCode,
        public string $destinationRegion,
        public ?string $destinationFlagUrl,
        public string $startDate,
        public string $endDate,
    ) {}

    /**
     * Construye la instancia desde los datos validados del FormRequest.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            identificationNumber: $data['identification_number'],
            email: $data['email'],
            birthDate: $data['birth_date'],
            destinationCountry: $data['destination_country'],
            destinationCountryCode: $data['destination_country_code'],
            destinationRegion: $data['destination_region'],
            destinationFlagUrl: $data['destination_flag_url'] ?? null,
            startDate: $data['start_date'],
            endDate: $data['end_date'],
        );
    }

    /**
     * Retorna el arreglo plano con nomenclatura snake_case para Eloquent.
     */
    public function toArray(): array
    {
        return [
            'first_name'               => $this->firstName,
            'last_name'                => $this->lastName,
            'identification_number'    => $this->identificationNumber,
            'email'                    => $this->email,
            'birth_date'               => $this->birthDate,
            'destination_country'      => $this->destinationCountry,
            'destination_country_code' => $this->destinationCountryCode,
            'destination_region'       => $this->destinationRegion,
            'destination_flag_url'     => $this->destinationFlagUrl,
            'start_date'               => $this->startDate,
            'end_date'                 => $this->endDate,
        ];
    }
}
