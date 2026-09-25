<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            // Nombre del dispositivo, para poder identificar y revocar
            // el token de este teléfono en particular más adelante.
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }
}
