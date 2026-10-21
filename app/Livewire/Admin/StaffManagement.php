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
    // New staff modal state
    public bool $showNewStaffModal = false;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public ?int $department_id = null;

    public string $role = 'staff';

    // Department modal state
    public bool $showNewDeptModal = false;

    public string $deptName = '';

    public string $deptCode = '';

    public string $deptDescription = '';

    public string $deptContactPerson = '';

    public string $deptContactPhone = '';

    public string $deptContactEmail = '';

    public function openNewStaffModal()
    {
        $this->reset(['name', 'email', 'phone', 'password', 'department_id', 'role']);
        $this->role = 'staff';
        $this->showNewStaffModal = true;
    }

    public function createStaff()
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:8',
            'department_id' => 'nullable|exists:departments,id',
            'role' => 'required|in:staff,admin',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'department_id' => $this->department_id,
            'role' => $this->role,
            'barangay' => 'Barangay San Isidro',
            'is_active' => true,
        ]);

        ActivityLog::log('staff_created', "Admin created {$this->role} user: {$user->name} ({$user->email})", $user);

        $this->showNewStaffModal = false;
        session()->flash('success', "Account for {$user->name} created successfully.");
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
        $staffMembers = User::with('department')
            ->withCount(['assignedReports as active_cases_count' => function ($q) {
                $q->whereNotIn('status', ['resolved', 'rejected', 'duplicate']);
            }])
            ->withCount(['assignedReports as completed_cases_count' => function ($q) {
                $q->where('status', 'resolved');
            }])
            ->whereIn('role', ['staff', 'admin'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $departments = Department::withCount('reports')
            ->withCount('staff')
            ->get();

        return view('livewire.admin.staff-management', [
            'staffMembers' => $staffMembers,
            'departments' => $departments,
        ]);
    }
}
