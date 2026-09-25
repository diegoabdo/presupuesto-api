<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'savings_goal_id',
        'amount',
        'note',
        'contributed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'contributed_at' => 'date',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class, 'savings_goal_id');
    }
}
