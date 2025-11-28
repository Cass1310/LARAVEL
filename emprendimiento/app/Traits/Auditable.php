<?php

namespace App\Traits;

use App\Services\AuditoriaService;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            AuditoriaService::crear(
                class_basename($model),
                "Creación de " . class_basename($model),
                $model->toArray()
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            
            $datosAnteriores = [];
            $datosNuevos = [];
            
            foreach ($changes as $key => $value) {
                if ($key !== 'updated_at') {
                    $datosAnteriores[$key] = $original[$key] ?? null;
                    $datosNuevos[$key] = $value;
                }
            }

            if (!empty($datosNuevos)) {
                AuditoriaService::actualizar(
                    class_basename($model),
                    "Actualización de " . class_basename($model),
                    $datosAnteriores,
                    $datosNuevos
                );
            }
        });

        static::deleted(function ($model) {
            AuditoriaService::eliminar(
                class_basename($model),
                "Eliminación de " . class_basename($model),
                $model->toArray()
            );
        });
    }
}