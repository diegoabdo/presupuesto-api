<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetCategoryRequest;
use App\Http\Resources\BudgetCategoryResource;
use App\Models\BudgetCategory;
use Illuminate\Http\Request;

class BudgetCategoryController extends Controller
{
    /**
     * Una categoría "de un solo mes" (is_recurring=false) solo se lista
     * durante el mes en que se creó; una recurrente siempre aparece.
     */
    public function index(Request $request)
    {
        $now = now();

        $categories = $request->user()->budgetCategories()
            ->where(function ($query) use ($now) {
                $query->where('is_recurring', true)
                    ->orWhere(function ($query) use ($now) {
                        $query->where('is_recurring', false)
                            ->whereYear('created_at', $now->year)
                            ->whereMonth('created_at', $now->month);
                    });
            })
            ->orderBy('name')
            ->get();

        return BudgetCategoryResource::collection($categories);
    }

    public function store(BudgetCategoryRequest $request)
    {
        $category = $request->user()->budgetCategories()->create($request->validated());

        return new BudgetCategoryResource($category);
    }

    public function update(BudgetCategoryRequest $request, BudgetCategory $budgetCategory)
    {
        $this->authorizeOwnership($request, $budgetCategory);

        $budgetCategory->update($request->validated());

        return new BudgetCategoryResource($budgetCategory);
    }

    public function destroy(Request $request, BudgetCategory $budgetCategory)
    {
        $this->authorizeOwnership($request, $budgetCategory);

        $budgetCategory->delete();

        return response()->json(status: 204);
    }

    private function authorizeOwnership(Request $request, BudgetCategory $budgetCategory): void
    {
        abort_unless($budgetCategory->user_id === $request->user()->id, 403);
    }
}
