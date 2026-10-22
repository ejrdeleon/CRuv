<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $totalReports = $user->reports()->count();
        $activeReports = $user->reports()
            ->whereIn('status', ['submitted', 'under_review', 'verified', 'assigned', 'in_progress'])
            ->count();
        $resolvedReports = $user->reports()->where('status', 'resolved')->count();
        $unreadNotifications = $user->notifications()->where('is_read', false)->count();

        return response()->json([
            'data' => new UserResource($user),
            'stats' => [
                'total' => $totalReports,
                'active' => $activeReports,
                'resolved' => $resolvedReports,
                'unread_notifications' => $unreadNotifications,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];

        if (array_key_exists('barangay', $validated) && $validated['barangay'] !== null) {
            $user->barangay = $validated['barangay'];
        }

        if (array_key_exists('address', $validated)) {
            $user->address = $validated['address'];
        }

        if (! empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        ActivityLog::log(
            'api_profile_updated',
            "User {$user->name} updated their profile via mobile API.",
            $user,
            null,
            $user
        );

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($user),
        ]);
    }
}
