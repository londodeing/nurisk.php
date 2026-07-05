<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ScopedByPcnu
{
    protected static function bootScopedByPcnu()
    {
        static::addGlobalScope('scoped_by_pcnu', function (Builder $builder) {
            // Jika berjalan via command line (artisan) tanpa auth, biarkan
            if (app()->runningInConsole() || !auth()->hasUser()) {
                return;
            }

            $user = auth()->user();

            // Jika role/scoped adalah pcnu
            if ($user->default_scope_type === 'pcnu' || $user->hasRole('pcnu')) {
                // Pastikan tabel yang sedang diquery memiliki kolom id_pcnu
                $table = $builder->getModel()->getTable();
                if ($user->default_scope_id) {
                    $builder->where($table . '.id_pcnu', $user->default_scope_id);
                } else {
                    $builder->whereRaw('1 = 0');
                }
            }
        });
    }
}
