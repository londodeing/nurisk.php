<?php

namespace App\Observers;

use App\Models\AssessmentUtama;
use Illuminate\Support\Facades\DB;

class AssessmentUtamaObserver
{
    public function created(AssessmentUtama $assessment)
    {
        if ($assessment->is_latest) {
            DB::table('assessment_utama')
                ->where('id_insiden', $assessment->id_insiden)
                ->where('id_assessment_utama', '!=', $assessment->id_assessment_utama)
                ->update(['is_latest' => 0]);
        }
    }

    public function updated(AssessmentUtama $assessment)
    {
        if ($assessment->isDirty('is_latest') && $assessment->is_latest) {
            DB::table('assessment_utama')
                ->where('id_insiden', $assessment->id_insiden)
                ->where('id_assessment_utama', '!=', $assessment->id_assessment_utama)
                ->update(['is_latest' => 0]);
        }
    }
}
