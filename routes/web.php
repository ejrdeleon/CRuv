<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Admin\Analytics;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\InteractiveMap;
use App\Livewire\Admin\ReportDetail as AdminReportDetail;
use App\Livewire\Admin\ReportManagement;
use App\Livewire\Admin\StaffManagement;
use App\Livewire\Resident\Home as ResidentHome;
use App\Livewire\Resident\NotificationCenter;
use App\Livewire\Resident\ReportDetail as ResidentReportDetail;
use App\Livewire\Resident\ReportList;
use App\Livewire\Resident\SubmitReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirection based on user authentication and role
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('resident.home');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Resident Interface Routes
    Route::prefix('app')->name('resident.')->group(function () {
        Route::get('/home', ResidentHome::class)->name('home');
        Route::get('/report', SubmitReport::class)->name('reports.create');
        Route::get('/my-reports', ReportList::class)->name('reports.index');
        Route::get('/my-reports/{report}', ResidentReportDetail::class)->name('reports.show');
        Route::get('/notifications', NotificationCenter::class)->name('notifications');
    });

    // Administrator Routes (Strictly role:admin)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/map', InteractiveMap::class)->name('map');
        Route::get('/reports', ReportManagement::class)->name('reports');
        Route::get('/reports/{report}', AdminReportDetail::class)->name('reports.show');
        Route::get('/departments', StaffManagement::class)->name('staff');
        Route::get('/analytics', Analytics::class)->name('analytics');
    });
});
