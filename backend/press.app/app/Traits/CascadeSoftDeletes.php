<?php

namespace App\Traits;

trait CascadeSoftDeletes
{
    public static function bootCascadeSoftDeletes(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                foreach ($model->getCascadeSoftDeleteRelations() as $relation) {
                    $model->{$relation}()->each(function ($child) {
                        $child->delete();
                    });
                }
            }
        });

        static::restoring(function ($model) {
            foreach ($model->getCascadeSoftDeleteRelations() as $relation) {
                $model->{$relation}()->onlyTrashed()->each(function ($child) {
                    $child->restore();
                });
            }
        });
    }

    public function getCascadeSoftDeleteRelations(): array
    {
        return property_exists($this, 'cascadeSoftDeletes')
            ? $this->cascadeSoftDeletes
            : [];
    }
}
