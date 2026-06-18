<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'client_id', 'vin', 'registration', 'brand', 'model', 'year',
    'mileage', 'energy', 'condition', 'status', 'price', 'description',
])]
class Vehicle extends Model
{
    use BelongsToCompany;

    public const ENERGIES = [
        'essence' => 'Essence', 'diesel' => 'Diesel',
        'electrique' => 'Électrique', 'hybride' => 'Hybride', 'gpl' => 'GPL',
    ];

    public const STATUSES = [
        'disponible' => 'Disponible', 'reserve' => 'Réservé', 'vendu' => 'Vendu',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'year' => 'integer', 'mileage' => 'integer'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function name(): string
    {
        return trim("{$this->brand} {$this->model}");
    }

    public function energyLabel(): string
    {
        return self::ENERGIES[$this->energy] ?? $this->energy;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'vendu'   => 'secondary',
            'reserve' => 'warning',
            default   => 'success',
        };
    }
}
