<?php

return [
    /*
     * Sync Engine untuk aplikasi mobile (Flutter offline-first)
     * Set ke false untuk pilot v1.0 — aktifkan kembali di v2.0 saat Flutter siap
     */
    'sync_engine_enabled' => env('SYNC_ENGINE_ENABLED', false),

    /*
     * PDF generation via queue (butuh supervisor berjalan)
     * Set ke false di development jika supervisor tidak ada
     */
    'pdf_queue_enabled' => env('PDF_QUEUE_ENABLED', true),
];
