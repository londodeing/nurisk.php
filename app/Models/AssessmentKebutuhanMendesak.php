<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentKebutuhanMendesak extends Model
{
    use HasFactory;

    protected $table = 'assessment_kebutuhan_mendesak';
    public $timestamps = false;
    protected $primaryKey = 'id_kebutuhan_mendesak';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_assessment_utama',
        'nama_kebutuhan',
        'jumlah',
        'satuan',
        'catatan',
    ];

    public function assessmentUtama()
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment_utama', 'id_assessment_utama');
    }
}
