<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| Aquí es donde puedes registrar las rutas API para tu aplicación. Estas
| rutas son cargadas por el RouteServiceProvider y todas ellas serán
| asignadas al grupo de middleware "api". ¡Haz algo genial!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Projects / Proyectos
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{project}/restore', [ProjectController::class, 'restore'])
        ->name('projects.restore')
        ->withTrashed();

    // Tasks / Tareas
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/restore', [TaskController::class, 'restore'])
        ->name('tasks.restore')
        ->withTrashed();
    Route::post('tasks/{task}/assign', [TaskController::class, 'assign'])
        ->name('tasks.assign');

    // Comments / Comentarios
    Route::apiResource('comments', CommentController::class);
});
