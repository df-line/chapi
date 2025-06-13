<?php

namespace Tests\Feature\Friends;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FriendTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $friend;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->verified()->create();
        $this->friend = User::factory()->verified()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_a_user_can_add_another_verified_and_active_user_as_a_friend(): void
    {
        //Only active users can befriend each other
        $this->user->createToken('auth_token');
        $this->friend->createToken('auth_token');

        $response = $this->postJson($this->APIUrl("/friends/{$this->friend->id}"));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Friend added successfully.']);

        $this->assertDatabaseHas('friends', [
            'user_id' => $this->user->id,
            'friend_id' => $this->friend->id,
        ]);

        Sanctum::actingAs($this->friend);

        $response = $this->postJson($this->APIUrl("/friends/{$this->user->id}"));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Friend added successfully.']);

        $this->assertDatabaseHas('friends', [
            'user_id' => $this->friend->id,
            'friend_id' => $this->user->id,
        ]);
    }

    public function test_a_user_cannot_add_themselves_as_a_friend(): void
    {
        $this->postJson($this->APIUrl("/friends/{$this->user->id}"))
            ->assertStatus(400)
            ->assertJson(['error' => 'You cannot add yourself as a friend.']);
    }

    public function test_a_user_cannot_add_an_unverified_user_as_a_friend(): void
    {
        $unverifiedUser = User::factory()->unverified()->create();

        $this->postJson($this->APIUrl("/friends/{$unverifiedUser->id}"))
            ->assertStatus(400)
            ->assertJson(['error' => 'You can only add verified users as friends.']);
    }

    public function test_a_user_cannot_add_an_existing_friend_again(): void
    {
        $this->user->friends()->attach($this->friend->id); // Már ismerősök

        $this->postJson($this->APIUrl("/friends/{$this->friend->id}"))
            ->assertStatus(400)
            ->assertJson(['error' => 'This user is already your friend.']);
    }

    public function test_a_user_can_list_their_friends(): void
    {
        $friend1 = User::factory()->verified()->create();
        $friend2 = User::factory()->verified()->create();
        $notAFriend = User::factory()->verified()->create();

        // Kapcsolatok létrehozása
        $this->user->friends()->attach($friend1->id);
        $this->user->friends()->attach($friend2->id);

        // Kölcsönösség miatt a másik oldal is
        $friend1->friends()->attach($this->user->id);
        $friend2->friends()->attach($this->user->id);

        $response = $this->getJson($this->APIUrl('/friends'));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $friend1->id])
            ->assertJsonFragment(['id' => $friend2->id])
            ->assertJsonMissing(['id' => $notAFriend->id]);
    }
}
