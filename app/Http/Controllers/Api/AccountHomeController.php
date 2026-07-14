<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sdui\Runtime\Screens\AccountHomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountHomeController extends Controller
{
    public function __construct(
        private AccountHomeService $accountHomeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            $user = new \App\Models\AuthUser();
        }

        return response()->json($this->accountHomeService->compose($user));
    }
}