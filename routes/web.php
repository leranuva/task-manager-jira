<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Projects Routes / Rutas de Proyectos
    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    Route::get('/projects/{project}/kanban', [\App\Http\Controllers\ProjectController::class, 'kanban'])->name('projects.kanban');

    // Tasks Routes / Rutas de Tareas
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);
    Route::post('/tasks/{task}/assign', [\App\Http\Controllers\TaskController::class, 'assign'])->name('tasks.assign');
});
