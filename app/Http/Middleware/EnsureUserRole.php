<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated.']);
        }

        // Check if user has one of the allowed roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Not authorized for this role
        abort(403, 'Unauthorized access: You do not have permission to view this section.');
    }
}
