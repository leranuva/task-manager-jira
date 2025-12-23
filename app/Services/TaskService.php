<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Create a new task.
     * Crea una nueva tarea.
     *
     * @param array<string, mixed> $data
     * @param User $user
     * @return Task
     */
    public function create(array $data, User $user): Task
    {
        return DB::transaction(function () use ($data, $user) {
            // Ensure team_id and creator_id are set / Asegurar que team_id y creator_id estén establecidos
            $data['team_id'] = $data['team_id'] ?? $user->currentTeam?->id;
            $data['creator_id'] = $data['creator_id'] ?? $user->id;

            // Get project to ensure it exists / Obtener proyecto para asegurar que existe
            $project = Project::findOrFail($data['project_id']);
            $data['team_id'] = $project->team_id;

            // Generate task key if not provided / Generar clave de tarea si no se proporciona
            if (empty($data['key'])) {
                $data['key'] = $this->generateTaskKey($project);
            }

            // Set defaults / Establecer valores por defecto
            $data['status'] = $data['status'] ?? 'todo';
            $data['priority'] = $data['priority'] ?? 'medium';
            $data['type'] = $data['type'] ?? 'task';
            $data['position'] = $data['position'] ?? 0;

            // Extract assignee_ids and label_ids / Extraer assignee_ids y label_ids
            $assigneeIds = $data['assignee_ids'] ?? [];
            $labelIds = $data['label_ids'] ?? [];
            unset($data['assignee_ids'], $data['label_ids']);

            // Create task / Crear tarea
            $task = Task::create($data);

            // Assign users / Asignar usuarios
            if (!empty($assigneeIds)) {
                $this->assignUsers($task, $assigneeIds, $user);
            }

            // Attach labels / Adjuntar etiquetas
            if (!empty($labelIds)) {
                $task->labels()->attach($labelIds);
            }

            return $task->load(['project.team', 'project.owner', 'creator', 'assignees', 'labels']);
        });
    }

    /**
     * Update a task.
     * Actualiza una tarea.
     *
     * @param Task $task
     * @param array<string, mixed> $data
     * @param User $user
     * @return Task
     */
    public function update(Task $task, array $data, User $user): Task
    {
        return DB::transaction(function () use ($task, $data, $user) {
            // Handle status changes / Manejar cambios de estado
            if (isset($data['status'])) {
                $this->handleStatusChange($task, $data['status']);
            }

            // Extract assignee_ids and label_ids / Extraer assignee_ids y label_ids
            $assigneeIds = $data['assignee_ids'] ?? null;
            $labelIds = $data['label_ids'] ?? null;
            unset($data['assignee_ids'], $data['label_ids']);

            // Update task / Actualizar tarea
            $task->update($data);

            // Update assignees if provided / Actualizar asignados si se proporcionan
            if ($assigneeIds !== null) {
                $this->syncAssignees($task, $assigneeIds, $user);
            }

            // Update labels if provided / Actualizar etiquetas si se proporcionan
            if ($labelIds !== null) {
                $task->labels()->sync($labelIds);
            }

            return $task->fresh(['project.team', 'project.owner', 'creator', 'assignees', 'labels']);
        });
    }

    /**
     * Delete a task (soft delete).
     * Elimina una tarea (soft delete).
     *
     * @param Task $task
     * @return bool
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Restore a deleted task.
     * Restaura una tarea eliminada.
     *
     * @param Task $task
     * @return bool
     */
    public function restore(Task $task): bool
    {
        return $task->restore();
    }

    /**
     * Assign users to a task.
     * Asigna usuarios a una tarea.
     *
     * @param Task $task
     * @param array<int> $userIds
     * @param User $assignedBy
     * @return void
     */
    public function assignUsers(Task $task, array $userIds, User $assignedBy): void
    {
        foreach ($userIds as $userId) {
            TaskAssignment::updateOrCreate(
                [
                    'task_id' => $task->id,
                    'user_id' => $userId,
                ],
                [
                    'assigned_by' => $assignedBy->id,
                ]
            );
        }
    }

    /**
     * Sync assignees for a task.
     * Sincroniza asignados para una tarea.
     *
     * @param Task $task
     * @param array<int> $userIds
     * @param User $assignedBy
     * @return void
     */
    public function syncAssignees(Task $task, array $userIds, User $assignedBy): void
    {
        // Remove existing assignments / Eliminar asignaciones existentes
        $task->assignments()->delete();

        // Create new assignments / Crear nuevas asignaciones
        if (!empty($userIds)) {
            $this->assignUsers($task, $userIds, $assignedBy);
        }
    }

    /**
     * Generate a unique task key for a project.
     * Genera una clave única de tarea para un proyecto.
     *
     * @param Project $project
     * @return string
     */
    private function generateTaskKey(Project $project): string
    {
        // Get max task number for this project / Obtener número máximo de tarea para este proyecto
        $maxTaskNumber = Task::where('project_id', $project->id)
            ->where('key', 'like', $project->key . '-%')
            ->get()
            ->map(function ($task) use ($project) {
                $parts = explode('-', $task->key);
                return isset($parts[1]) ? (int)$parts[1] : 0;
            })
            ->max() ?? 0;

        $taskNumber = $maxTaskNumber + 1;

        return $project->key . '-' . $taskNumber;
    }

    /**
     * Handle status change and update timestamps.
     * Maneja el cambio de estado y actualiza las marcas de tiempo.
     *
     * @param Task $task
     * @param string $newStatus
     * @return void
     */
    private function handleStatusChange(Task $task, string $newStatus): void
    {
        // Set started_at when moving to in_progress / Establecer started_at al mover a in_progress
        if ($newStatus === 'in_progress' && !$task->started_at) {
            $task->started_at = now();
        }

        // Set completed_at when moving to done / Establecer completed_at al mover a done
        if ($newStatus === 'done' && !$task->completed_at) {
            $task->completed_at = now();
        }

        // Clear completed_at when moving away from done / Limpiar completed_at al alejarse de done
        if ($newStatus !== 'done' && $task->completed_at) {
            $task->completed_at = null;
        }
    }
}

