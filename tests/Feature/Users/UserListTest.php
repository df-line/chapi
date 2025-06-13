<?php

namespace Tests\Feature\Users;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $this->getJson($this->APIUrl('/users'))->assertStatus(401);
    }

    public function test_authenticated_user_can_list_other_verified_users(): void
    {
        $user = User::factory()->verified()->create();
        Sanctum::actingAs($user);

        $verifiedUser1 = User::factory()->verified()->create(['name' => 'Joska']);
        $verifiedUser2 = User::factory()->verified()->create(['name' => 'Pista']);
        $unverifiedUser = User::factory()->unverified()->create(['name' => 'Mancineni']);

        //Create tokens, since we want to list active (logged in users)
        $verifiedUser1->createToken('auth_token');
        $verifiedUser2->createToken('auth_token');

        $response = $this->getJson($this->APIUrl('/users'));

        $response->assertStatus(200)
            //Joska es Pista
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Joska'])
            ->assertJsonFragment(['name' => 'Pista'])
            //unverified user
            ->assertJsonMissing(['name' => 'Mancineni'])
            //Me missing
            ->assertJsonMissing(['name' => $user->name]);
    }

    public function test_user_list_can_be_filtered_by_name(): void
    {
        $user = User::factory()->verified()->create();
        Sanctum::actingAs($user);

        $u1 = User::factory()->verified()->create(['name' => 'Findable One']);
        $u2 = User::factory()->verified()->create(['name' => 'Findable Two']);
        User::factory()->verified()->create(['name' => 'Another User']);

        $u1->createToken('auth_token');
        $u2->createToken('auth_token');

        $response = $this->getJson($this->APIUrl('/users?name=Findable'));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Findable One'])
            ->assertJsonFragment(['name' => 'Findable Two'])
            ->assertJsonMissing(['name' => 'Another User']);
    }

    public function test_user_list_paging(): void
    {
        $pageSize = 15;
        $genCount = 20;
        $user = User::factory()->verified()->create();
        Sanctum::actingAs($user);

        $users = User::factory()->verified()->count($genCount)->create(); // Több, mint a lapozási limit

        $users->each(function ($user)
        {
            $user->createToken('auth_token');
        });

        $response = $this->getJson($this->APIUrl('/users'));

        $response->assertStatus(200)
            ->assertJsonCount($pageSize, 'data') // Alapértelmezett lapméret
            ->assertJsonStructure(['data', 'links', 'meta']);
    }
}
