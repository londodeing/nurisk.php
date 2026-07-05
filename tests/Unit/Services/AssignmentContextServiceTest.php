<?php

namespace Tests\Unit\Services;

use Tests\TestCase;

class AssignmentContextServiceTest extends TestCase
{
    public function test_command_staff_punya_authority(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R003');
    }

    public function test_relawan_tanpa_assignment_tidak_punya_akses(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R003');
    }

    public function test_relawan_dengan_field_assignment_punya_akses(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R003');
    }

    public function test_has_any_assignment_true_jika_salah_satu_ada(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R003');
    }

    public function test_peran_tertinggi_komandan_menang_dari_relawan(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R003');
    }

    public function test_selesainya_penugasan_command_menghilangkan_akses(): void
    {
        $this->markTestSkipped('Skipping due to missing factories for R003');
    }
}
