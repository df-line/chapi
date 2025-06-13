<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends ApiController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'body' => 'required|string',
        ]);

        $sender = Auth::user();
        $recipientId = $validated['recipient_id'];

        if (!$sender->friends()->where('friend_id', $recipientId)->exists())
            return $this->jsonErrorResponse('You can only send messages to your friends.', 403);

        $message = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipientId,
            'body' => $validated['body'],
        ]);

        return $this->jsonResponse($message, 201);
    }

    public function show(User $user)
    {
        $authUserId = Auth::id();
        $friendId = $user->id;

        //Shows sent and received messages
        $messages = Message::where(function ($query) use ($authUserId, $friendId) {
                $query->where('sender_id', $authUserId)
                      ->where('recipient_id', $friendId);
            })->orWhere(function ($query) use ($authUserId, $friendId) {
                $query->where('sender_id', $friendId)
                      ->where('recipient_id', $authUserId);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return $this->jsonResponse($messages);
    }
}
