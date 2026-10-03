<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Keval Patel',
            'email' => 'keval_' . uniqid() . '@example.com',
            'mobile' => '98765' . rand(10000, 99999),
            'password' => bcrypt('admin123'),
            'role_title' => 'Super Administrator',
            'theme' => 'charcoal',
        ], $attributes));
    }

    public function test_user_can_upload_profile_avatar(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();

        $file = UploadedFile::fake()->create('profile.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Keval Patel Updated',
            'email' => $user->email,
            'role_title' => 'Super Administrator',
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertStringContainsString('storage/avatars/', $user->avatar_url);
    }

    public function test_user_can_remove_avatar(): void
    {
        Storage::fake('public');
        $user = $this->makeUser(['avatar' => 'avatars/dummy.jpg']);
        Storage::disk('public')->put('avatars/dummy.jpg', 'dummy content');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'role_title' => $user->role_title,
            'remove_avatar' => '1',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertNull($user->avatar);
        Storage::disk('public')->assertMissing('avatars/dummy.jpg');
    }

    public function test_user_can_upload_cropped_base64_avatar(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();

        $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Keval Patel Cropped',
            'email' => $user->email,
            'role_title' => 'Super Administrator',
            'avatar_cropped' => $base64Image,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertStringContainsString('storage/avatars/', $user->avatar_url);
    }

    public function test_staff_avatar_update_syncs_to_employee_and_is_visible_to_admin(): void
    {
        Storage::fake('public');

        $staffUser = $this->makeUser([
            'name' => 'Vikram Singh',
            'email' => 'staff@uesthrms.com',
            'role_title' => 'Employee',
        ]);

        $employee = \App\Models\Employee::create([
            'employee_code' => 'EMP-099',
            'first_name' => 'Vikram',
            'last_name' => 'Singh',
            'email' => 'vikram.singh@uesthrms.com',
            'designation' => 'Staff Officer',
            'joining_date' => now(),
            'salary' => 45000,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('vikram_avatar.jpg', 100, 'image/jpeg');

        $this->actingAs($staffUser)->put(route('profile.update'), [
            'name' => 'Vikram Singh',
            'email' => 'staff@uesthrms.com',
            'role_title' => 'Employee',
            'avatar' => $file,
        ]);

        $staffUser->refresh();
        $employee->refresh();

        $this->assertNotNull($staffUser->avatar);
        $this->assertEquals($staffUser->avatar, $employee->avatar);
        $this->assertNotNull($employee->avatar_url);

        // Super Admin views employees index and sees the updated avatar
        $adminUser = $this->makeUser(['role_title' => 'Super Administrator']);
        $adminResponse = $this->actingAs($adminUser)->get(route('employees.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee($employee->avatar_url);
    }

    public function test_admin_can_update_employee_avatar_and_syncs_to_user(): void
    {
        Storage::fake('public');

        $adminUser = $this->makeUser(['role_title' => 'Super Administrator']);

        $staffUser = $this->makeUser([
            'name' => 'Vikram Singh',
            'email' => 'staff@uesthrms.com',
            'role_title' => 'Employee',
        ]);

        $employee = \App\Models\Employee::create([
            'employee_code' => 'EMP-088',
            'first_name' => 'Vikram',
            'last_name' => 'Singh',
            'email' => 'vikram.singh@uesthrms.com',
            'designation' => 'Staff Officer',
            'joining_date' => now(),
            'salary' => 45000,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('admin_upload_avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($adminUser)->put(route('employees.update', $employee->id), [
            'employee_code' => 'EMP-088',
            'first_name' => 'Vikram',
            'last_name' => 'Singh',
            'email' => 'vikram.singh@uesthrms.com',
            'designation' => 'Staff Officer',
            'joining_date' => '2026-01-01',
            'salary' => 45000,
            'status' => 'active',
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('employees.index'));
        $employee->refresh();
        $staffUser->refresh();

        $this->assertNotNull($employee->avatar);
        $this->assertEquals($employee->avatar, $staffUser->avatar);
    }

    public function test_superadmin_avatar_update_syncs_to_lead_employee_and_others_see_it(): void
    {
        Storage::fake('public');

        $superAdmin = $this->makeUser([
            'name' => 'Keval Dhandhukiya',
            'email' => 'keval192837@gmail.com',
            'role_title' => 'Super Administrator',
        ]);

        $leadEmployee = \App\Models\Employee::create([
            'employee_code' => 'EMP-001',
            'first_name' => 'Keval',
            'last_name' => 'Dhandhukya',
            'email' => 'keval@uesthrms.com',
            'designation' => 'Lead Full-Stack Engineer',
            'joining_date' => now(),
            'salary' => 85000,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('superadmin_avatar.jpg', 120, 'image/jpeg');

        // Super Admin updates his profile avatar and phone
        $this->actingAs($superAdmin)->put(route('profile.update'), [
            'name' => 'Keval Dhandhukiya',
            'email' => 'keval192837@gmail.com',
            'phone' => '+91 99999 88888',
            'role_title' => 'Super Administrator',
            'avatar' => $file,
        ]);

        $superAdmin->refresh();
        $leadEmployee->refresh();

        $this->assertNotNull($superAdmin->avatar);
        $this->assertEquals($superAdmin->avatar, $leadEmployee->avatar);
        $this->assertEquals('+91 99999 88888', $leadEmployee->phone);
        $this->assertNotNull($leadEmployee->avatar_url);

        // HR / Colleague user logs in and can see Super Admin's updated profile avatar on employees directory
        $hrRole = \App\Models\Role::create([
            'name' => 'HR Manager',
            'slug' => 'hr-manager',
            'description' => 'HR Manager',
            'is_system' => true,
        ]);
        $viewPerm = \App\Models\Permission::create([
            'name' => 'View Employees',
            'slug' => 'employees.view',
            'module' => 'Employees',
        ]);
        $hrRole->permissions()->attach($viewPerm->id);

        $hrUser = $this->makeUser([
            'name' => 'Priya Patel',
            'email' => 'hr@uesthrms.com',
            'role_title' => 'HR Manager',
            'role_id' => $hrRole->id,
        ]);

        $hrResponse = $this->actingAs($hrUser)->get(route('employees.index'));
        $hrResponse->assertOk();
        $hrResponse->assertSee($leadEmployee->avatar_url);
    }

    public function test_all_details_sync_real_time_between_user_and_employee(): void
    {
        Storage::fake('public');

        $user = $this->makeUser([
            'name' => 'Vikram Original',
            'email' => 'staff@uesthrms.com',
            'phone' => '+91 11111 22222',
            'role_title' => 'Employee',
        ]);

        $employee = \App\Models\Employee::create([
            'employee_code' => 'EMP-077',
            'first_name' => 'Vikram',
            'last_name' => 'Original',
            'email' => 'vikram.singh@uesthrms.com',
            'phone' => '+91 11111 22222',
            'designation' => 'Staff Officer',
            'joining_date' => now(),
            'salary' => 45000,
            'status' => 'active',
        ]);

        // 1. User updates profile details
        $avatarFile = UploadedFile::fake()->create('vikram_new.jpg', 100, 'image/jpeg');
        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Vikram Updated',
            'email' => 'staff@uesthrms.com',
            'phone' => '+91 99999 11111',
            'role_title' => 'Employee',
            'avatar' => $avatarFile,
        ]);

        $employee->refresh();
        $user->refresh();

        $this->assertEquals('Vikram', $employee->first_name);
        $this->assertEquals('Updated', $employee->last_name);
        $this->assertEquals('+91 99999 11111', $employee->phone);
        $this->assertNotNull($employee->avatar);
        $this->assertEquals($user->avatar, $employee->avatar);

        // 2. Admin updates employee details via employees.update
        $admin = $this->makeUser(['role_title' => 'Super Administrator']);
        $newAvatar = UploadedFile::fake()->create('admin_edited.jpg', 100, 'image/jpeg');

        $this->actingAs($admin)->put(route('employees.update', $employee->id), [
            'employee_code' => 'EMP-077',
            'first_name' => 'Vikram',
            'last_name' => 'AdminEdited',
            'email' => 'vikram.singh@uesthrms.com',
            'phone' => '+91 77777 88888',
            'designation' => 'Staff Officer',
            'joining_date' => '2026-01-01',
            'salary' => 45000,
            'status' => 'active',
            'avatar' => $newAvatar,
        ]);

        $user->refresh();
        $employee->refresh();

        $this->assertEquals('Vikram AdminEdited', $user->name);
        $this->assertEquals('+91 77777 88888', $user->phone);
        $this->assertEquals($employee->avatar, $user->avatar);
    }
}


