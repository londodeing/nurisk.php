<?php

namespace Tests\Unit\Bff;

use App\Services\Bff\BffContextService;
use Illuminate\Http\Request;
use App\Models\AuthUser;
use PHPUnit\Framework\TestCase;

class BffContextServiceTest extends TestCase
{
    public function test_guest_context()
    {
        $request = Request::create('/api/bff/dashboard', 'GET');
        $service = new BffContextService($request);

        $this->assertEquals('guest', $service->getActiveRole());
        $this->assertNull($service->getScopeId());
        $this->assertFalse($service->canApproveDocuments());
        $this->assertFalse($service->canViewMap());
    }

    public function test_authenticated_without_scope()
    {
        $user = new AuthUser();
        
        $request = Request::create('/api/bff/dashboard', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $service = new BffContextService($request);

        $this->assertEquals('authenticated', $service->getActiveRole());
        $this->assertNull($service->getScopeId());
        $this->assertFalse($service->canApproveDocuments());
        $this->assertTrue($service->canViewMap());
    }

    public function test_authenticated_with_scope_mock_pcnu()
    {
        $user = new AuthUser();
        
        $request = Request::create('/api/bff/dashboard', 'GET');
        $request->headers->set('X-Scope-Id', '1');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $service = new BffContextService($request);

        $this->assertEquals('pcnu', $service->getActiveRole());
        $this->assertEquals('1', $service->getScopeId());
        $this->assertTrue($service->canApproveDocuments());
        $this->assertTrue($service->canViewMap());
    }
}
