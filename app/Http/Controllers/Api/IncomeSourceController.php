<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeSourceRequest;
use App\Models\IncomeSource;
use Illuminate\Http\Request;

class IncomeSourceController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->incomeSources()->orderBy('label')->get();
    }

    public function store(IncomeSourceRequest $request)
    {
        $source = $request->user()->incomeSources()->create($request->validated());

        return response()->json($source, 201);
    }

    public function update(IncomeSourceRequest $request, IncomeSource $incomeSource)
    {
        $this->authorizeOwnership($request, $incomeSource);

        $incomeSource->update($request->validated());

        return response()->json($incomeSource);
    }

    public function destroy(Request $request, IncomeSource $incomeSource)
    {
        $this->authorizeOwnership($request, $incomeSource);

        $incomeSource->delete();

        return response()->json(status: 204);
    }

    private function authorizeOwnership(Request $request, IncomeSource $incomeSource): void
    {
        abort_unless($incomeSource->user_id === $request->user()->id, 403);
    }
}
