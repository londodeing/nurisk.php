<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VAsetOperasionalReady extends Model
{
    protected $table = 'v_aset_operasional_ready';
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $guarded = [];

    public function save(array $options = []): bool
    {
        return false;
    }

    public function delete(): bool
    {
        return false;
    }
}
