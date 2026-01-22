<?php

namespace Tests\Unit;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    protected SecurityHeaders $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SecurityHeaders();
    }

    /** @test */
    public function it_sets_x_content_type_options_header(): void
    {
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('test');
        });

        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    /** @test */
    public function it_sets_x_frame_options_header(): void
    {
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('test');
        });

        $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    /** @test */
    public function it_sets_x_xss_protection_header(): void
    {
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('test');
        });

        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
    }

    /** @test */
    public function it_sets_referrer_policy_header(): void
    {
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('test');
        });

        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }

    /** @test */
    public function it_sets_permissions_policy_header(): void
    {
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('test');
        });

        $permissionsPolicy = $response->headers->get('Permissions-Policy');
        
        $this->assertNotNull($permissionsPolicy);
        $this->assertStringContainsString('camera=()', $permissionsPolicy);
        $this->assertStringContainsString('microphone=()', $permissionsPolicy);
        $this->assertStringContainsString('geolocation=()', $permissionsPolicy);
    }

    /** @test */
    public function it_does_not_set_csp_in_non_production(): void
    {
        // Ensure we're not in production
        config(['app.env' => 'testing']);
        
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('test');
        });

        $this->assertNull($response->headers->get('Content-Security-Policy'));
    }

    /** @test */
    public function it_sets_hsts_in_production_with_https(): void
    {
        config(['app.env' => 'production']);
        
        // Create a secure request
        $request = Request::create('https://example.com/test', 'GET', [], [], [], [
            'HTTPS' => 'on',
        ]);
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('test');
        });

        $hsts = $response->headers->get('Strict-Transport-Security');
        
        if ($request->secure()) {
            $this->assertNotNull($hsts);
            $this->assertStringContainsString('max-age=31536000', $hsts);
            $this->assertStringContainsString('includeSubDomains', $hsts);
        }
    }

    /** @test */
    public function it_removes_x_powered_by_header(): void
    {
        $request = Request::create('/test', 'GET');
        
        $originalResponse = new Response('test');
        $originalResponse->headers->set('X-Powered-By', 'PHP/8.2');
        
        $response = $this->middleware->handle($request, function ($req) use ($originalResponse) {
            return $originalResponse;
        });

        $this->assertNull($response->headers->get('X-Powered-By'));
    }

    /** @test */
    public function it_preserves_original_response_content(): void
    {
        $request = Request::create('/test', 'GET');
        $content = 'Original content';
        
        $response = $this->middleware->handle($request, function ($req) use ($content) {
            return new Response($content);
        });

        $this->assertEquals($content, $response->getContent());
    }

    /** @test */
    public function it_preserves_response_status_code(): void
    {
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Not Found', 404);
        });

        $this->assertEquals(404, $response->getStatusCode());
    }
}
