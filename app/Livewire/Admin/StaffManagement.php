<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class StaffManagement extends Component
{
    // New administrator modal state
    public bool $showNewAdminModal = false;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    // Department modal state
    public bool $showNewDeptModal = false;

    public string $deptName = '';

    public string $deptCode = '';

    public string $deptDescription = '';

    public string $deptContactPerson = '';

    public string $deptContactPhone = '';

    public string $deptContactEmail = '';

    public function openNewAdminModal()
    {
        $this->reset(['name', 'email', 'phone', 'password']);
        $this->showNewAdminModal = true;
    }

    public function createAdmin()
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'role' => 'admin',
            'barangay' => 'Barangay San Isidro',
            'is_active' => true,
        ]);

        ActivityLog::log('admin_created', "Admin account created: {$user->name} ({$user->email})", $user);

        $this->showNewAdminModal = false;
        session()->flash('success', "Administrator account for {$user->name} created successfully.");
    }

    public function toggleUserStatus(int $userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot deactivate your own administrative account.');

            return;
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        ActivityLog::log('user_status_toggled', "User {$user->name} active status set to ".($user->is_active ? 'Active' : 'Inactive'), $user);
        session()->flash('success', "Account status updated for {$user->name}.");
    }

    public function openNewDeptModal()
    {
        $this->reset(['deptName', 'deptCode', 'deptDescription', 'deptContactPerson', 'deptContactPhone', 'deptContactEmail']);
        $this->showNewDeptModal = true;
    }

    public function createDepartment()
    {
        $this->validate([
            'deptName' => 'required|string|max:150',
            'deptCode' => 'required|string|max:30|unique:departments,code',
            'deptDescription' => 'nullable|string|max:500',
            'deptContactPerson' => 'nullable|string|max:150',
            'deptContactPhone' => 'nullable|string|max:50',
            'deptContactEmail' => 'nullable|email|max:150',
        ]);

        $dept = Department::create([
            'name' => $this->deptName,
            'code' => strtoupper($this->deptCode),
            'description' => $this->deptDescription,
            'contact_person' => $this->deptContactPerson,
            'contact_phone' => $this->deptContactPhone,
            'contact_email' => $this->deptContactEmail,
            'is_active' => true,
        ]);

        ActivityLog::log('department_created', "Created department {$dept->name} ({$dept->code})", $dept);

        $this->showNewDeptModal = false;
        session()->flash('success', "Department {$dept->name} created successfully.");
    }

    public function render()
    {
        $adminUsers = User::where('role', 'admin')
            ->orderBy('name')
            ->get();

        $departments = Department::withCount('reports')
            ->get();

        return view('livewire.admin.staff-management', [
            'adminUsers' => $adminUsers,
            'departments' => $departments,
        ]);
    }
}
