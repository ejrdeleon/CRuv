<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated.']);
        }

        // Check if user has one of the allowed roles
        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // If a resident session attempts to access an admin/staff web route, log out the web session cleanly
        if ($user->role === 'resident' && ! $request->expectsJson()) {
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Residents use the mobile app.',
            ]);
        }

        // Not authorized for this role
        abort(403, 'Unauthorized access: You do not have permission to view this section.');
    }
}
