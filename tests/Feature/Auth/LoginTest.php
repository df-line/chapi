<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_verified_user_can_login(): void
    {
        $user = User::factory()->verified()->create([
            'password' => bcrypt('verysecure123'),
        ]);

        $response = $this->postJson($this->APIUrl('/login'), [
            'email' => $user->email,
            'password' => 'verysecure123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type']);
    }

    public function test_an_unverified_user_cannot_login(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => bcrypt('verysecure123'),
        ]);

        $response = $this->postJson($this->APIUrl('/login'), [
            'email' => $user->email,
            'password' => 'verysecure123',
        ]);

        $response->assertStatus(403)
        ->assertJson(['error' => 'Please verify your email address first.']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->verified()->create([
            'password' => bcrypt('verysecure123'),
        ]);

        $response = $this->postJson($this->APIUrl('/login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401) // Unauthorized
        ->assertJson(['error' => 'Invalid credentials']);
    }

    public function test_an_authenticated_user_can_logout(): void
    {
        $user = User::factory()->verified()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $this->assertCount(1, $user->tokens);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson($this->APIUrl('/logout'));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);

        //Token got deleted, fresh reloads the model from db
        $this->assertCount(0, $user->fresh()->tokens);
    }
}
