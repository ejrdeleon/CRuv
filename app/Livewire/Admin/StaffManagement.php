<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    // Category modal state
    public bool $showCategoryModal = false;

    public ?int $editingCategoryId = null;

    public string $catName = '';

    public string $catSlug = '';

    public string $catDescription = '';

    public string $catIcon = 'alert-triangle';

    public string $catColor = '#0F766E';

    public ?int $catDepartmentId = null;

    public string $catDefaultPriority = 'medium';

    public bool $catIsHazardous = false;

    public string $catSafetyAdvisory = '';

    public bool $catIsActive = true;

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
            'barangay' => config('community.barangays.0', 'Poblacion I'),
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

        Cache::forget('admin:departments:active');
        ActivityLog::log('department_created', "Created department {$dept->name} ({$dept->code})", $dept);

        $this->showNewDeptModal = false;
        session()->flash('success', "Department {$dept->name} created successfully.");
    }

    public function openNewCategoryModal(): void
    {
        $this->reset([
            'editingCategoryId',
            'catName',
            'catSlug',
            'catDescription',
            'catIcon',
            'catColor',
            'catDepartmentId',
            'catDefaultPriority',
            'catIsHazardous',
            'catSafetyAdvisory',
        ]);
        $this->catIcon = 'alert-triangle';
        $this->catColor = '#0F766E';
        $this->catDefaultPriority = 'medium';
        $this->catIsActive = true;
        $this->showCategoryModal = true;
    }

    public function editCategory(int $id): void
    {
        $category = ReportCategory::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->catName = $category->name;
        $this->catSlug = $category->slug;
        $this->catDescription = $category->description ?? '';
        $this->catIcon = $category->icon ?? 'alert-triangle';
        $this->catColor = $category->color ?? '#0F766E';
        $this->catDepartmentId = $category->department_id;
        $this->catDefaultPriority = $category->default_priority ?? 'medium';
        $this->catIsHazardous = (bool) $category->is_hazardous;
        $this->catSafetyAdvisory = $category->safety_advisory ?? '';
        $this->catIsActive = (bool) $category->is_active;

        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'catName' => 'required|string|max:100',
            'catSlug' => 'nullable|string|max:100',
            'catDescription' => 'nullable|string|max:500',
            'catIcon' => 'required|string|max:50',
            'catColor' => 'required|string|regex:/^#([a-fA-F0-9]{6})$/',
            'catDepartmentId' => 'nullable|exists:departments,id',
            'catDefaultPriority' => 'required|in:low,medium,high,urgent',
            'catIsHazardous' => 'boolean',
            'catSafetyAdvisory' => 'nullable|string|max:500',
            'catIsActive' => 'boolean',
        ]);

        $slug = trim($this->catSlug);
        if ($slug === '') {
            $slug = Str::slug($this->catName);
        }

        if ($this->editingCategoryId) {
            $category = ReportCategory::findOrFail($this->editingCategoryId);
            $category->update([
                'name' => $this->catName,
                'slug' => $slug,
                'description' => $this->catDescription ?: null,
                'icon' => $this->catIcon,
                'color' => $this->catColor,
                'department_id' => $this->catDepartmentId,
                'default_priority' => $this->catDefaultPriority,
                'is_hazardous' => $this->catIsHazardous,
                'safety_advisory' => $this->catSafetyAdvisory ?: null,
                'is_active' => $this->catIsActive,
            ]);

            ActivityLog::log('category_updated', "Updated category {$category->name}", $category);
            session()->flash('success', "Category '{$category->name}' updated successfully.");
        } else {
            $category = ReportCategory::create([
                'name' => $this->catName,
                'slug' => $slug,
                'description' => $this->catDescription ?: null,
                'icon' => $this->catIcon,
                'color' => $this->catColor,
                'department_id' => $this->catDepartmentId,
                'default_priority' => $this->catDefaultPriority,
                'is_hazardous' => $this->catIsHazardous,
                'safety_advisory' => $this->catSafetyAdvisory ?: null,
                'is_active' => $this->catIsActive,
            ]);

            ActivityLog::log('category_created', "Created category {$category->name}", $category);
            session()->flash('success', "Category '{$category->name}' created successfully.");
        }

        $this->showCategoryModal = false;
        Cache::forget('admin:categories:active');
        Cache::forget('api:categories:active');
    }

    public function toggleCategoryActive(int $id): void
    {
        $category = ReportCategory::findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        Cache::forget('admin:categories:active');
        Cache::forget('api:categories:active');
        ActivityLog::log('category_status_toggled', "Category {$category->name} set to ".($category->is_active ? 'Active' : 'Inactive'), $category);
        session()->flash('success', "Category '{$category->name}' is now ".($category->is_active ? 'Active' : 'Deactivated').'.');
    }

    public function deleteCategory(int $id): void
    {
        $category = ReportCategory::withCount('reports')->findOrFail($id);

        if ($category->reports_count > 0) {
            session()->flash('error', "Cannot delete category '{$category->name}' because it has {$category->reports_count} associated report(s). Please deactivate it instead to preserve data integrity.");

            return;
        }

        $categoryName = $category->name;
        $category->delete();

        Cache::forget('admin:categories:active');
        Cache::forget('api:categories:active');
        ActivityLog::log('category_deleted', "Deleted category {$categoryName}");
        session()->flash('success', "Category '{$categoryName}' deleted successfully.");
    }

    public function render()
    {
        $adminUsers = User::where('role', 'admin')
            ->orderBy('name')
            ->get();

        $departments = Department::withCount('reports')
            ->get();

        $categories = ReportCategory::with('department')
            ->withCount('reports')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.staff-management', [
            'adminUsers' => $adminUsers,
            'departments' => $departments,
            'categories' => $categories,
        ]);
    }
}
