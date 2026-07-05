<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ScopedAuthUser
{
    protected static function bootScopedAuthUser()
    {
        static::addGlobalScope('scoped_auth_user', function (Builder $builder) {
            // Jika berjalan via command line (artisan) tanpa auth, biarkan
            if (app()->runningInConsole() || !auth()->hasUser()) {
                return;
            }

            $user = auth()->user();

            // Jika yang mengakses adalah pcnu
            if ($user->default_scope_type === 'pcnu' || $user->hasRole('pcnu')) {
                $table = $builder->getModel()->getTable();
                // User PCNU hanya boleh melihat user yang memiliki default_scope_id yang sama dengan mereka
                // Atau, untuk pendaftaran baru, filter secara ketat
                if ($user->default_scope_id) {
                    $builder->where($table . '.default_scope_type', 'pcnu')
                            ->where($table . '.default_scope_id', $user->default_scope_id);
                }
            }
        });
    }
}
