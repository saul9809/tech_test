<?php

namespace App\Traits;

use App\Services\AuditService;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            app(AuditService::class)->log(
                auth()->id() ?? 0,
                $model->getTable(),
                $model->id,
                'created',
                null,
                $model->toArray()
            );
        });

        static::updated(function ($model) {
            app(AuditService::class)->log(
                auth()->id() ?? 0,
                $model->getTable(),
                $model->id,
                'updated',
                $model->getOriginal(),
                $model->toArray()
            );
        });

        static::deleted(function ($model) {
            app(AuditService::class)->log(
                auth()->id() ?? 0,
                $model->getTable(),
                $model->id,
                'deleted',
                $model->toArray(),
                null
            );
        });
    }
}
