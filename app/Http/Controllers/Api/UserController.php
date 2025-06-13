<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class UserController extends ApiController
{
    public function show(Request $request)
    {
        $query = User::query()
            ->whereNotNull('email_verified_at')
            ->where('id', '!=', Auth::id())
            ->whereHas('tokens', function ($query)
            {
                $query->where(function ($subQuery)
                {
                    $subQuery->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });
            });

        if ( $request->has('name') )
            $query->where('name', 'like', '%' . $request->name . '%');

        return UserResource::collection($query->paginate(15));
    }
}
