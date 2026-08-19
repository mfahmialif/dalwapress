<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAuditColumns
{
    public static function bootHasAuditColumns(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && in_array('created_by', $model->getFillable())) {
                $model->created_by = $model->created_by ?? Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && in_array('updated_by', $model->getFillable())) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check()
                && in_array('deleted_by', $model->getFillable())
                && method_exists($model, 'isForceDeleting')
                && !$model->isForceDeleting()
            ) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }
}
