<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class AssessmentKebutuhanNumerikMaster extends Model
{
    protected $table = 'assessment_kebutuhan_numerik_master';
    protected $primaryKey = 'id_item';
    public $timestamps = false;

    protected $fillable = [
        'kode_item', 'nama_item', 'satuan_default', 'kategori', 'aktif', 'urutan'
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];
}
