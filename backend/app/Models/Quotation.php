<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
        'birth_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'integer',
        'base_rate_per_day' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'surcharge_percentage' => 'decimal:2',
        'surcharge_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'contracted_at' => 'datetime',
    ];

    protected $appends = [
        'full_name',
    ];

    /**
     * Get the full name of the insured client.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope for searching quotations by client name, email, or identification number.
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
     * Scope for filtering by quotation status.
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (!empty($status) && in_array($status, ['Cotizado', 'Contratado'])) {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Mark quotation as contracted.
     */
    public function markAsContracted(): bool
    {
        $this->status = 'Contratado';
        $this->contracted_at = now();

        return $this->save();
    }
}
