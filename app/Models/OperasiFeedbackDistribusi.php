<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperasiFeedbackDistribusi extends Model
{
    use HasFactory;

    protected $table = 'operasi_feedback_distribusi';
    protected $primaryKey = 'id_feedback';

    public $timestamps = false;

    protected $fillable = [
        'id_distribusi',
        'id_pengguna',
        'kecukupan',
        'kualitas',
        'tepat_waktu',
        'tepat_sasaran',
        'kendala',
        'rekomendasi',
        'status_feedback',
        'dikunci_pada',
    ];

    protected $casts = [
        'tepat_waktu' => 'boolean',
        'tepat_sasaran' => 'boolean',
        'dikunci_pada' => 'datetime',
    ];

    public function distribusi(): BelongsTo
    {
        return $this->belongsTo(OperasiDistribusi::class, 'id_distribusi', 'id_distribusi');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function isFinal(): bool
    {
        return $this->status_feedback === 'final';
    }

    public function finalize(): void
    {
        $this->update([
            'status_feedback' => 'final',
            'dikunci_pada' => now(),
        ]);
    }
}