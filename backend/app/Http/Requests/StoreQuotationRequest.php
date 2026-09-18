<?php

namespace App\Http\Requests;

use App\Domain\DTOs\QuotationData;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Insured customer
            'first_name'               => ['required', 'string', 'min:2', 'max:100'],
            'last_name'                => ['required', 'string', 'min:2', 'max:100'],
            'identification_number'    => ['required', 'string', 'min:4', 'max:30'],
            'email'                    => ['required', 'email:rfc', 'max:150'],
            'birth_date'               => ['required', 'date', 'before:today'],

            // Trip details
            'destination_country'      => ['required', 'string', 'max:100'],
            'destination_country_code' => ['required', 'string', 'max:10'],
            'destination_region'       => ['required', 'string', 'max:50'],
            'destination_flag_url'     => ['nullable', 'url', 'max:500'],
            'start_date'               => ['required', 'date', 'after_or_equal:today'],
            'end_date'                 => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'            => 'Los nombres del asegurado son obligatorios.',
            'last_name.required'             => 'Los apellidos del asegurado son obligatorios.',
            'identification_number.required' => 'El número de identificación es obligatorio.',
            'email.required'                 => 'El correo electrónico es obligatorio.',
            'email.email'                    => 'Debe proporcionar una dirección de correo electrónico válida.',
            'birth_date.required'            => 'La fecha de nacimiento es obligatoria.',
            'birth_date.before'              => 'La fecha de nacimiento debe ser anterior al día de hoy.',
            'destination_country.required'   => 'Debe seleccionar un país de destino.',
            'destination_region.required'    => 'La región de destino es obligatoria.',
            'start_date.required'            => 'La fecha de salida es obligatoria.',
            'start_date.after_or_equal'      => 'La fecha de salida no puede ser anterior a la fecha actual.',
            'end_date.required'              => 'La fecha de regreso es obligatoria.',
            'end_date.after_or_equal'        => 'La fecha de regreso debe ser igual o posterior a la fecha de salida.',
        ];
    }

    /**
     * Transform validated HTTP payload into Domain DTO.
     */
    public function toDTO(): QuotationData
    {
        return QuotationData::fromArray($this->validated());
    }
}
