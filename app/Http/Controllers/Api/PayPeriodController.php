<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayPeriodRequest;
use App\Http\Resources\PayPeriodResource;
use App\Models\DeductionRule;
use App\Models\PayPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayPeriodController extends Controller
{
    public function index(Request $request)
    {
        $periods = $request->user()
            ->payPeriods()
            ->with(['incomes', 'deductions'])
            ->orderByDesc('pay_date')
            ->get();

        return PayPeriodResource::collection($periods);
    }

    public function show(Request $request, PayPeriod $payPeriod)
    {
        $this->authorizeOwnership($request, $payPeriod);

        return new PayPeriodResource($payPeriod->load(['incomes', 'deductions']));
    }

    /**
     * Crea la planilla junto con sus líneas de ingreso y, para cada
     * descuento indicado, calcula el monto (base_amount * regla.percentage)
     * y lo guarda ya resuelto — así el historial no cambia si la regla
     * se edita después.
     */
    public function store(PayPeriodRequest $request)
    {
        $payPeriod = DB::transaction(function () use ($request) {
            $payPeriod = $request->user()->payPeriods()->create($request->safe()->only([
                'period_start',
                'period_end',
                'pay_date',
                'payment_method',
                'work_schedule',
            ]));

            foreach ($request->input('incomes', []) as $income) {
                $payPeriod->incomes()->create($income);
            }

            foreach ($request->input('deductions', []) as $deduction) {
                $rule = DeductionRule::findOrFail($deduction['deduction_rule_id']);
                abort_unless($rule->user_id === $request->user()->id, 403);

                $payPeriod->deductions()->create([
                    'label' => $rule->label,
                    'percentage' => $rule->percentage,
                    'base_amount' => $deduction['base_amount'],
                    'amount' => round($deduction['base_amount'] * $rule->percentage, 2),
                ]);
            }

            return $payPeriod;
        });

        return new PayPeriodResource($payPeriod->load(['incomes', 'deductions']));
    }

    public function destroy(Request $request, PayPeriod $payPeriod)
    {
        $this->authorizeOwnership($request, $payPeriod);

        $payPeriod->delete();

        return response()->json(status: 204);
    }

    private function authorizeOwnership(Request $request, PayPeriod $payPeriod): void
    {
        abort_unless($payPeriod->user_id === $request->user()->id, 403);
    }
}
