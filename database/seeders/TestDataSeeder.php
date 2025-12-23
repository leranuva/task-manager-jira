<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Ejecuta los seeders de la base de datos.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creando datos de prueba / Creating test data...');

        // Get or create a team / Obtener o crear un equipo
        $team = Team::first() ?? Team::factory()->create([
            'name' => 'Equipo de Desarrollo',
            'user_id' => User::first()->id,
        ]);

        // Create additional users / Crear usuarios adicionales
        $users = User::factory(5)->create();
        $allUsers = User::all();

        // Create projects / Crear proyectos
        $projects = Project::factory(3)->create([
            'team_id' => $team->id,
            'owner_id' => $allUsers->random()->id,
        ]);

        $this->command->info("✅ {$projects->count()} proyectos creados / {$projects->count()} projects created");

        // Create labels / Crear etiquetas
        $globalLabelNames = ['urgent', 'bug', 'feature', 'enhancement', 'documentation'];
        foreach ($globalLabelNames as $name) {
            Label::factory()->create([
                'team_id' => $team->id,
                'project_id' => null,
                'name' => $name,
            ]);
        }

        $projectLabelNames = ['frontend', 'backend', 'api', 'database', 'testing', 'design'];
        foreach ($projects as $project) {
            $labelIndex = 0;
            foreach (array_slice($projectLabelNames, 0, 3) as $name) {
                Label::factory()->create([
                    'team_id' => $team->id,
                    'project_id' => $project->id,
                    'name' => $name . '-' . $project->id . '-' . $labelIndex++,
                ]);
            }
        }

        $this->command->info("✅ Etiquetas creadas / Labels created");

        // Create tasks for each project / Crear tareas para cada proyecto
        $allTasks = collect();

        foreach ($projects as $project) {
            // Create tasks with sequential keys / Crear tareas con claves secuenciales
            $projectTasks = collect();
            
            for ($i = 1; $i <= 10; $i++) {
                $task = Task::factory()->create([
                    'team_id' => $team->id,
                    'project_id' => $project->id,
                    'creator_id' => $allUsers->random()->id,
                    'key' => $project->key . '-' . $i,
                ]);
                
                // Validate task was created correctly / Validar que la tarea se creó correctamente
                if (!$task->exists) {
                    throw new \RuntimeException("Failed to create task for project: {$project->key}");
                }
                
                // Validate relationships / Validar relaciones
                if ($task->project_id !== $project->id) {
                    throw new \RuntimeException("Task project_id mismatch for task: {$task->key}");
                }
                
                if ($task->team_id !== $team->id) {
                    throw new \RuntimeException("Task team_id mismatch for task: {$task->key}");
                }
                
                $projectTasks->push($task);
            }

            $allTasks = $allTasks->merge($projectTasks);

            // Assign labels to some tasks / Asignar etiquetas a algunas tareas
            $projectLabels = Label::where('project_id', $project->id)->get();
            $globalLabels = Label::whereNull('project_id')->where('team_id', $team->id)->get();
            $availableLabels = $projectLabels->merge($globalLabels);

            foreach ($projectTasks->random(5) as $task) {
                $labelsToAttach = $availableLabels->random(min(rand(1, 3), $availableLabels->count()));
                $labelIds = $labelsToAttach->pluck('id')->toArray();
                
                // Validate labels belong to same team / Validar que las etiquetas pertenecen al mismo equipo
                $invalidLabels = Label::whereIn('id', $labelIds)
                    ->where('team_id', '!=', $team->id)
                    ->exists();
                
                if ($invalidLabels) {
                    throw new \RuntimeException("Label team_id mismatch for task: {$task->key}");
                }
                
                $task->labels()->attach($labelIds);
                
                // Validate labels were attached / Validar que las etiquetas fueron asignadas
                $attachedCount = $task->labels()->count();
                if ($attachedCount !== count($labelIds)) {
                    throw new \RuntimeException(
                        "Label attachment mismatch for task: {$task->key}. Expected: " . count($labelIds) . ", Found: {$attachedCount}"
                    );
                }
            }

            // Assign users to some tasks / Asignar usuarios a algunas tareas
            foreach ($projectTasks->random(7) as $task) {
                $assignedUser = $allUsers->random();
                $assignedBy = $allUsers->random();
                
                $assignment = TaskAssignment::factory()->create([
                    'task_id' => $task->id,
                    'user_id' => $assignedUser->id,
                    'assigned_by' => $assignedBy->id,
                ]);
                
                // Validate assignment was created / Validar que la asignación se creó
                if (!$assignment->exists) {
                    throw new \RuntimeException("Failed to create assignment for task: {$task->key}");
                }
                
                // Validate task has assignment / Validar que la tarea tiene la asignación
                $assignmentExists = $task->assignments()
                    ->where('user_id', $assignedUser->id)
                    ->exists();
                
                if (!$assignmentExists) {
                    throw new \RuntimeException("Assignment not found for task: {$task->key}");
                }
            }

            // Create comments for some tasks / Crear comentarios para algunas tareas
            foreach ($projectTasks->random(5) as $task) {
                $commentCount = rand(2, 5);
                $comments = Comment::factory($commentCount)->create([
                    'team_id' => $team->id,
                    'user_id' => $allUsers->random()->id,
                    'commentable_id' => $task->id,
                    'commentable_type' => Task::class,
                ]);
                
                // Validate comments were created / Validar que los comentarios se crearon
                foreach ($comments as $comment) {
                    if ($comment->team_id !== $team->id) {
                        throw new \RuntimeException("Comment team_id mismatch for task: {$task->key}");
                    }
                    
                    if ($comment->commentable_id !== $task->id) {
                        throw new \RuntimeException("Comment commentable_id mismatch for task: {$task->key}");
                    }
                }
                
                // Validate task has comments / Validar que la tarea tiene comentarios
                $taskCommentCount = $task->comments()->count();
                if ($taskCommentCount !== $commentCount) {
                    throw new \RuntimeException(
                        "Comment count mismatch for task: {$task->key}. Expected: {$commentCount}, Found: {$taskCommentCount}"
                    );
                }
            }
        }

        $this->command->info("✅ {$allTasks->count()} tareas creadas / {$allTasks->count()} tasks created");

        // Create some completed tasks / Crear algunas tareas completadas
        foreach ($projects->random(2) as $project) {
            for ($i = 1; $i <= 3; $i++) {
                $maxTaskNumber = Task::where('project_id', $project->id)
                    ->where('key', 'like', $project->key . '-%')
                    ->get()
                    ->map(function ($t) use ($project) {
                        $parts = explode('-', $t->key);
                        return isset($parts[1]) ? (int)$parts[1] : 0;
                    })
                    ->max() ?? 0;
                
                $taskNumber = $maxTaskNumber + 1;
                
                Task::factory()->completed()->create([
                    'team_id' => $team->id,
                    'project_id' => $project->id,
                    'creator_id' => $allUsers->random()->id,
                    'key' => $project->key . '-' . $taskNumber,
                ]);
            }
        }

        // Create some in-progress tasks / Crear algunas tareas en progreso
        foreach ($projects->random(1) as $project) {
            for ($i = 1; $i <= 3; $i++) {
                $maxTaskNumber = Task::where('project_id', $project->id)
                    ->where('key', 'like', $project->key . '-%')
                    ->get()
                    ->map(function ($t) use ($project) {
                        $parts = explode('-', $t->key);
                        return isset($parts[1]) ? (int)$parts[1] : 0;
                    })
                    ->max() ?? 0;
                
                $taskNumber = $maxTaskNumber + 1;
                
                Task::factory()->inProgress()->create([
                    'team_id' => $team->id,
                    'project_id' => $project->id,
                    'creator_id' => $allUsers->random()->id,
                    'key' => $project->key . '-' . $taskNumber,
                ]);
            }
        }

        // Create comments for projects / Crear comentarios para proyectos
        foreach ($projects->random(2) as $project) {
            Comment::factory(rand(1, 3))->create([
                'team_id' => $team->id,
                'user_id' => $allUsers->random()->id,
                'commentable_id' => $project->id,
                'commentable_type' => Project::class,
            ]);
        }

        $this->command->info("✅ Comentarios creados / Comments created");

        // Final integrity validation / Validación final de integridad
        $this->command->info('🔍 Validando integridad de relaciones / Validating relationship integrity...');
        
        // Validate all tasks belong to projects / Validar que todas las tareas pertenecen a proyectos
        $orphanTasks = Task::where('team_id', $team->id)
            ->whereDoesntHave('project')
            ->count();
        
        if ($orphanTasks > 0) {
            throw new \RuntimeException("Found {$orphanTasks} orphan tasks (without project)");
        }
        
        // Validate all comments belong to valid commentables / Validar que todos los comentarios pertenecen a comentables válidos
        $orphanComments = Comment::where('team_id', $team->id)
            ->get()
            ->filter(function ($comment) {
                $commentable = $comment->commentable;
                return $commentable === null;
            })
            ->count();
        
        if ($orphanComments > 0) {
            throw new \RuntimeException("Found {$orphanComments} orphan comments");
        }
        
        // Validate all assignments belong to valid tasks / Validar que todas las asignaciones pertenecen a tareas válidas
        $orphanAssignments = TaskAssignment::whereHas('task', function ($query) use ($team) {
            $query->where('team_id', $team->id);
        })
        ->whereDoesntHave('task')
        ->count();
        
        if ($orphanAssignments > 0) {
            throw new \RuntimeException("Found {$orphanAssignments} orphan assignments");
        }
        
        $this->command->info('✅ Integridad de relaciones validada / Relationship integrity validated');
        
        $this->command->info('🎉 Datos de prueba creados exitosamente / Test data created successfully');
        $this->command->info("📊 Resumen / Summary:");
        $this->command->info("   - Proyectos / Projects: {$projects->count()}");
        $this->command->info("   - Tareas / Tasks: " . Task::where('team_id', $team->id)->count());
        $this->command->info("   - Comentarios / Comments: " . Comment::where('team_id', $team->id)->count());
        $this->command->info("   - Etiquetas / Labels: " . Label::where('team_id', $team->id)->count());
        $this->command->info("   - Asignaciones / Assignments: " . TaskAssignment::count());
    }
}
