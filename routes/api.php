<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetCategoryController;
use App\Http\Controllers\Api\BudgetExpenseController;
use App\Http\Controllers\Api\DeductionRuleController;
use App\Http\Controllers\Api\IncomeSourceController;
use App\Http\Controllers\Api\PayPeriodController;
use App\Http\Controllers\Api\SavingsContributionController;
use App\Http\Controllers\Api\SavingsGoalController;
use Illuminate\Support\Facades\Route;

// Rutas públicas: no necesitan un token todavía.
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Todo lo demás requiere el Bearer token que devuelve /login o /register.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::apiResource('income-sources', IncomeSourceController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::apiResource('deduction-rules', DeductionRuleController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::apiResource('pay-periods', PayPeriodController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::apiResource('budget-categories', BudgetCategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::apiResource('budget-expenses', BudgetExpenseController::class)
        ->only(['index', 'store', 'destroy']);

    Route::apiResource('savings-goals', SavingsGoalController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::post('/savings-goals/{savingsGoal}/contributions', [SavingsContributionController::class, 'store']);
    Route::delete('/savings-goals/{savingsGoal}/contributions/{contribution}', [SavingsContributionController::class, 'destroy']);
});
