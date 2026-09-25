<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeductionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
