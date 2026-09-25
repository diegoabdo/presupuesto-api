<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_start' => $this->period_start->toDateString(),
            'period_end' => $this->period_end->toDateString(),
            'pay_date' => $this->pay_date->toDateString(),
            'payment_method' => $this->payment_method,
            'work_schedule' => $this->work_schedule,
            'incomes' => $this->incomes->map(fn ($income) => [
                'id' => $income->id,
                'label' => $income->label,
                'amount' => (float) $income->amount,
            ]),
            'deductions' => $this->deductions->map(fn ($deduction) => [
                'id' => $deduction->id,
                'label' => $deduction->label,
                'percentage' => (float) $deduction->percentage,
                'base_amount' => (float) $deduction->base_amount,
                'amount' => (float) $deduction->amount,
            ]),
            'gross_pay' => (float) $this->grossPay,
            'total_deductions' => (float) $this->totalDeductions,
            'net_pay' => (float) $this->netPay,
        ];
    }
}
