<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $budgeted = $this->monthly_budget !== null ? (float) $this->monthly_budget : null;
        $spent = (float) $this->spentThisMonth;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'monthly_budget' => $budgeted,
            'spent_this_month' => $spent,
            'remaining' => $budgeted !== null ? $budgeted - $spent : null,
            'is_over_budget' => $budgeted !== null && $spent > $budgeted,
            'is_recurring' => (bool) $this->is_recurring,
        ];
    }
}
