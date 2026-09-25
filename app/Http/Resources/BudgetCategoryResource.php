<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $budgeted = (float) $this->monthly_budget;
        $spent = (float) $this->spentThisMonth;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'monthly_budget' => $budgeted,
            'spent_this_month' => $spent,
            'remaining' => $budgeted - $spent,
            'is_over_budget' => $spent > $budgeted,
        ];
    }
}
