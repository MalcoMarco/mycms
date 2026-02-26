<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('tenants', 'id'),
            ],
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tenant_id.required' => 'El ID del tenant es obligatorio.',
            'tenant_id.min' => 'El ID debe tener al menos 3 caracteres.',
            'tenant_id.max' => 'El ID no puede tener más de 50 caracteres.',
            'tenant_id.regex' => 'El ID solo puede contener letras minúsculas, números, guiones y guiones bajos.',
            'tenant_id.unique' => 'Este ID ya está en uso.',
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede tener más de 100 caracteres.',
            'description.max' => 'La descripción no puede tener más de 500 caracteres.',
        ];
    }
}
