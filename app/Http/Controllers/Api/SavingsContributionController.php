<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavingsContributionRequest;
use App\Http\Resources\SavingsGoalResource;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsContributionController extends Controller
{
    public function store(SavingsContributionRequest $request, SavingsGoal $savingsGoal)
    {
        abort_unless($savingsGoal->user_id === $request->user()->id, 403);

        $savingsGoal->contributions()->create($request->validated());

        // Se devuelve la meta completa, ya con el saldo/porcentaje
        // recalculado, para que el cliente no tenga que sumarlo aparte.
        return new SavingsGoalResource($savingsGoal->load('contributions'));
    }

    public function destroy(Request $request, SavingsGoal $savingsGoal, int $contribution)
    {
        abort_unless($savingsGoal->user_id === $request->user()->id, 403);

        $savingsGoal->contributions()->whereKey($contribution)->firstOrFail()->delete();

        return new SavingsGoalResource($savingsGoal->load('contributions'));
    }
}
