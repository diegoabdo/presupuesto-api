<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'monthly_budget',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(BudgetExpense::class);
    }

    /**
     * Suma de los gastos del mes en curso para esta categoría.
     */
    protected function spentThisMonth(): Attribute
    {
        return Attribute::get(function () {
            $now = now();

            return $this->expenses()
                ->whereYear('spent_at', $now->year)
                ->whereMonth('spent_at', $now->month)
                ->sum('amount');
        });
    }
}
