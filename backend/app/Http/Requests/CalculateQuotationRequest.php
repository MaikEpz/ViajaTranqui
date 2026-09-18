<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_region' => ['required', 'string', 'max:50'],
            'start_date'         => ['required', 'date', 'after_or_equal:today'],
            'end_date'           => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_region.required' => 'La región de destino es obligatoria.',
            'start_date.required'         => 'La fecha de salida es obligatoria.',
            'start_date.date'             => 'La fecha de salida debe ser una fecha válida.',
            'start_date.after_or_equal'   => 'La fecha de salida no puede ser anterior a la fecha actual.',
            'end_date.required'           => 'La fecha de regreso es obligatoria.',
            'end_date.date'               => 'La fecha de regreso debe ser una fecha válida.',
            'end_date.after_or_equal'     => 'La fecha de regreso debe ser igual o posterior a la fecha de salida.',
        ];
    }
}
