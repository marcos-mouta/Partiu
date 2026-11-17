<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    public const MAX_PHOTOS = 8;

    protected $fillable = [
        'user_id', 'brand', 'model', 'year', 'plate', 'color', 'transmission',
        'seats', 'city', 'daily_price', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'daily_price' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /** Fotos ordenadas; a primeira (position 0) é a capa. */
    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->orderBy('position')->orderBy('id');
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->photos->first()?->url;
    }

    public function getTitleAttribute(): string
    {
        return "{$this->brand} {$this->model} {$this->year}";
    }

    /** Veículos sem reserva CONFIRMADA que conflite com o período. */
    public function scopeAvailableBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->whereDoesntHave('rentals', fn ($r) => $r->overlapping($start, $end)->where('status', 'confirmed'));
    }

    public function isAvailable(string $start, string $end, ?int $ignoreRentalId = null): bool
    {
        return ! $this->rentals()
            ->where('status', 'confirmed')
            ->when($ignoreRentalId, fn ($q) => $q->where('id', '!=', $ignoreRentalId))
            ->overlapping($start, $end)
            ->exists();
    }
}
