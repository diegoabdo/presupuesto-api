<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'default_amount',
        'is_recurring',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
