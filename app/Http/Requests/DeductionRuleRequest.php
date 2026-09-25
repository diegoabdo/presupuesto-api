<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeductionRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            // Se recibe como porcentaje humano (ej. 4.83) y se guarda como
            // fracción (0.0483); ver DeductionRuleController::store().
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
