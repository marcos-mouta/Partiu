<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';

    protected $fillable = ['vehicle_id', 'user_id', 'start_date', 'end_date', 'total_price', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Períodos (inclusivos) que se sobrepõem a [$start, $end]. */
    public function scopeOverlapping(Builder $query, string $start, string $end): Builder
    {
        return $query->where('start_date', '<=', $end)->where('end_date', '>=', $start);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::PENDING => 'Aguardando aprovação',
            self::CONFIRMED => 'Confirmada',
            self::REJECTED => 'Recusada',
            self::CANCELLED => 'Cancelada',
        ][$this->status] ?? $this->status;
    }

    public function getDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
