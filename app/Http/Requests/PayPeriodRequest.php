<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'pay_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'work_schedule' => ['required', 'in:medio_tiempo,tiempo_completo,mixto'],

            'incomes' => ['required', 'array', 'min:1'],
            'incomes.*.label' => ['required', 'string', 'max:255'],
            'incomes.*.amount' => ['required', 'numeric', 'min:0'],

            // Cuáles reglas de descuento (ya configuradas en Ajustes)
            // aplicar a esta planilla y sobre qué base calcularlas.
            'deductions' => ['array'],
            'deductions.*.deduction_rule_id' => ['required', 'exists:deduction_rules,id'],
            'deductions.*.base_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
