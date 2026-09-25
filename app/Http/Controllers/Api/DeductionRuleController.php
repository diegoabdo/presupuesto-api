<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeductionRuleRequest;
use App\Models\DeductionRule;
use Illuminate\Http\Request;

class DeductionRuleController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->deductionRules()->orderBy('label')->get();
    }

    public function store(DeductionRuleRequest $request)
    {
        $rule = $request->user()->deductionRules()->create([
            'label' => $request->label,
            // El usuario escribe 4.83 (%); se guarda como 0.0483.
            'percentage' => $request->percentage / 100,
        ]);

        return response()->json($rule, 201);
    }

    public function update(DeductionRuleRequest $request, DeductionRule $deductionRule)
    {
        $this->authorizeOwnership($request, $deductionRule);

        $deductionRule->update([
            'label' => $request->label,
            'percentage' => $request->percentage / 100,
        ]);

        return response()->json($deductionRule);
    }

    public function destroy(Request $request, DeductionRule $deductionRule)
    {
        $this->authorizeOwnership($request, $deductionRule);

        $deductionRule->delete();

        return response()->json(status: 204);
    }

    private function authorizeOwnership(Request $request, DeductionRule $deductionRule): void
    {
        abort_unless($deductionRule->user_id === $request->user()->id, 403);
    }
}
