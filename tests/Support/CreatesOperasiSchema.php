<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Schema;

trait CreatesOperasiSchema
{
    /**
     * Membuat skema tabel-tabel domain operasi untuk keperluan testing.
     * 100% tersinkronisasi dengan MySQL Frozen v37 (OPS-002A Audit).
     */
    protected function createOperasiSchema(): void
    {
        // Berkas migrasi fisik sekarang menangani pembuatan skema
    }
}
