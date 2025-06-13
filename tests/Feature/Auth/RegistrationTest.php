<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function regUser($name, $email, $pw = 'verysecure123')
    {
        $response = $this->postJson($this->APIUrl('/register'), [
            'name' => $name,
            'email' => $email,
            'password' => $pw,
            'password_confirmation' => $pw,
        ]);

        return $response;
    }

    public function test_a_user_can_register_successfully(): void
    {
        Event::fake();

        $response = $this->regUser('Test User', 'test@example.com');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'access_token', 'token_type']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertNull(User::first()->email_verified_at); // Fontos: regisztráció után még nem verifikált

        Event::assertDispatched(Registered::class);
    }

    public function test_no_duplicate_users():void
    {
        $response = $this->regUser('Test User', 'test@example.com');
        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'access_token', 'token_type']);
        $response = $this->regUser('Test User', 'test@example.com');

        $response->assertStatus(422);
    }

    public function test_registration_fails_with_invalid_data(): void
    {
        #bad email
        $this->postJson($this->APIUrl('/register'), [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'verysecure123',
        ])->assertStatus(422)
        ->assertJsonValidationErrorFor('email');

        #bad pw, short
        $this->postJson($this->APIUrl('/register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
        ])->assertStatus(422)
            ->assertJsonValidationErrorFor('password');
    }
}
