<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by Policy / La autorización será manejada por Policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teamId = $this->user()->currentTeam?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'key' => [
                'required',
                'string',
                'max:10',
                'regex:/^[A-Z0-9]+$/', // Solo letras mayúsculas y números
                Rule::unique('projects', 'key')->where(function ($query) use ($teamId) {
                    return $query->where('team_id', $teamId);
                }),
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/', // Color hexadecimal válido
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'settings' => [
                'nullable',
                'array',
            ],
            'settings.default_task_type' => [
                'nullable',
                'string',
                Rule::in(['task', 'bug', 'feature', 'epic', 'story']),
            ],
            'settings.default_priority' => [
                'nullable',
                'string',
                Rule::in(['lowest', 'low', 'medium', 'high', 'highest']),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     * Obtiene mensajes personalizados para errores de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proyecto es obligatorio. / Project name is required.',
            'name.max' => 'El nombre no puede exceder 255 caracteres. / Name cannot exceed 255 characters.',
            'key.required' => 'La clave del proyecto es obligatoria. / Project key is required.',
            'key.unique' => 'Esta clave ya está en uso. / This key is already in use.',
            'key.regex' => 'La clave solo puede contener letras mayúsculas y números. / Key can only contain uppercase letters and numbers.',
            'key.max' => 'La clave no puede exceder 10 caracteres. / Key cannot exceed 10 characters.',
            'description.max' => 'La descripción no puede exceder 5000 caracteres. / Description cannot exceed 5000 characters.',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #3B82F6). / Color must be a valid hexadecimal code (e.g., #3B82F6).',
        ];
    }

    /**
     * Prepare the data for validation.
     * Prepara los datos para la validación.
     */
    protected function prepareForValidation(): void
    {
        // Ensure key is uppercase / Asegurar que la clave esté en mayúsculas
        if ($this->has('key')) {
            $this->merge([
                'key' => strtoupper($this->key),
            ]);
        }

        // Set default values / Establecer valores por defecto
        $this->merge([
            'team_id' => $this->user()->currentTeam?->id,
            'owner_id' => $this->user()->id,
            'is_active' => $this->input('is_active', true),
            'color' => $this->input('color', '#3B82F6'),
        ]);
    }
}
