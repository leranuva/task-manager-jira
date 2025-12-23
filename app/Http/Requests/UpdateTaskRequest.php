<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'project_id' => [
                'sometimes',
                'required',
                'uuid',
                function ($attribute, $value, $fail) use ($teamId) {
                    $project = Project::where('id', $value)
                        ->where('team_id', $teamId)
                        ->first();
                    
                    if (!$project) {
                        $fail('El proyecto no existe o no pertenece a tu equipo. / Project does not exist or does not belong to your team.');
                    }
                },
            ],
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['todo', 'in_progress', 'in_review', 'done', 'cancelled']),
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in(['lowest', 'low', 'medium', 'high', 'highest']),
            ],
            'type' => [
                'nullable',
                'string',
                Rule::in(['task', 'bug', 'feature', 'epic', 'story']),
            ],
            'story_points' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'due_date' => [
                'nullable',
                'date',
            ],
            'started_at' => [
                'nullable',
                'date',
            ],
            'completed_at' => [
                'nullable',
                'date',
            ],
            'position' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'assignee_ids' => [
                'nullable',
                'array',
            ],
            'assignee_ids.*' => [
                'exists:users,id',
            ],
            'label_ids' => [
                'nullable',
                'array',
            ],
            'label_ids.*' => [
                'uuid',
                'exists:labels,id',
            ],
            'metadata' => [
                'nullable',
                'array',
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
            'project_id.required' => 'El proyecto es obligatorio. / Project is required.',
            'project_id.uuid' => 'El ID del proyecto debe ser un UUID válido. / Project ID must be a valid UUID.',
            'title.required' => 'El título de la tarea es obligatorio. / Task title is required.',
            'title.max' => 'El título no puede exceder 255 caracteres. / Title cannot exceed 255 characters.',
            'status.in' => 'El estado no es válido. / Status is not valid.',
            'priority.in' => 'La prioridad no es válida. / Priority is not valid.',
            'type.in' => 'El tipo de tarea no es válido. / Task type is not valid.',
            'story_points.min' => 'Los puntos de historia deben ser al menos 1. / Story points must be at least 1.',
            'story_points.max' => 'Los puntos de historia no pueden exceder 100. / Story points cannot exceed 100.',
            'assignee_ids.array' => 'Los asignados deben ser un array. / Assignees must be an array.',
            'assignee_ids.*.exists' => 'Uno o más usuarios asignados no existen. / One or more assigned users do not exist.',
            'label_ids.array' => 'Las etiquetas deben ser un array. / Labels must be an array.',
            'label_ids.*.exists' => 'Una o más etiquetas no existen. / One or more labels do not exist.',
        ];
    }
}
