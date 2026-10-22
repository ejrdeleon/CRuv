<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Admin\Analytics;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\InteractiveMap;
use App\Livewire\Admin\ReportDetail as AdminReportDetail;
use App\Livewire\Admin\ReportManagement;
use App\Livewire\Admin\StaffManagement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Admin & Staff Portal Only)
|--------------------------------------------------------------------------
| Residents use the Flutter mobile application via /api/v1 endpoints.
*/

// Root URL redirects to the admin login (or admin dashboard if already signed in as admin/staff)
Route::get('/', function () {
    if (Auth::check() && in_array(Auth::user()->role, ['admin', 'staff'], true)) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
});

// Guest Authentication Routes (Admin & Staff Login Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Admin & Staff Routes
Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/map', InteractiveMap::class)->name('map');
        Route::get('/reports', ReportManagement::class)->name('reports');
        Route::get('/reports/{report}', AdminReportDetail::class)->name('reports.show');
        Route::get('/departments', StaffManagement::class)->name('staff');
        Route::get('/analytics', Analytics::class)->name('analytics');
    });
});
