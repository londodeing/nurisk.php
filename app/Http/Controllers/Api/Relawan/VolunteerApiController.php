<?php

namespace App\Http\Controllers\Api\Relawan;

use App\Http\Controllers\Controller;
use App\Services\Relawan\VolunteerAvailabilityService;
use Illuminate\Http\Request;

class VolunteerApiController extends Controller
{
    private VolunteerAvailabilityService $availabilityService;

    public function __construct(VolunteerAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function getAvailableVolunteers(Request $request)
    {
        $filters = $request->only(['wilayah', 'kompetensi', 'sertifikasi']);
        $volunteers = $this->availabilityService->getAvailableVolunteers($filters);

        return response()->json([
            'success' => true,
            'data' => $volunteers->values(),
        ]);
    }
}
