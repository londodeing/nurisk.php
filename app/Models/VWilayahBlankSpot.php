<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VWilayahBlankSpot extends Model
{
    protected $table = 'v_wilayah_blank_spot';
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
