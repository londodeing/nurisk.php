<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ScopedAuthUser
{
    protected static function bootScopedAuthUser()
    {
        static::addGlobalScope('scoped_auth_user', function (Builder $builder) {
            if (app()->runningInConsole() || !auth()->hasUser()) {
                return;
            }

            $user = auth()->user();

            if (!$user->relationLoaded('peran')) {
                $user->load('peran');
            }

            if ($user->default_scope_type === 'pcnu' || $user->hasRole('pcnu')) {
                $table = $builder->getModel()->getTable();
                if ($user->default_scope_id) {
                    $builder->where($table . '.default_scope_type', 'pcnu')
                            ->where($table . '.default_scope_id', $user->default_scope_id);
                }
            }
        });
    }
}
