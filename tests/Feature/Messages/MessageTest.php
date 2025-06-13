<?php

namespace Tests\Feature\Messages;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Message;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $friend;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->verified()->create();
        $this->friend = User::factory()->verified()->create();

        // Legyenek barátok - de nagyon
        $this->user->friends()->attach($this->friend->id);
        $this->friend->friends()->attach($this->user->id);

        Sanctum::actingAs($this->user);
    }

    public function test_a_user_can_send_a_message_to_a_friend(): void
    {
        $messageBody = 'Hello!';
        $response = $this->postJson($this->APIUrl('/message'), [
            'recipient_id' => $this->friend->id,
            'body' => $messageBody,
        ]);

        $response->assertStatus(201); // Created
        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->user->id,
            'recipient_id' => $this->friend->id,
            'body' => $messageBody,
        ]);
    }

    public function test_a_user_cannot_send_a_message_to_a_non_friend(): void
    {
        $nonFriend = User::factory()->verified()->create();

        $response = $this->postJson($this->APIUrl('/message'),
        [
            'recipient_id' => $nonFriend->id,
            'body' => 'Hellobello!',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('messages', ['body' => 'Hellobello!']);
    }

    public function test_a_user_can_view_the_conversation_with_a_friend(): void
    {
        Message::factory()->create(['sender_id' => $this->user->id, 'recipient_id' => $this->friend->id, 'body' => 'Message 1 from user']);
        Message::factory()->create(['sender_id' => $this->friend->id, 'recipient_id' => $this->user->id, 'body' => 'Message 2 from friend']);
        Message::factory()->create(['sender_id' => $this->user->id, 'recipient_id' => $this->friend->id, 'body' => 'Message 3 from user']);

        $anotherUser = User::factory()->verified()->create();
        Message::factory()->create(['sender_id' => $this->user->id, 'recipient_id' => $anotherUser->id, 'body' => 'Irrelevant message']);

        $response = $this->getJson($this->APIUrl("/messages/{$this->friend->id}"));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment(['body' => 'Message 1 from user'])
            ->assertJsonFragment(['body' => 'Message 2 from friend'])
            ->assertJsonFragment(['body' => 'Message 3 from user'])
            ->assertJsonMissing(['body' => 'Irrelevant message']);
    }
}
