<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends ApiController
{
    public function store(User $friend)
    {
        $user = Auth::user();

        if (!$friend->hasVerifiedEmail())
            return $this->jsonErrorResponse('You can only add verified users as friends.', 400);

        if ($user->id === $friend->id)
            return $this->jsonErrorResponse('You cannot add yourself as a friend.', 400);

        if ($user->friends()->where('friend_id', $friend->id)->exists())
            return $this->jsonErrorResponse('This user is already your friend.', 400);

        if (!$friend->loggedIn())
            return $this->jsonErrorResponse('You can only add active (loggedin) users', 400);

        $user->friends()->attach($friend->id);
        //$friend->friends()->attach($user->id);

        return $this->jsonResponse('Friend added successfully.');
    }

    public function show()
    {
        $friends = Auth::user()->friends()->with('friends')->paginate(15);

        return $this->jsonResponse($friends);
    }
}
