<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuids
{
    /**
     * Boot the trait.
     * Inicializa el trait.
     */
    protected static function bootHasUuids(): void
    {
        static::creating(function ($model) {
            // Generate UUID if not already set
            // Genera UUID si no está ya establecido
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     * Obtiene el valor que indica si los IDs son auto-incrementales.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     * Obtiene el tipo de clave auto-incremental.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}

