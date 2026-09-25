<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetExpenseRequest;
use App\Models\BudgetCategory;
use App\Models\BudgetExpense;
use Illuminate\Http\Request;

class BudgetExpenseController extends Controller
{
    /**
     * Lista de gastos, opcionalmente filtrada por categoría o por mes
     * (?month=2026-09) para poblar el historial de una categoría.
     */
    public function index(Request $request)
    {
        $query = $request->user()->budgetExpenses()->with('category')->orderByDesc('spent_at');

        if ($request->filled('budget_category_id')) {
            $query->where('budget_category_id', $request->integer('budget_category_id'));
        }

        if ($request->filled('month')) {
            // Se recibe como "2026-09"; whereYear/whereMonth funcionan
            // igual en SQLite, MySQL y PostgreSQL (a diferencia de un
            // strftime/DATE_FORMAT crudo, que no es portable entre motores).
            [$year, $month] = explode('-', $request->string('month'));
            $query->whereYear('spent_at', $year)->whereMonth('spent_at', $month);
        }

        return $query->get();
    }

    public function store(BudgetExpenseRequest $request)
    {
        $category = BudgetCategory::findOrFail($request->budget_category_id);
        abort_unless($category->user_id === $request->user()->id, 403);

        $expense = $request->user()->budgetExpenses()->create($request->validated());

        return response()->json($expense->load('category'), 201);
    }

    public function destroy(Request $request, BudgetExpense $budgetExpense)
    {
        abort_unless($budgetExpense->user_id === $request->user()->id, 403);

        $budgetExpense->delete();

        return response()->json(status: 204);
    }
}
