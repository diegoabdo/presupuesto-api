<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * is_recurring llega opcional desde el cliente (los endpoints viejos
     * no lo mandan); si falta, se asume true para no romper categorías
     * ya creadas antes de este campo.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_recurring' => $this->boolean('is_recurring', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // null = sin límite de presupuesto (ej. "Sin categoría").
            'monthly_budget' => ['nullable', 'numeric', 'min:0'],
            'is_recurring' => ['boolean'],
        ];
    }
}
