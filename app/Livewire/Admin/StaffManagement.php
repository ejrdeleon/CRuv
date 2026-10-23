<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Personnel;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class StaffManagement extends Component
{
    // Active tab: 'units_personnel', 'categories', 'admins'
    public string $activeTab = 'units_personnel';

    // New administrator modal state
    public bool $showNewAdminModal = false;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    // Unit / Department modal state
    public bool $showDeptModal = false;

    public ?int $editingDeptId = null;

    public string $deptName = '';

    public string $deptCode = '';

    public string $deptDescription = '';

    public string $deptContactPerson = '';

    public string $deptContactPhone = '';

    public string $deptContactEmail = '';

    public bool $deptIsActive = true;

    // Personnel modal state
    public bool $showPersonnelModal = false;

    public ?int $editingPersonnelId = null;

    public ?int $personnelDeptId = null;

    public string $personnelName = '';

    public string $personnelPosition = '';

    public string $personnelContactNumber = '';

    public string $personnelEmail = '';

    public bool $personnelIsActive = true;

    public ?int $personnelFilterDeptId = null;

    public string $personnelSearch = '';

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

    public function boot(): void
    {
        abort_if(! Auth::check() || Auth::user()->role !== 'admin', 403, 'Unauthorized access: Administrator role required.');
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // --- Administrator Management ---
    public function openNewAdminModal(): void
    {
        $this->reset(['name', 'email', 'phone', 'password']);
        $this->showNewAdminModal = true;
    }

    public function createAdmin(): void
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

    public function toggleUserStatus(int $userId): void
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

    // --- Unit / Department Management ---
    public function openNewDeptModal(): void
    {
        $this->reset(['editingDeptId', 'deptName', 'deptCode', 'deptDescription', 'deptContactPerson', 'deptContactPhone', 'deptContactEmail']);
        $this->deptIsActive = true;
        $this->showDeptModal = true;
    }

    public function editDepartment(int $id): void
    {
        $dept = Department::findOrFail($id);
        $this->editingDeptId = $dept->id;
        $this->deptName = $dept->name;
        $this->deptCode = $dept->code;
        $this->deptDescription = $dept->description ?? '';
        $this->deptContactPerson = $dept->contact_person ?? '';
        $this->deptContactPhone = $dept->contact_phone ?? '';
        $this->deptContactEmail = $dept->contact_email ?? '';
        $this->deptIsActive = (bool) $dept->is_active;
        $this->showDeptModal = true;
    }

    public function saveDepartment(): void
    {
        $this->validate([
            'deptName' => 'required|string|max:150',
            'deptCode' => ['required', 'string', 'max:30', Rule::unique('departments', 'code')->ignore($this->editingDeptId)],
            'deptDescription' => 'nullable|string|max:500',
            'deptContactPerson' => 'nullable|string|max:150',
            'deptContactPhone' => 'nullable|string|max:50',
            'deptContactEmail' => 'nullable|email|max:150',
            'deptIsActive' => 'boolean',
        ]);

        $code = strtoupper(trim($this->deptCode));

        if ($this->editingDeptId) {
            $dept = Department::findOrFail($this->editingDeptId);
            $dept->update([
                'name' => $this->deptName,
                'code' => $code,
                'description' => $this->deptDescription ?: null,
                'contact_person' => $this->deptContactPerson ?: null,
                'contact_phone' => $this->deptContactPhone ?: null,
                'contact_email' => $this->deptContactEmail ?: null,
                'is_active' => $this->deptIsActive,
            ]);

            ActivityLog::log('department_updated', "Updated unit {$dept->name} ({$dept->code})", $dept);
            session()->flash('success', "Unit '{$dept->name}' updated successfully.");
        } else {
            $dept = Department::create([
                'name' => $this->deptName,
                'code' => $code,
                'description' => $this->deptDescription ?: null,
                'contact_person' => $this->deptContactPerson ?: null,
                'contact_phone' => $this->deptContactPhone ?: null,
                'contact_email' => $this->deptContactEmail ?: null,
                'is_active' => $this->deptIsActive,
            ]);

            ActivityLog::log('department_created', "Created unit {$dept->name} ({$dept->code})", $dept);
            session()->flash('success', "Unit '{$dept->name}' created successfully.");
        }

        Cache::forget('admin:departments:active');
        $this->showDeptModal = false;
    }

    public function toggleDepartmentStatus(int $id): void
    {
        $dept = Department::findOrFail($id);
        $dept->is_active = ! $dept->is_active;
        $dept->save();

        Cache::forget('admin:departments:active');
        ActivityLog::log('department_status_toggled', "Unit {$dept->name} active status set to ".($dept->is_active ? 'Active' : 'Inactive'), $dept);
        session()->flash('success', "Unit '{$dept->name}' is now ".($dept->is_active ? 'Active' : 'Inactive').'.');
    }

    public function deleteDepartment(int $id): void
    {
        $dept = Department::withCount(['reports', 'personnel', 'categories'])->findOrFail($id);

        if ($dept->reports_count > 0 || $dept->personnel_count > 0 || $dept->categories_count > 0) {
            $reasons = [];
            if ($dept->reports_count > 0) {
                $reasons[] = "{$dept->reports_count} report(s)";
            }
            if ($dept->personnel_count > 0) {
                $reasons[] = "{$dept->personnel_count} personnel";
            }
            if ($dept->categories_count > 0) {
                $reasons[] = "{$dept->categories_count} category/categories";
            }
            $reasonStr = implode(', ', $reasons);

            session()->flash('error', "Cannot delete unit '{$dept->name}' because it is referenced by {$reasonStr}. Please deactivate the unit instead to preserve history.");

            return;
        }

        $deptName = $dept->name;
        $dept->delete();

        Cache::forget('admin:departments:active');
        ActivityLog::log('department_deleted', "Deleted unit {$deptName}");
        session()->flash('success', "Unit '{$deptName}' deleted successfully.");
    }

    // --- Personnel Management ---
    public function openNewPersonnelModal(?int $departmentId = null): void
    {
        $this->reset(['editingPersonnelId', 'personnelName', 'personnelPosition', 'personnelContactNumber', 'personnelEmail']);
        $this->personnelDeptId = $departmentId ?: ($this->personnelFilterDeptId ?: Department::where('is_active', true)->value('id'));
        $this->personnelIsActive = true;
        $this->showPersonnelModal = true;
    }

    public function editPersonnel(int $id): void
    {
        $person = Personnel::findOrFail($id);
        $this->editingPersonnelId = $person->id;
        $this->personnelDeptId = $person->department_id;
        $this->personnelName = $person->name;
        $this->personnelPosition = $person->position;
        $this->personnelContactNumber = $person->contact_number;
        $this->personnelEmail = $person->email ?? '';
        $this->personnelIsActive = (bool) $person->is_active;
        $this->showPersonnelModal = true;
    }

    public function savePersonnel(): void
    {
        $this->validate([
            'personnelDeptId' => 'required|exists:departments,id',
            'personnelName' => 'required|string|max:150',
            'personnelPosition' => 'required|string|max:150',
            'personnelContactNumber' => 'required|string|max:50',
            'personnelEmail' => 'nullable|email|max:150',
            'personnelIsActive' => 'boolean',
        ]);

        if ($this->editingPersonnelId) {
            $person = Personnel::findOrFail($this->editingPersonnelId);
            $person->update([
                'department_id' => $this->personnelDeptId,
                'name' => $this->personnelName,
                'position' => $this->personnelPosition,
                'contact_number' => $this->personnelContactNumber,
                'email' => $this->personnelEmail ?: null,
                'is_active' => $this->personnelIsActive,
            ]);

            ActivityLog::log('personnel_updated', "Updated personnel {$person->name} ({$person->position})", $person);
            session()->flash('success', "Personnel '{$person->name}' updated successfully.");
        } else {
            $person = Personnel::create([
                'department_id' => $this->personnelDeptId,
                'name' => $this->personnelName,
                'position' => $this->personnelPosition,
                'contact_number' => $this->personnelContactNumber,
                'email' => $this->personnelEmail ?: null,
                'is_active' => $this->personnelIsActive,
            ]);

            ActivityLog::log('personnel_created', "Created personnel {$person->name} for unit {$person->department?->name}", $person);
            session()->flash('success', "Personnel '{$person->name}' created successfully.");
        }

        $this->showPersonnelModal = false;
    }

    public function togglePersonnelStatus(int $id): void
    {
        $person = Personnel::findOrFail($id);
        $person->is_active = ! $person->is_active;
        $person->save();

        ActivityLog::log('personnel_status_toggled', "Personnel {$person->name} status set to ".($person->is_active ? 'Active' : 'Inactive'), $person);
        session()->flash('success', "Personnel '{$person->name}' is now ".($person->is_active ? 'Active' : 'Inactive').'.');
    }

    public function deletePersonnel(int $id): void
    {
        $person = Personnel::withCount(['reports', 'assignments'])->findOrFail($id);

        if ($person->reports_count > 0 || $person->assignments_count > 0) {
            session()->flash('error', "Cannot delete personnel '{$person->name}' because they have associated report assignment history. Please deactivate them instead.");

            return;
        }

        $name = $person->name;
        $person->delete();

        ActivityLog::log('personnel_deleted', "Deleted personnel {$name}");
        session()->flash('success', "Personnel '{$name}' deleted successfully.");
    }

    // --- Category Management ---
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
        session()->flash('success', "Category '{$category->name}' is now ".($category->is_active ? 'Active' : 'Inactive').'.');
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

        $departments = Department::withCount(['reports', 'personnel'])
            ->orderBy('name')
            ->get();

        $personnelQuery = Personnel::with(['department'])
            ->withCount('reports');

        if ($this->personnelFilterDeptId) {
            $personnelQuery->where('department_id', $this->personnelFilterDeptId);
        }

        if (! empty($this->personnelSearch)) {
            $search = '%'.$this->personnelSearch.'%';
            $personnelQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('position', 'like', $search)
                    ->orWhere('contact_number', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        $personnelList = $personnelQuery->orderBy('name')->get();

        $categories = ReportCategory::with('department')
            ->withCount('reports')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.staff-management', [
            'adminUsers' => $adminUsers,
            'departments' => $departments,
            'personnelList' => $personnelList,
            'categories' => $categories,
        ]);
    }
}
