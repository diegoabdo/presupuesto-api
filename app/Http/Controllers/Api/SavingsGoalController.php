<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavingsGoalRequest;
use App\Http\Resources\SavingsGoalResource;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        $goals = $request->user()->savingsGoals()->with('contributions')->orderBy('name')->get();

        return SavingsGoalResource::collection($goals);
    }

    public function show(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorizeOwnership($request, $savingsGoal);

        return new SavingsGoalResource($savingsGoal->load('contributions'));
    }

    public function store(SavingsGoalRequest $request)
    {
        $goal = $request->user()->savingsGoals()->create($request->validated());

        return new SavingsGoalResource($goal->load('contributions'));
    }

    public function update(SavingsGoalRequest $request, SavingsGoal $savingsGoal)
    {
        $this->authorizeOwnership($request, $savingsGoal);

        $savingsGoal->update($request->validated());

        return new SavingsGoalResource($savingsGoal->load('contributions'));
    }

    public function destroy(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorizeOwnership($request, $savingsGoal);

        $savingsGoal->delete();

        return response()->json(status: 204);
    }

    private function authorizeOwnership(Request $request, SavingsGoal $savingsGoal): void
    {
        abort_unless($savingsGoal->user_id === $request->user()->id, 403);
    }
}
