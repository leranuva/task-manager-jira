<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
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
        return [
            'body' => [
                'required',
                'string',
                'min:1',
                'max:10000',
            ],
            'commentable_type' => [
                'required',
                'string',
                Rule::in(['App\\Models\\Task', 'App\\Models\\Project']),
            ],
            'commentable_id' => [
                'required',
                'uuid',
            ],
            'parent_id' => [
                'nullable',
                'uuid',
                'exists:comments,id',
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
            'body.required' => 'El contenido del comentario es obligatorio. / Comment body is required.',
            'body.min' => 'El comentario debe tener al menos 1 carácter. / Comment must have at least 1 character.',
            'body.max' => 'El comentario no puede exceder 10000 caracteres. / Comment cannot exceed 10000 characters.',
            'commentable_type.required' => 'El tipo de comentable es obligatorio. / Commentable type is required.',
            'commentable_type.in' => 'El tipo de comentable no es válido. / Commentable type is not valid.',
            'commentable_id.required' => 'El ID del comentable es obligatorio. / Commentable ID is required.',
            'commentable_id.uuid' => 'El ID del comentable debe ser un UUID válido. / Commentable ID must be a valid UUID.',
            'parent_id.exists' => 'El comentario padre no existe. / Parent comment does not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     * Prepara los datos para la validación.
     */
    protected function prepareForValidation(): void
    {
        // Set default values / Establecer valores por defecto
        $this->merge([
            'team_id' => $this->user()->currentTeam?->id,
            'user_id' => $this->user()->id,
        ]);
    }
}
