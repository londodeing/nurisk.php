<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperasiGapKebutuhan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_gap_kebutuhan';
    protected $primaryKey = 'id_gap_kebutuhan';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'uuid_gap_kebutuhan',
        'id_insiden',
        'id_klaster_operasi',
        'id_feedback_klaster',
        'jenis_gap',
        'deskripsi_gap',
        'selisih_jumlah',
        'satuan',
        'prioritas',
        'status_gap',
        'id_penugasan',
        'catatan_penanganan',
        'waktu_terselesaikan',
    ];

    protected $casts = [
        'waktu_terselesaikan' => 'datetime',
    ];

    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function klasterOperasi(): BelongsTo
    {
        return $this->belongsTo(OperasiKlaster::class, 'id_klaster_operasi', 'id_klaster_operasi');
    }

    public function feedbackKlaster(): BelongsTo
    {
        return $this->belongsTo(OperasiFeedbackKlaster::class, 'id_feedback_klaster', 'id_feedback_klaster');
    }

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(OperasiPenugasan::class, 'id_penugasan', 'id_penugasan');
    }

    public function isTerselesaikan(): bool
    {
        return $this->status_gap === 'terselesaikan';
    }

    public function markTerselesaikan(): void
    {
        $this->update([
            'status_gap' => 'terselesaikan',
            'waktu_terselesaikan' => now(),
        ]);
    }

    public function labelPrioritas(): string
    {
        $colors = [
            'rendah' => 'bg-slate-100 text-slate-700',
            'sedang' => 'bg-blue-100 text-blue-700',
            'tinggi' => 'bg-orange-100 text-orange-700',
            'kritis' => 'bg-red-100 text-red-700',
        ];
        return '<span class="px-2 py-1 rounded-full text-xs font-semibold ' . ($colors[$this->prioritas] ?? 'bg-slate-100 text-slate-700') . '">' . ucfirst($this->prioritas) . '</span>';
    }

    public function labelStatus(): string
    {
        $colors = [
            'dibuka' => 'bg-yellow-100 text-yellow-700',
            'diprioritaskan' => 'bg-blue-100 text-blue-700',
            'dikerjakan' => 'bg-purple-100 text-purple-700',
            'terselesaikan' => 'bg-green-100 text-green-700',
            'ditutup' => 'bg-slate-100 text-slate-700',
        ];
        return '<span class="px-2 py-1 rounded-full text-xs font-semibold ' . ($colors[$this->status_gap] ?? 'bg-slate-100 text-slate-700') . '">' . ucfirst($this->status_gap) . '</span>';
    }
}