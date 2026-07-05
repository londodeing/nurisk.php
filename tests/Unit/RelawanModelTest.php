<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AuthPenggunaProfil;
use App\Models\AuthKeahlianMaster;
use App\Models\AuthPenggunaKeahlian;
use App\Models\RelawanKebutuhan;
use App\Models\RelawanPendaftaran;
use App\Models\RelawanPenugasan;
use App\Models\RelawanShift;
use App\Models\AuthUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Unit Test untuk domain model Relawan NURISK.
 *
 * Test ini TIDAK menyentuh database — hanya memverifikasi konfigurasi
 * Eloquent model sesuai SQL Frozen v37.
 */
class RelawanModelTest extends TestCase
{
    // =========================================================================
    // AUTH_PENGGUNA_PROFIL
    // =========================================================================

    public function test_auth_pengguna_profil_table_name(): void
    {
        $model = new AuthPenggunaProfil();
        $this->assertEquals('auth_pengguna_profil', $model->getTable());
    }

    public function test_auth_pengguna_profil_primary_key(): void
    {
        $model = new AuthPenggunaProfil();
        $this->assertEquals('id_pengguna', $model->getKeyName());
    }

    public function test_auth_pengguna_profil_not_incrementing(): void
    {
        $model = new AuthPenggunaProfil();
        // id_pengguna bukan auto-increment — nilainya berasal dari auth_users
        $this->assertFalse($model->getIncrementing());
    }

    public function test_auth_pengguna_profil_key_type_is_int(): void
    {
        $model = new AuthPenggunaProfil();
        $this->assertEquals('int', $model->getKeyType());
    }

    public function test_auth_pengguna_profil_no_timestamps(): void
    {
        $model = new AuthPenggunaProfil();
        // Tidak ada dibuat_pada / diperbarui_pada di SQL v37
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_auth_pengguna_profil_fillable(): void
    {
        $model = new AuthPenggunaProfil();
        $fillable = $model->getFillable();
        $this->assertContains('id_pengguna', $fillable);
        $this->assertContains('nik', $fillable);
        $this->assertContains('nama_lengkap', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('id_desa_domisili', $fillable);
    }

    public function test_auth_pengguna_profil_pengguna_relation_type(): void
    {
        $model = new AuthPenggunaProfil();
        $this->assertInstanceOf(BelongsTo::class, $model->pengguna());
    }

    public function test_auth_pengguna_profil_desa_domisili_relation_type(): void
    {
        $model = new AuthPenggunaProfil();
        $this->assertInstanceOf(BelongsTo::class, $model->desaDomisili());
    }

    // =========================================================================
    // AUTH_KEAHLIAN_MASTER
    // =========================================================================

    public function test_auth_keahlian_master_table_name(): void
    {
        $model = new AuthKeahlianMaster();
        $this->assertEquals('auth_keahlian_master', $model->getTable());
    }

    public function test_auth_keahlian_master_primary_key(): void
    {
        $model = new AuthKeahlianMaster();
        $this->assertEquals('id_keahlian', $model->getKeyName());
    }

    public function test_auth_keahlian_master_is_incrementing(): void
    {
        $model = new AuthKeahlianMaster();
        // AUTO_INCREMENT dikonfirmasi via MODIFY di SQL v37
        $this->assertTrue($model->getIncrementing());
    }

    public function test_auth_keahlian_master_key_type_is_int(): void
    {
        $model = new AuthKeahlianMaster();
        $this->assertEquals('int', $model->getKeyType());
    }

    public function test_auth_keahlian_master_no_timestamps(): void
    {
        $model = new AuthKeahlianMaster();
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_auth_keahlian_master_pengguna_relation_is_belongs_to_many(): void
    {
        $model = new AuthKeahlianMaster();
        $this->assertInstanceOf(BelongsToMany::class, $model->pengguna());
    }

    public function test_auth_keahlian_master_kebutuhan_relawan_relation_type(): void
    {
        $model = new AuthKeahlianMaster();
        $this->assertInstanceOf(HasMany::class, $model->kebutuhanRelawan());
    }

    // =========================================================================
    // AUTH_PENGGUNA_KEAHLIAN (PIVOT)
    // =========================================================================

    public function test_auth_pengguna_keahlian_table_name(): void
    {
        $model = new AuthPenggunaKeahlian();
        $this->assertEquals('auth_pengguna_keahlian', $model->getTable());
    }

    public function test_auth_pengguna_keahlian_not_incrementing(): void
    {
        $model = new AuthPenggunaKeahlian();
        // Composite PK — tidak ada single auto-increment PK
        $this->assertFalse($model->getIncrementing());
    }

    public function test_auth_pengguna_keahlian_no_timestamps(): void
    {
        $model = new AuthPenggunaKeahlian();
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_auth_pengguna_keahlian_fillable(): void
    {
        $model = new AuthPenggunaKeahlian();
        $fillable = $model->getFillable();
        $this->assertContains('id_pengguna', $fillable);
        $this->assertContains('id_keahlian', $fillable);
    }

    public function test_auth_pengguna_keahlian_pengguna_relation_type(): void
    {
        $model = new AuthPenggunaKeahlian();
        $this->assertInstanceOf(BelongsTo::class, $model->pengguna());
    }

    public function test_auth_pengguna_keahlian_keahlian_relation_type(): void
    {
        $model = new AuthPenggunaKeahlian();
        $this->assertInstanceOf(BelongsTo::class, $model->keahlian());
    }

    // =========================================================================
    // RELAWAN_KEBUTUHAN
    // =========================================================================

    public function test_relawan_kebutuhan_table_name(): void
    {
        $model = new RelawanKebutuhan();
        $this->assertEquals('relawan_kebutuhan', $model->getTable());
    }

    public function test_relawan_kebutuhan_primary_key(): void
    {
        $model = new RelawanKebutuhan();
        $this->assertEquals('id_relawan_kebutuhan', $model->getKeyName());
    }

    public function test_relawan_kebutuhan_is_incrementing(): void
    {
        $model = new RelawanKebutuhan();
        $this->assertTrue($model->getIncrementing());
    }

    public function test_relawan_kebutuhan_key_type_is_int(): void
    {
        $model = new RelawanKebutuhan();
        $this->assertEquals('int', $model->getKeyType());
    }

    public function test_relawan_kebutuhan_no_standard_timestamps(): void
    {
        $model = new RelawanKebutuhan();
        // Hanya ada dibuat_pada, tidak ada diperbarui_pada
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_relawan_kebutuhan_created_at_column(): void
    {
        // dibuat_pada adalah satu-satunya timestamp yang ada
        $this->assertEquals('dibuat_pada', RelawanKebutuhan::CREATED_AT);
    }

    public function test_relawan_kebutuhan_soft_delete_column(): void
    {
        $this->assertEquals('dihapus_pada', RelawanKebutuhan::DELETED_AT);
    }

    public function test_relawan_kebutuhan_uses_soft_deletes(): void
    {
        $model = new RelawanKebutuhan();
        $this->assertContains(SoftDeletes::class, class_uses_recursive($model));
    }

    public function test_relawan_kebutuhan_fillable(): void
    {
        $model = new RelawanKebutuhan();
        $fillable = $model->getFillable();
        $this->assertContains('id_insiden', $fillable);
        $this->assertContains('id_operasi_klaster', $fillable);
        $this->assertContains('id_posaju', $fillable);
        $this->assertContains('id_keahlian_utama', $fillable);
        $this->assertContains('judul_posisi', $fillable);
        $this->assertContains('deskripsi_tugas', $fillable);
        $this->assertContains('jumlah_dibutuhkan', $fillable);
        $this->assertContains('status_rekrutmen', $fillable);
    }

    public function test_relawan_kebutuhan_insiden_relation_type(): void
    {
        $model = new RelawanKebutuhan();
        $this->assertInstanceOf(BelongsTo::class, $model->insiden());
    }

    public function test_relawan_kebutuhan_keahlian_utama_relation_type(): void
    {
        $model = new RelawanKebutuhan();
        $this->assertInstanceOf(BelongsTo::class, $model->keahlianUtama());
    }

    public function test_relawan_kebutuhan_pendaftaran_relation_type(): void
    {
        $model = new RelawanKebutuhan();
        $this->assertInstanceOf(HasMany::class, $model->pendaftaran());
    }

    public function test_relawan_kebutuhan_is_dibuka_helper(): void
    {
        $model = new RelawanKebutuhan();
        $model->status_rekrutmen = 'dibuka';
        $this->assertTrue($model->isDibuka());

        $model->status_rekrutmen = 'terpenuhi';
        $this->assertFalse($model->isDibuka());
    }

    public function test_relawan_kebutuhan_label_status_rekrutmen(): void
    {
        $model = new RelawanKebutuhan();

        $model->status_rekrutmen = 'dibuka';
        $this->assertEquals('Dibuka', $model->labelStatusRekrutmen());

        $model->status_rekrutmen = 'terpenuhi';
        $this->assertEquals('Terpenuhi', $model->labelStatusRekrutmen());

        $model->status_rekrutmen = 'dibatalkan';
        $this->assertEquals('Dibatalkan', $model->labelStatusRekrutmen());

        $model->status_rekrutmen = 'ditutup';
        $this->assertEquals('Ditutup', $model->labelStatusRekrutmen());
    }

    // =========================================================================
    // RELAWAN_PENDAFTARAN
    // =========================================================================

    public function test_relawan_pendaftaran_table_name(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertEquals('relawan_pendaftaran', $model->getTable());
    }

    public function test_relawan_pendaftaran_primary_key(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertEquals('id_pendaftaran', $model->getKeyName());
    }

    public function test_relawan_pendaftaran_is_incrementing(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertTrue($model->getIncrementing());
    }

    public function test_relawan_pendaftaran_key_type_is_int(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertEquals('int', $model->getKeyType());
    }

    public function test_relawan_pendaftaran_no_standard_timestamps(): void
    {
        $model = new RelawanPendaftaran();
        // Hanya waktu_daftar (bukan created_at/updated_at standar)
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_relawan_pendaftaran_soft_delete_column(): void
    {
        $this->assertEquals('dihapus_pada', RelawanPendaftaran::DELETED_AT);
    }

    public function test_relawan_pendaftaran_uses_soft_deletes(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertContains(SoftDeletes::class, class_uses_recursive($model));
    }

    public function test_relawan_pendaftaran_fillable(): void
    {
        $model = new RelawanPendaftaran();
        $fillable = $model->getFillable();
        $this->assertContains('id_relawan_kebutuhan', $fillable);
        $this->assertContains('id_pengguna', $fillable);
        $this->assertContains('motivasi_singkat', $fillable);
        $this->assertContains('status_pendaftaran', $fillable);
        $this->assertContains('id_verifikator', $fillable);
        $this->assertContains('id_penyaring', $fillable);
    }

    public function test_relawan_pendaftaran_kebutuhan_relation_type(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertInstanceOf(BelongsTo::class, $model->kebutuhan());
    }

    public function test_relawan_pendaftaran_relawan_relation_type(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertInstanceOf(BelongsTo::class, $model->relawan());
    }

    public function test_relawan_pendaftaran_verifikator_relation_type(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertInstanceOf(BelongsTo::class, $model->verifikator());
    }

    public function test_relawan_pendaftaran_penyaring_relation_type(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertInstanceOf(BelongsTo::class, $model->penyaring());
    }

    public function test_relawan_pendaftaran_penugasan_relation_type(): void
    {
        $model = new RelawanPendaftaran();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $model->penugasan());
    }

    public function test_relawan_pendaftaran_is_status_terminal(): void
    {
        $model = new RelawanPendaftaran();

        $model->status_pendaftaran = 'selesai';
        $this->assertTrue($model->isStatusTerminal());

        $model->status_pendaftaran = 'ditolak';
        $this->assertTrue($model->isStatusTerminal());

        $model->status_pendaftaran = 'dibuka';
        $this->assertFalse($model->isStatusTerminal());

        $model->status_pendaftaran = 'diterima';
        $this->assertFalse($model->isStatusTerminal());
    }

    public function test_relawan_pendaftaran_is_diterima_helper(): void
    {
        $model = new RelawanPendaftaran();

        foreach (['diterima', 'ditugaskan', 'selesai'] as $status) {
            $model->status_pendaftaran = $status;
            $this->assertTrue($model->isDiterima(), "Status '$status' seharusnya isDiterima()");
        }

        foreach (['dibuka', 'seleksi', 'ditolak'] as $status) {
            $model->status_pendaftaran = $status;
            $this->assertFalse($model->isDiterima(), "Status '$status' seharusnya NOT isDiterima()");
        }
    }

    public function test_relawan_pendaftaran_label_status(): void
    {
        $model = new RelawanPendaftaran();
        $expected = [
            'dibuka'     => 'Menunggu Seleksi',
            'seleksi'    => 'Dalam Seleksi',
            'diterima'   => 'Diterima',
            'ditugaskan' => 'Ditugaskan',
            'selesai'    => 'Selesai',
            'ditolak'    => 'Ditolak',
        ];

        foreach ($expected as $status => $label) {
            $model->status_pendaftaran = $status;
            $this->assertEquals($label, $model->labelStatus(), "Label untuk status '$status' salah");
        }
    }

    // =========================================================================
    // RELAWAN_PENUGASAN
    // =========================================================================

    public function test_relawan_penugasan_table_name(): void
    {
        $model = new RelawanPenugasan();
        $this->assertEquals('relawan_penugasan', $model->getTable());
    }

    public function test_relawan_penugasan_primary_key(): void
    {
        $model = new RelawanPenugasan();
        $this->assertEquals('id_penugasan_relawan', $model->getKeyName());
    }

    public function test_relawan_penugasan_is_incrementing(): void
    {
        $model = new RelawanPenugasan();
        $this->assertTrue($model->getIncrementing());
    }

    public function test_relawan_penugasan_key_type_is_int(): void
    {
        $model = new RelawanPenugasan();
        $this->assertEquals('int', $model->getKeyType());
    }

    public function test_relawan_penugasan_no_timestamps_at_all(): void
    {
        $model = new RelawanPenugasan();
        // SQL v37: tidak ada dibuat_pada maupun diperbarui_pada
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_relawan_penugasan_soft_delete_column(): void
    {
        $this->assertEquals('dihapus_pada', RelawanPenugasan::DELETED_AT);
    }

    public function test_relawan_penugasan_uses_soft_deletes(): void
    {
        $model = new RelawanPenugasan();
        $this->assertContains(SoftDeletes::class, class_uses_recursive($model));
    }

    public function test_relawan_penugasan_fillable(): void
    {
        $model = new RelawanPenugasan();
        $fillable = $model->getFillable();
        $this->assertContains('id_pendaftaran', $fillable);
        $this->assertContains('id_penugasan_insiden', $fillable);
        $this->assertContains('id_posaju', $fillable);
        $this->assertContains('peran_lapangan', $fillable);
        $this->assertContains('tgl_mulai_aktif', $fillable);
        $this->assertContains('tgl_selesai_aktif', $fillable);
        $this->assertContains('status_aktif', $fillable);
        $this->assertContains('id_surat_tugas', $fillable);
    }

    public function test_relawan_penugasan_pendaftaran_relation_type(): void
    {
        $model = new RelawanPenugasan();
        $this->assertInstanceOf(BelongsTo::class, $model->pendaftaran());
    }

    public function test_relawan_penugasan_shift_relation_type(): void
    {
        $model = new RelawanPenugasan();
        $this->assertInstanceOf(HasMany::class, $model->shift());
    }

    public function test_relawan_penugasan_is_aktif_helper(): void
    {
        $model = new RelawanPenugasan();

        $model->status_aktif = true;
        $this->assertTrue($model->isAktif());

        $model->status_aktif = false;
        $this->assertFalse($model->isAktif());
    }

    // =========================================================================
    // RELAWAN_SHIFT
    // =========================================================================

    public function test_relawan_shift_table_name(): void
    {
        $model = new RelawanShift();
        $this->assertEquals('relawan_shift', $model->getTable());
    }

    public function test_relawan_shift_primary_key(): void
    {
        $model = new RelawanShift();
        $this->assertEquals('id_relawan_shift', $model->getKeyName());
    }

    public function test_relawan_shift_is_incrementing(): void
    {
        $model = new RelawanShift();
        $this->assertTrue($model->getIncrementing());
    }

    public function test_relawan_shift_key_type_is_int(): void
    {
        $model = new RelawanShift();
        $this->assertEquals('int', $model->getKeyType());
    }

    public function test_relawan_shift_no_timestamps(): void
    {
        $model = new RelawanShift();
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_relawan_shift_no_soft_deletes(): void
    {
        $model = new RelawanShift();
        // relawan_shift tidak punya dihapus_pada — cascade dari penugasan
        $this->assertNotContains(SoftDeletes::class, class_uses_recursive($model));
    }

    public function test_relawan_shift_fillable(): void
    {
        $model = new RelawanShift();
        $fillable = $model->getFillable();
        $this->assertContains('id_penugasan_relawan', $fillable);
        $this->assertContains('waktu_mulai', $fillable);
        $this->assertContains('waktu_selesai', $fillable);
    }

    public function test_relawan_shift_penugasan_relation_type(): void
    {
        $model = new RelawanShift();
        $this->assertInstanceOf(BelongsTo::class, $model->penugasan());
    }

    // =========================================================================
    // AUTH_USER — RELASI RELAWAN (tambahan REL-002)
    // =========================================================================

    public function test_auth_user_has_profil_relation(): void
    {
        $model = new AuthUser();
        $this->assertInstanceOf(HasOne::class, $model->profil());
    }

    public function test_auth_user_has_keahlian_relation(): void
    {
        $model = new AuthUser();
        $this->assertInstanceOf(BelongsToMany::class, $model->keahlian());
    }

    public function test_auth_user_keahlian_uses_pivot_table(): void
    {
        $model = new AuthUser();
        $relation = $model->keahlian();
        $this->assertEquals('auth_pengguna_keahlian', $relation->getTable());
    }

    public function test_auth_user_has_pendaftaran_relawan_relation(): void
    {
        $model = new AuthUser();
        $this->assertInstanceOf(HasMany::class, $model->pendaftaranRelawan());
    }

    // =========================================================================
    // RELASI CHAIN INTEGRITY — Verifikasi foreign key kolom
    // =========================================================================

    public function test_relawan_kebutuhan_to_pendaftaran_fk_columns(): void
    {
        $model = new RelawanKebutuhan();
        $relation = $model->pendaftaran();
        // HasMany: localKey = id_relawan_kebutuhan (pada RelawanKebutuhan)
        $this->assertEquals('id_relawan_kebutuhan', $relation->getLocalKeyName());
        // foreignKey = id_relawan_kebutuhan (pada RelawanPendaftaran)
        $this->assertEquals('id_relawan_kebutuhan', $relation->getForeignKeyName());
    }

    public function test_relawan_pendaftaran_to_penugasan_fk_columns(): void
    {
        $model = new RelawanPendaftaran();
        $relation = $model->penugasan();
        // HasOne: localKey = id_pendaftaran (pada RelawanPendaftaran)
        $this->assertEquals('id_pendaftaran', $relation->getLocalKeyName());
    }

    public function test_relawan_penugasan_to_shift_fk_columns(): void
    {
        $model = new RelawanPenugasan();
        $relation = $model->shift();
        // HasMany: localKey = id_penugasan_relawan
        $this->assertEquals('id_penugasan_relawan', $relation->getLocalKeyName());
        $this->assertEquals('id_penugasan_relawan', $relation->getForeignKeyName());
    }
}
