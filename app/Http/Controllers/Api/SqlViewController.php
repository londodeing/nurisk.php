<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VCommandCenterSummary;
use App\Models\VAlertInsidenBaru;
use App\Models\VWilayahBlankSpot;
use App\Models\VLogistikDistributionAudit;
use App\Models\VAsetSiapPakai;
use App\Models\VAsetOperasionalReady;
use App\Models\VIncidentTimelineComprehensive;
use App\Models\VRelawanDomisiliCheck;
use App\Models\VUserAccessControl;
use App\Models\VAuditSuratOrphans;
use Illuminate\Http\JsonResponse;

class SqlViewController extends Controller
{
    public function commandCenterSummary(): JsonResponse
    {
        try {
            return response()->json(['data' => VCommandCenterSummary::get()]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => 'view_unavailable'], 503);
        }
    }

    public function incidentTimeline(int $id): JsonResponse
    {
        try {
            return response()->json(['data' => VIncidentTimelineComprehensive::get()]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => 'view_unavailable'], 503);
        }
    }

    public function alertInsiden(): JsonResponse
    {
        try {
            return response()->json(['data' => VAlertInsidenBaru::get()]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => 'view_unavailable'], 503);
        }
    }

    public function blankSpot(): JsonResponse
    {
        return response()->json(['data' => VWilayahBlankSpot::get()]);
    }

    public function logistikAudit(): JsonResponse
    {
        return response()->json(['data' => VLogistikDistributionAudit::get()]);
    }

    public function asetSiapPakai(): JsonResponse
    {
        try {
            return response()->json(['data' => VAsetSiapPakai::get()]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => 'view_unavailable'], 503);
        }
    }

    public function asetOperasionalReady(): JsonResponse
    {
        try {
            return response()->json(['data' => VAsetOperasionalReady::get()]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => 'view_unavailable'], 503);
        }
    }

    public function relawanDomisiliCheck(): JsonResponse
    {
        return response()->json(['data' => VRelawanDomisiliCheck::get()]);
    }

    public function userAccessControl(): JsonResponse
    {
        return response()->json(['data' => VUserAccessControl::get()]);
    }

    public function suratOrphans(): JsonResponse
    {
        return response()->json(['data' => VAuditSuratOrphans::get()]);
    }
}
