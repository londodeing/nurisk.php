<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Refactor assessment_dampak_lingkungan
        Schema::table('assessment_dampak_lingkungan', function (Blueprint $table) {
            // Drop old columns
            if (Schema::hasColumn('assessment_dampak_lingkungan', 'butuh_rehabilitasi_lahan')) {
                $table->dropColumn('butuh_rehabilitasi_lahan');
            }
            if (Schema::hasColumn('assessment_dampak_lingkungan', 'tingkat_kerusakan_lingkungan')) {
                $table->dropColumn('tingkat_kerusakan_lingkungan');
            }
            if (Schema::hasColumn('assessment_dampak_lingkungan', 'ternak_terdampak_ekor')) {
                $table->dropColumn('ternak_terdampak_ekor');
            }
            
            // Add new columns
            $table->integer('ternak_unggas_ekor')->default(0)->after('kerusakan_daerah_aliran_sungai');
            $table->integer('ternak_kaki_empat_ekor')->default(0)->after('ternak_unggas_ekor');
            $table->decimal('perikanan_kolam_ha', 10, 2)->default(0)->after('ternak_kaki_empat_ekor');
            $table->integer('perikanan_nelayan_unit')->default(0)->after('perikanan_kolam_ha');
        });

        // 2. Refactor assessment_dampak_ekonomi
        Schema::table('assessment_dampak_ekonomi', function (Blueprint $table) {
            // Drop old columns
            $columnsToDrop = [
                'kerugian_perumahan', 'kerugian_pertanian', 'kerugian_peternakan',
                'kerugian_perikanan', 'kerugian_umkm', 'kerugian_infrastruktur',
                'kerugian_lainnya', 'estimasi_kerugian_total', 'mata_pencaharian_hilang',
                'usaha_terdampak', 'metodologi_estimasi'
            ];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('assessment_dampak_ekonomi', $col)) {
                    $table->dropColumn($col);
                }
            }

            // Add new communal economic columns
            $table->enum('persentase_ekonomi_terdampak', ['< 25%', '25% - 50%', '51% - 75%', '> 75%'])->nullable()->after('id_assessment');
            
            $table->string('sektor_pencaharian_1', 255)->nullable()->after('persentase_ekonomi_terdampak');
            $table->decimal('kontribusi_1', 5, 2)->nullable()->after('sektor_pencaharian_1');
            $table->enum('status_terdampak_1', ['tidak_terdampak', 'sementara', 'permanen'])->nullable()->after('kontribusi_1');
            
            $table->string('sektor_pencaharian_2', 255)->nullable()->after('status_terdampak_1');
            $table->decimal('kontribusi_2', 5, 2)->nullable()->after('sektor_pencaharian_2');
            $table->enum('status_terdampak_2', ['tidak_terdampak', 'sementara', 'permanen'])->nullable()->after('kontribusi_2');
            
            $table->string('sektor_pencaharian_3', 255)->nullable()->after('status_terdampak_2');
            $table->decimal('kontribusi_3', 5, 2)->nullable()->after('sektor_pencaharian_3');
            $table->enum('status_terdampak_3', ['tidak_terdampak', 'sementara', 'permanen'])->nullable()->after('kontribusi_3');
            
            $table->enum('distribusi_hasil_panen', ['berfungsi', 'rusak_sebagian', 'rusak_total'])->nullable()->after('status_terdampak_3');
            $table->enum('fasilitas_pengolahan_kolektif', ['berfungsi', 'rusak_sebagian', 'rusak_total'])->nullable()->after('distribusi_hasil_panen');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_dampak_ekonomi', function (Blueprint $table) {
            $table->dropColumn([
                'persentase_ekonomi_terdampak',
                'sektor_pencaharian_1', 'kontribusi_1', 'status_terdampak_1',
                'sektor_pencaharian_2', 'kontribusi_2', 'status_terdampak_2',
                'sektor_pencaharian_3', 'kontribusi_3', 'status_terdampak_3',
                'distribusi_hasil_panen', 'fasilitas_pengolahan_kolektif'
            ]);

            $table->decimal('kerugian_perumahan', 18, 2)->default(0);
            $table->decimal('kerugian_pertanian', 18, 2)->default(0);
            $table->decimal('kerugian_peternakan', 18, 2)->default(0);
            $table->decimal('kerugian_perikanan', 18, 2)->default(0);
            $table->decimal('kerugian_umkm', 18, 2)->default(0);
            $table->decimal('kerugian_infrastruktur', 18, 2)->default(0);
            $table->decimal('kerugian_lainnya', 18, 2)->default(0);
            $table->decimal('estimasi_kerugian_total', 18, 2)->default(0);
            $table->integer('mata_pencaharian_hilang')->default(0);
            $table->integer('usaha_terdampak')->default(0);
            $table->string('metodologi_estimasi', 255)->default('estimasi_lapangan');
        });

        Schema::table('assessment_dampak_lingkungan', function (Blueprint $table) {
            $table->dropColumn([
                'ternak_unggas_ekor', 'ternak_kaki_empat_ekor',
                'perikanan_kolam_ha', 'perikanan_nelayan_unit'
            ]);

            $table->boolean('butuh_rehabilitasi_lahan')->default(false);
            $table->enum('tingkat_kerusakan_lingkungan', ['tidak_ada', 'ringan', 'sedang', 'berat', 'sangat_berat'])->default('tidak_ada');
            $table->integer('ternak_terdampak_ekor')->default(0);
        });
    }
};
