<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayPeriodDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'pay_period_id',
        'label',
        'percentage',
        'base_amount',
        'amount',
    ];

    protected $casts = [
        'percentage' => 'decimal:4',
        'base_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function payPeriod(): BelongsTo
    {
        return $this->belongsTo(PayPeriod::class);
    }
}
