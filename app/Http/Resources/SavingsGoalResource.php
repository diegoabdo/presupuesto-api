<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'target_amount' => (float) $this->target_amount,
            'target_date' => $this->target_date?->toDateString(),
            'current_amount' => (float) $this->currentAmount,
            'progress' => round($this->progress, 4),
            'contributions' => $this->whenLoaded('contributions', fn () => $this->contributions
                ->sortByDesc('contributed_at')
                ->values()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'amount' => (float) $c->amount,
                    'note' => $c->note,
                    'contributed_at' => $c->contributed_at->toDateString(),
                ])),
        ];
    }
}
