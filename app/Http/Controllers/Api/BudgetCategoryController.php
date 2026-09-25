<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetCategoryRequest;
use App\Http\Resources\BudgetCategoryResource;
use App\Models\BudgetCategory;
use Illuminate\Http\Request;

class BudgetCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = $request->user()->budgetCategories()->orderBy('name')->get();

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
