<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Services\Dashboard\Composer\PublicIncidentListComposer;
use App\Services\Dashboard\Composer\PublicMissionListComposer;

class PublicListApiController extends Controller
{
    public function incidentList(PublicIncidentListComposer $composer): JsonResponse
    {
        $node = $composer->compose();

        return response()->json([
            'screen' => 'IncidentList',
            'layout' => 'vertical',
            'nodes' => [$node]
        ]);
    }

    public function missionList(PublicMissionListComposer $composer): JsonResponse
    {
        $node = $composer->compose();

        return response()->json([
            'screen' => 'MissionList',
            'layout' => 'vertical',
            'nodes' => [$node]
        ]);
    }
}
