<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo Eloquent para la entidad Quotation (Cotizaciones y Seguros de Viaje).
 */
class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'identification_number',
        'email',
        'birth_date',
        'destination_country',
        'destination_country_code',
        'destination_region',
        'destination_flag_url',
        'start_date',
        'end_date',
        'days_count',
        'base_rate_per_day',
        'base_amount',
        'surcharge_percentage',
        'surcharge_amount',
        'total_amount',
        'status',
        'contracted_at',
    ];

    protected $casts = [
        'birth_date'           => 'date',
        'start_date'           => 'date',
        'end_date'             => 'date',
        'days_count'           => 'integer',
        'base_rate_per_day'    => 'decimal:2',
        'base_amount'          => 'decimal:2',
        'surcharge_percentage' => 'decimal:2',
        'surcharge_amount'     => 'decimal:2',
        'total_amount'         => 'decimal:2',
        'contracted_at'        => 'datetime',
    ];

    protected $appends = [
        'full_name',
    ];

    /**
     * Accesor para obtener el nombre completo del asegurado.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope para filtrar por término de búsqueda (nombre, apellido, cédula, correo o país).
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $term = trim($search);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('identification_number', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('destination_country', 'like', "%{$term}%");
        });
    }

    /**
     * Scope para filtrar por estado ('Cotizado' o 'Contratado').
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (!empty($status) && in_array($status, ['Cotizado', 'Contratado'])) {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Método auxiliar para marcar el registro como contratado.
     */
    public function markAsContracted(): bool
    {
        $this->status = 'Contratado';
        $this->contracted_at = now();

        return $this->save();
    }
}
