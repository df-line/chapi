<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->jsonResponse([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function emailVerification(Request $request)
    {
        $user = \App\Models\User::find($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification())))
            return $this->jsonResponse('Invalid verification link.', 400);

        if ($user->hasVerifiedEmail())
            return $this->jsonResponse('Email already verified.');

        if ($user->markEmailAsVerified())
            event(new \Illuminate\Auth\Events\Verified($user));

        return $this->jsonResponse(['message' => 'Email has been verified.']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($credentials))
            return $this->jsonErrorResponse('Invalid credentials', 401);

        $user = User::where('email', $request->email)->firstOrFail();

        if (!$user->hasVerifiedEmail())
            return $this->jsonErrorResponse('Please verify your email address first.', 403);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->jsonResponse(
        [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->jsonResponse('Successfully logged out');
    }

    public function emailVerificationNotify(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return $this->jsonResponse('Verification link sent!');
    }
}
