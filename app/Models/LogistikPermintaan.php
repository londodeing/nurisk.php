<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogistikPermintaan extends Model
{
    use SoftDeletes;
    protected $table = 'logistik_permintaan';
    protected $primaryKey = 'id_permintaan';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_operasi_klaster',
        'id_penugasan',
        'id_posaju_tujuan',
        'prioritas',
        'status_permintaan'
    ];

    public function klaster() {
        return $this->belongsTo(OperasiKlaster::class, 'id_operasi_klaster', 'id_klaster_operasi');
    }

    public function penugasan() {
        return $this->belongsTo(OperasiPenugasan::class, 'id_penugasan', 'id_incident_assignment');
    }

    public function posaju() {
        return $this->belongsTo(OperasiPosaju::class, 'id_posaju_tujuan', 'id_posaju');
    }
}
