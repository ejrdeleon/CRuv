<?php

namespace Tests\Feature;

use App\Livewire\Admin\StaffManagement;
use App\Models\Department;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_category_management_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ReportCategory::create([
            'name' => 'Fallen Streetlamp',
            'slug' => 'fallen-streetlamp',
            'icon' => 'zap',
            'color' => '#0F766E',
            'default_priority' => 'high',
            'is_hazardous' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(StaffManagement::class)
            ->assertStatus(200)
            ->assertSee('Report Categories')
            ->assertSee('Fallen Streetlamp');
    }

    public function test_admin_can_create_new_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create([
            'name' => 'Sanitation Office',
            'code' => 'SO',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(StaffManagement::class)
            ->call('openNewCategoryModal')
            ->set('catName', 'Blocked Culvert')
            ->set('catSlug', 'blocked-culvert')
            ->set('catDescription', 'Culvert blocked by storm debris')
            ->set('catIcon', 'droplet')
            ->set('catColor', '#0F766E')
            ->set('catDepartmentId', $dept->id)
            ->set('catDefaultPriority', 'high')
            ->set('catIsHazardous', true)
            ->set('catSafetyAdvisory', 'Risk of rapid flash flooding')
            ->set('catIsActive', true)
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('report_categories', [
            'name' => 'Blocked Culvert',
            'slug' => 'blocked-culvert',
            'color' => '#0F766E',
            'is_hazardous' => true,
        ]);
    }

    public function test_admin_can_edit_existing_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ReportCategory::create([
            'name' => 'Old Category Name',
            'slug' => 'old-category',
            'icon' => 'alert-triangle',
            'color' => '#475569',
            'default_priority' => 'low',
            'is_hazardous' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(StaffManagement::class)
            ->call('editCategory', $category->id)
            ->set('catName', 'Updated Category Name')
            ->set('catColor', '#D97706')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('report_categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'color' => '#D97706',
        ]);
    }

    public function test_admin_can_toggle_category_active_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ReportCategory::create([
            'name' => 'Test Toggle Category',
            'slug' => 'test-toggle',
            'icon' => 'alert-triangle',
            'color' => '#0F766E',
            'default_priority' => 'medium',
            'is_hazardous' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(StaffManagement::class)
            ->call('toggleCategoryActive', $category->id);

        $this->assertFalse($category->fresh()->is_active);

        Livewire::test(StaffManagement::class)
            ->call('toggleCategoryActive', $category->id);

        $this->assertTrue($category->fresh()->is_active);
    }

    public function test_cannot_delete_category_with_existing_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ReportCategory::create([
            'name' => 'Active Damaged Pavement',
            'slug' => 'damaged-pavement',
            'icon' => 'construction',
            'color' => '#E11D48',
            'default_priority' => 'urgent',
            'is_hazardous' => true,
            'is_active' => true,
        ]);

        Report::create([
            'reference_number' => 'REP-TEST-0001',
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Test Report',
            'description' => 'Test description',
            'priority' => 'urgent',
            'status' => 'submitted',
            'latitude' => 14.2816,
            'longitude' => 121.4146,
            'address_text' => 'Town Hall',
            'barangay' => 'Poblacion I',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
        ]);

        $this->actingAs($admin);

        Livewire::test(StaffManagement::class)
            ->call('deleteCategory', $category->id);

        $this->assertDatabaseHas('report_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_can_delete_category_with_zero_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ReportCategory::create([
            'name' => 'Unused Test Category',
            'slug' => 'unused-category',
            'icon' => 'alert-triangle',
            'color' => '#0F766E',
            'default_priority' => 'medium',
            'is_hazardous' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(StaffManagement::class)
            ->call('deleteCategory', $category->id);

        $this->assertDatabaseMissing('report_categories', [
            'id' => $category->id,
        ]);
    }
}
