<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_new_registration_cannot_self_assign_the_admin_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin', // attacker-supplied field
        ]);

        $response->assertRedirect('/');

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertSame('customer', $user->role);
    }
}