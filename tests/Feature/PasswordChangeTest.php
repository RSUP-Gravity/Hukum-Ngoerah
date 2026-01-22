<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that password change page can be rendered.
     */
    public function test_password_change_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/password/change');

        $response->assertStatus(200);
    }

    /**
     * Test that guests cannot access password change page.
     */
    public function test_guests_cannot_access_password_change_screen(): void
    {
        $response = $this->get('/password/change');

        $response->assertRedirect('/login');
    }

    /**
     * Test that user can change password with valid current password.
     */
    public function test_users_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->post('/password/change', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $user->refresh();

        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $response->assertRedirect();
    }

    /**
     * Test that user cannot change password with wrong current password.
     */
    public function test_users_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->post('/password/change', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
        
        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    /**
     * Test that password confirmation must match.
     */
    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->post('/password/change', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test that new password must meet minimum length.
     */
    public function test_new_password_must_meet_minimum_length(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->post('/password/change', [
            'current_password' => 'oldpassword123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test that must_change_password flag is cleared after password change.
     */
    public function test_must_change_password_flag_is_cleared_after_change(): void
    {
        $user = User::factory()->mustChangePassword()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $this->assertTrue($user->must_change_password);

        $this->actingAs($user)->post('/password/change', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $user->refresh();

        $this->assertFalse($user->must_change_password);
    }

    /**
     * Test that password_changed_at is updated after password change.
     */
    public function test_password_changed_at_is_updated(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
            'password_changed_at' => null,
        ]);

        $this->actingAs($user)->post('/password/change', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $user->refresh();

        $this->assertNotNull($user->password_changed_at);
    }

    /**
     * Test all fields are required.
     */
    public function test_all_fields_are_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/password/change', []);

        $response->assertSessionHasErrors(['current_password', 'password']);
    }
}
