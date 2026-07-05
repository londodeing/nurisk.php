try {
    $pwnuService = app(\App\Services\PwnuDashboardService::class);
    $pwnuData = $pwnuService->getPollingData();
    echo "PWNU OK\n";

    $pcnuService = app(\App\Services\PcnuDashboardService::class);
    $pcnuData = $pcnuService->getPollingData();
    echo "PCNU OK\n";

    $poskoCmdService = app(\App\Services\PoskoCommanderDashboardService::class);
    $poskoCmdData = $poskoCmdService->getPollingData();
    echo "POSKO_CMD OK\n";

    $poskoService = app(\App\Services\PoskoDashboardService::class);
    $poskoData = $poskoService->getPollingData();
    echo "POSKO OK\n";

    // Trc uses Auth::user(), so we mock Auth
    $user = \App\Models\AuthUser::first();
    \Illuminate\Support\Facades\Auth::login($user);

    $trcService = app(\App\Services\TrcDashboardService::class);
    $trcData = $trcService->getPollingData();
    echo "TRC OK\n";

    $opQService = app(\App\Services\OperatorWorkQueueService::class);
    $opQData = $opQService->getPendingQueue();
    echo "OPERATOR_Q OK\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
