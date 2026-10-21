<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'login-attempt:'.strtolower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account has been deactivated. Please contact the barangay administrator.',
                ]);
            }

            ActivityLog::log('user_login', "User {$user->name} ({$user->role}) logged in.", $user, null, $user);

            return $this->redirectBasedOnRole($user);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'barangay' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'barangay' => $validated['barangay'],
            'address' => $validated['address'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'resident',
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLog::log('resident_register', "New resident account registered: {$user->name}", $user, null, $user);

        return redirect()->route('resident.home')->with('success', 'Account registered successfully! Welcome to the Community Reporting System.');
    }

    public function logout(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user) {
            ActivityLog::log('user_logout', "User {$user->name} logged out.", $user, null, $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    public function showProfile(): View
    {
        return view('profile.show', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'max:30'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        if (isset($validated['barangay'])) {
            $user->barangay = $validated['barangay'];
        }
        if (isset($validated['address'])) {
            $user->address = $validated['address'];
        }

        if (! empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        ActivityLog::log('profile_updated', "User {$user->name} updated their profile info.", $user, null, $user);

        return back()->with('success', 'Profile updated successfully.');
    }

    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('resident.home');
    }
}
