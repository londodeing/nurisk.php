<?php

namespace App\Services\Bff;

use Illuminate\Http\Request;

class BffContextService
{
    protected string $activeRole = 'guest';
    protected ?string $scopeId = null;

    public function __construct(Request $request)
    {
        // Extract context from headers / auth
        if ($user = $request->user()) {
            $this->activeRole = 'authenticated'; // Base role
            
            // In Flutter, we pass X-Scope-Id and sometimes role info via headers or token
            if ($request->hasHeader('X-Scope-Id')) {
                $this->scopeId = $request->header('X-Scope-Id');
            }
            // For now we simulate the role from a custom header or just default to 'pcnu' if they have a scope
            if ($this->scopeId) {
                // In real app, we verify the user's role on this scope
                $this->activeRole = 'pcnu'; // Mocking role
            }
        }
    }

    public function getActiveRole(): string
    {
        return $this->activeRole;
    }

    public function getScopeId(): ?string
    {
        return $this->scopeId;
    }

    public function canApproveDocuments(): bool
    {
        return in_array($this->activeRole, ['pcnu', 'pwnu', 'ketua']);
    }

    public function canViewMap(): bool
    {
        return $this->activeRole !== 'guest';
    }
}
