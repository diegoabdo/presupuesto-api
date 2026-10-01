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
        'is_recurring',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
        'is_recurring' => 'boolean',
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
     * Suma de los gastos del período de pago en curso para esta categoría
     * (no del mes calendario: ver currentPeriodBounds()).
     */
    protected function spentThisMonth(): Attribute
    {
        return Attribute::get(function () {
            [$start, $end] = $this->currentPeriodBounds();

            // whereDate (no where crudo): spent_at se guarda como fecha
            // "Y-m-d" sin hora, así que compararlo contra un Carbon con
            // hora (00:00:00) como string sale mal por longitud/formato —
            // "2026-09-30" resulta "menor" que "2026-09-30 00:00:00" en
            // una comparación de texto, aunque sean el mismo día.
            $query = $this->expenses()->whereDate('spent_at', '>=', $start->toDateString());
            if ($end !== null) {
                $query->whereDate('spent_at', '<', $end->toDateString());
            }

            return $query->sum('amount');
        });
    }

    /**
     * El "mes" para efectos de presupuesto no es el mes calendario: va de
     * la fecha de pago de tu planilla más reciente a un día antes de la
     * siguiente. Así, si te pagan el 30 de cada mes, tu período corre de
     * pago a pago en vez de reiniciarse el día 1 — que es justo cuando
     * normalmente todavía no has registrado ningún gasto nuevo.
     *
     * Si todavía no hay ninguna planilla registrada, usa el mes calendario
     * como respaldo razonable.
     */
    private function currentPeriodBounds(): array
    {
        $payDates = $this->user->payPeriods()->orderBy('pay_date')->pluck('pay_date');

        if ($payDates->isEmpty()) {
            return [now()->startOfMonth(), null];
        }

        $today = now()->startOfDay();
        $start = $payDates->filter(fn ($date) => $date->lte($today))->last() ?? $payDates->first();
        $end = $payDates->first(fn ($date) => $date->gt($start));

        return [$start, $end];
    }
}
