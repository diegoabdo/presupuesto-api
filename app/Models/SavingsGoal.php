<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'target_date',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'target_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SavingsContribution::class);
    }

    /**
     * No se guarda un total aparte: siempre se calcula sumando los aportes.
     * Así el saldo nunca puede desincronizarse del historial.
     */
    protected function currentAmount(): Attribute
    {
        return Attribute::get(fn () => $this->contributions->sum('amount'));
    }

    protected function progress(): Attribute
    {
        return Attribute::get(function () {
            if ((float) $this->target_amount <= 0) {
                return 0;
            }

            return min(1, $this->currentAmount / $this->target_amount);
        });
    }
}
