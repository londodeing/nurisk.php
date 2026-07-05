<?php

namespace App\View\Composers;

use App\Services\Auth\AuthorizationContextService;
use App\Services\CommandCenter\ContactDirectoryService;
use App\Services\CommandCenter\DecisionQueueService;
use App\Services\CommandCenter\QuickActionService;
use Illuminate\View\View;

class DashboardComposer
{
    public function __construct(
        private AuthorizationContextService $authCtx,
        private DecisionQueueService $decisionQueue,
        private QuickActionService $quickActions,
        private ContactDirectoryService $contacts,
    ) {}

    public function compose(View $view): void
    {
        $user = $this->authCtx->getCurrentUser();

        $view->with([
            'queue' => $user ? $this->decisionQueue->getQueue($user) : [],
            'actions' => $user ? $this->quickActions->getActions($user) : [],
            'contacts' => $user ? $this->contacts->getContacts($user) : [],
            'alerts' => [],
        ]);
    }
}
