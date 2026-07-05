<?php

namespace App\Services\Operasi;

use App\Models\AssessmentUtama;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentDeletionGuard
{
    /**
     * @return bool True if assessment can be deleted, false otherwise
     */
    public function canDelete(AssessmentUtama $assessment): bool
    {
        // Check if operasi_sitrep table exists and references this assessment
        if (Schema::hasTable('operasi_sitrep')) {
            $isReferenced = DB::table('operasi_sitrep')
                ->where('id_assessment_basis', $assessment->id_assessment_utama)
                ->whereNull('dihapus_pada')
                ->exists();

            if ($isReferenced) {
                return false;
            }
        }

        // Add more dependencies here if needed in the future

        return true;
    }
}
