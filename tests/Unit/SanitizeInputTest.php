<?php

namespace Tests\Unit;

use App\Http\Middleware\SanitizeInput;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SanitizeInputTest extends TestCase
{
    protected SanitizeInput $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SanitizeInput();
    }

    /** @test */
    public function it_trims_whitespace_from_input(): void
    {
        $request = Request::create('/test', 'POST', [
            'name' => '  John Doe  ',
            'email' => '  john@example.com  ',
        ]);
        
        $response = $this->middleware->handle($request, function ($req) {
            $this->assertEquals('John Doe', $req->input('name'));
            $this->assertEquals('john@example.com', $req->input('email'));
            return new Response('ok');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_converts_empty_strings_to_null(): void
    {
        $request = Request::create('/test', 'POST', [
            'name' => 'John',
            'middle_name' => '',
            'notes' => '   ',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('John', $req->input('name'));
            $this->assertNull($req->input('middle_name'));
            $this->assertNull($req->input('notes'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_does_not_sanitize_get_requests(): void
    {
        $request = Request::create('/test', 'GET', [
            'name' => '  <script>alert("XSS")</script>  ',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            // GET requests should not be modified
            $this->assertEquals('  <script>alert("XSS")</script>  ', $req->input('name'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_does_not_sanitize_password_fields(): void
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'john',
            'password' => '  Password123!  ',
            'password_confirmation' => '  Password123!  ',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            // Passwords should retain their whitespace
            $this->assertEquals('  Password123!  ', $req->input('password'));
            $this->assertEquals('  Password123!  ', $req->input('password_confirmation'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_strips_html_tags_from_regular_fields(): void
    {
        $request = Request::create('/test', 'POST', [
            'title' => 'Hello <script>alert("XSS")</script> World',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            // Script tags should be stripped
            $this->assertStringNotContainsString('<script>', $req->input('title'));
            $this->assertStringNotContainsString('</script>', $req->input('title'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_allows_safe_html_in_description_fields(): void
    {
        $request = Request::create('/test', 'POST', [
            'description' => '<p>Hello <strong>World</strong></p><script>bad()</script>',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $description = $req->input('description');
            // Safe tags should be preserved
            $this->assertStringContainsString('<p>', $description);
            $this->assertStringContainsString('<strong>', $description);
            // Dangerous tags should be stripped
            $this->assertStringNotContainsString('<script>', $description);
            return new Response('ok');
        });
    }

    /** @test */
    public function it_removes_javascript_urls_from_href(): void
    {
        $request = Request::create('/test', 'POST', [
            'description' => '<a href="javascript:alert(1)">Click me</a>',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $description = $req->input('description');
            $this->assertStringNotContainsString('javascript:', $description);
            return new Response('ok');
        });
    }

    /** @test */
    public function it_removes_inline_event_handlers(): void
    {
        $request = Request::create('/test', 'POST', [
            'description' => '<p onclick="evil()">Text</p>',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $description = $req->input('description');
            $this->assertStringNotContainsString('onclick', $description);
            $this->assertStringNotContainsString('evil()', $description);
            return new Response('ok');
        });
    }

    /** @test */
    public function it_sanitizes_nested_arrays(): void
    {
        $request = Request::create('/test', 'POST', [
            'users' => [
                ['name' => '  John  ', 'email' => '  john@test.com  '],
                ['name' => '  Jane  ', 'email' => '  jane@test.com  '],
            ],
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $users = $req->input('users');
            $this->assertEquals('John', $users[0]['name']);
            $this->assertEquals('john@test.com', $users[0]['email']);
            $this->assertEquals('Jane', $users[1]['name']);
            return new Response('ok');
        });
    }

    /** @test */
    public function it_removes_null_bytes(): void
    {
        $request = Request::create('/test', 'POST', [
            'filename' => "file\0.txt",
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertStringNotContainsString("\0", $req->input('filename'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_preserves_non_string_values(): void
    {
        $request = Request::create('/test', 'POST', [
            'count' => 42,
            'active' => true,
            'price' => 99.99,
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertSame(42, $req->input('count'));
            $this->assertSame(true, $req->input('active'));
            $this->assertSame(99.99, $req->input('price'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_handles_put_requests(): void
    {
        $request = Request::create('/test', 'PUT', [
            'name' => '  Updated Name  ',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('Updated Name', $req->input('name'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_handles_patch_requests(): void
    {
        $request = Request::create('/test', 'PATCH', [
            'name' => '  Patched Name  ',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('Patched Name', $req->input('name'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_does_not_sanitize_current_password(): void
    {
        $request = Request::create('/test', 'POST', [
            'current_password' => '  OldPassword123!  ',
            'new_password' => '  NewPassword123!  ',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('  OldPassword123!  ', $req->input('current_password'));
            $this->assertEquals('  NewPassword123!  ', $req->input('new_password'));
            return new Response('ok');
        });
    }

    /** @test */
    public function it_allows_notes_field_with_safe_html(): void
    {
        $request = Request::create('/test', 'POST', [
            'notes' => '<ul><li>Item 1</li><li>Item 2</li></ul>',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $notes = $req->input('notes');
            $this->assertStringContainsString('<ul>', $notes);
            $this->assertStringContainsString('<li>', $notes);
            return new Response('ok');
        });
    }

    /** @test */
    public function it_allows_content_field_with_safe_html(): void
    {
        $request = Request::create('/test', 'POST', [
            'content' => '<h1>Title</h1><p>Paragraph with <em>emphasis</em></p>',
        ]);
        
        $this->middleware->handle($request, function ($req) {
            $content = $req->input('content');
            $this->assertStringContainsString('<h1>', $content);
            $this->assertStringContainsString('<p>', $content);
            $this->assertStringContainsString('<em>', $content);
            return new Response('ok');
        });
    }
}
