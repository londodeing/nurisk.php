<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OperasiMetricsDaily extends Model
{
    protected $table = 'operasi_metrics_daily';
        protected $primaryKey = 'id';
protected $fillable = [
        'tanggal',
        'login_count',
        'sync_success',
        'sync_failed',
        'sync_conflict_count',
        'bootstrap_count',
        'pdf_success',
        'pdf_failed',
        'queue_backlog_max',
        'avg_sync_duration_ms',
        'avg_bootstrap_duration_ms',
    ];
    protected $casts = [
        'tanggal' => 'date',
        'login_count' => 'integer',
        'sync_success' => 'integer',
        'sync_failed' => 'integer',
        'sync_conflict_count' => 'integer',
        'bootstrap_count' => 'integer',
        'pdf_success' => 'integer',
        'pdf_failed' => 'integer',
        'queue_backlog_max' => 'integer',
        'avg_sync_duration_ms' => 'decimal:2',
        'avg_bootstrap_duration_ms' => 'decimal:2',
    ];
}
