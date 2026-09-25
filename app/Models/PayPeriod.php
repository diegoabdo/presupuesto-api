<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_start',
        'period_end',
        'pay_date',
        'payment_method',
        'work_schedule',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(PayPeriodIncome::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayPeriodDeduction::class);
    }

    /**
     * Suma de todas las líneas de ingreso de esta planilla.
     */
    protected function grossPay(): Attribute
    {
        return Attribute::get(fn () => $this->incomes->sum('amount'));
    }

    /**
     * Suma de todos los descuentos ya calculados de esta planilla.
     */
    protected function totalDeductions(): Attribute
    {
        return Attribute::get(fn () => $this->deductions->sum('amount'));
    }

    /**
     * Bruto menos descuentos.
     */
    protected function netPay(): Attribute
    {
        return Attribute::get(fn () => $this->grossPay - $this->totalDeductions);
    }
}
