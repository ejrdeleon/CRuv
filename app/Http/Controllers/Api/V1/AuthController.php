<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'barangay' => $validated['barangay'],
            'address' => $validated['address'] ?? null,
            'role' => 'resident',
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::log(
            'api_user_registered',
            "New resident registered via mobile app: {$user->name} ({$user->email})",
            $user,
            null,
            $user
        );

        $deviceName = $validated['device_name'] ?? 'flutter-mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Account registered successfully.',
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $throttleKey = 'api-login:'.strtolower($validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                'errors' => [
                    'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
                ],
            ], 429);
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'message' => 'The provided credentials do not match our records.',
                'errors' => [
                    'email' => ['The provided credentials do not match our records.'],
                ],
            ], 422);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'This account has been deactivated. Please contact the barangay administrator.',
                'errors' => [
                    'email' => ['This account has been deactivated.'],
                ],
            ], 403);
        }

        RateLimiter::clear($throttleKey);

        ActivityLog::log(
            'api_user_login',
            "User {$user->name} ({$user->role}) signed in via mobile API.",
            $user,
            null,
            $user
        );

        $deviceName = $validated['device_name'] ?? 'flutter-mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Signed in successfully.',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Signed out successfully.',
        ]);
    }
}
