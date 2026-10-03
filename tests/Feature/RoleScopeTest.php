<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_sees_only_own_attendance_and_cannot_mark_others(): void
    {
        $staffRole = Role::create(['name' => 'Staff / Employee', 'slug' => 'employee', 'is_system' => false]);
        $permAttView = Permission::create(['name' => 'Attendance View', 'slug' => 'attendance.view', 'module' => 'Attendance']);
        $staffRole->permissions()->sync([$permAttView->id]);

        $staffEmp = Employee::create([
            'employee_code' => 'EMP-101',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'designation' => 'Developer',
            'joining_date' => now()->toDateString(),
            'salary' => 50000,
            'status' => 'active',
            'role_id' => $staffRole->id,
        ]);

        $otherEmp = Employee::create([
            'employee_code' => 'EMP-102',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'designation' => 'Analyst',
            'joining_date' => now()->toDateString(),
            'salary' => 55000,
            'status' => 'active',
            'role_id' => $staffRole->id,
        ]);

        $staffUser = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '9111111111',
            'password' => bcrypt('password'),
            'role_title' => 'Staff / Employee',
            'role_id' => $staffRole->id,
        ]);

        // Staff visits attendance index
        $response = $this->actingAs($staffUser)->get(route('attendance.index'));
        $response->assertOk();
        $employees = $response->viewData('employees');
        $this->assertEquals(1, $employees->count());
        $this->assertEquals($staffEmp->id, $employees->first()->id);

        // Staff tries to mark attendance for another employee -> should be rejected
        $markOther = $this->actingAs($staffUser)->post(route('attendance.mark'), [
            'employee_id' => $otherEmp->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);
        $markOther->assertSessionHas('error');
    }

    public function test_staff_sees_only_own_payroll_and_cannot_generate_payroll(): void
    {
        $staffRole = Role::create(['name' => 'Staff / Employee', 'slug' => 'employee', 'is_system' => false]);
        $permPayslip = Permission::create(['name' => 'Payroll Payslip', 'slug' => 'payroll.payslip', 'module' => 'Payroll']);
        $staffRole->permissions()->sync([$permPayslip->id]);

        $staffEmp = Employee::create([
            'employee_code' => 'EMP-201',
            'first_name' => 'Sam',
            'last_name' => 'Staff',
            'email' => 'sam@example.com',
            'designation' => 'Support',
            'joining_date' => now()->toDateString(),
            'salary' => 35000,
            'status' => 'active',
            'role_id' => $staffRole->id,
        ]);

        $otherEmp = Employee::create([
            'employee_code' => 'EMP-202',
            'first_name' => 'Mary',
            'last_name' => 'Manager',
            'email' => 'mary@example.com',
            'designation' => 'Lead',
            'joining_date' => now()->toDateString(),
            'salary' => 75000,
            'status' => 'active',
            'role_id' => $staffRole->id,
        ]);

        $currentMonth = now()->format('Y-m');
        $staffPayroll = Payroll::create([
            'employee_id' => $staffEmp->id,
            'month' => $currentMonth,
            'basic_salary' => 35000,
            'allowances' => 7000,
            'deductions' => 3500,
            'net_salary' => 38500,
            'status' => 'pending',
        ]);

        $otherPayroll = Payroll::create([
            'employee_id' => $otherEmp->id,
            'month' => $currentMonth,
            'basic_salary' => 75000,
            'allowances' => 15000,
            'deductions' => 7500,
            'net_salary' => 82500,
            'status' => 'pending',
        ]);

        $staffUser = User::create([
            'name' => 'Sam Staff',
            'email' => 'sam@example.com',
            'mobile' => '9222222222',
            'password' => bcrypt('password'),
            'role_title' => 'Staff / Employee',
            'role_id' => $staffRole->id,
        ]);

        // Staff sees only their own payroll record in index
        $response = $this->actingAs($staffUser)->get(route('payroll.index'));
        $response->assertOk();
        $payrolls = $response->viewData('payrolls');
        $this->assertEquals(1, $payrolls->total());
        $this->assertEquals($staffPayroll->id, $payrolls->first()->id);

        // Staff cannot generate payroll
        $genResponse = $this->actingAs($staffUser)->post(route('payroll.generate'), [
            'month' => $currentMonth,
        ]);
        $genResponse->assertSessionHas('error');

        // Staff cannot disburse/mark paid
        $payResponse = $this->actingAs($staffUser)->post(route('payroll.pay', $staffPayroll->id));
        $payResponse->assertSessionHas('error');

        // Staff cannot view other person's payslip
        $slipResponse = $this->actingAs($staffUser)->get(route('payroll.payslip', $otherPayroll->id));
        $slipResponse->assertStatus(403);

        // Staff CAN view their own payslip
        $ownSlipResponse = $this->actingAs($staffUser)->get(route('payroll.payslip', $staffPayroll->id));
        $ownSlipResponse->assertOk();
    }

    public function test_department_manager_sees_only_own_department_and_cannot_approve_other_departments(): void
    {
        $dept1 = \App\Models\Department::create(['name' => 'Finance', 'code' => 'FIN', 'status' => 'active']);
        $dept2 = \App\Models\Department::create(['name' => 'Sales', 'code' => 'SLS', 'status' => 'active']);

        $deptManagerRole = Role::create(['name' => 'Department Manager', 'slug' => 'department-manager', 'is_system' => false]);
        $permEmpView = Permission::create(['name' => 'Employees View', 'slug' => 'employees.view', 'module' => 'Employees']);
        $permLeaveView = Permission::create(['name' => 'Leaves View', 'slug' => 'leaves.view', 'module' => 'Leaves']);
        $permLeaveApprove = Permission::create(['name' => 'Leaves Approve', 'slug' => 'leaves.approve', 'module' => 'Leaves']);
        $deptManagerRole->permissions()->sync([$permEmpView->id, $permLeaveView->id, $permLeaveApprove->id]);

        $mgrEmp = Employee::create([
            'employee_code' => 'EMP-301',
            'first_name' => 'Finance',
            'last_name' => 'Lead',
            'email' => 'fin.mgr@example.com',
            'designation' => 'Lead',
            'department_id' => $dept1->id,
            'joining_date' => now()->toDateString(),
            'salary' => 80000,
            'status' => 'active',
            'role_id' => $deptManagerRole->id,
        ]);

        $dept1Staff = Employee::create([
            'employee_code' => 'EMP-302',
            'first_name' => 'Finance',
            'last_name' => 'Staff',
            'email' => 'fin.staff@example.com',
            'designation' => 'Accountant',
            'department_id' => $dept1->id,
            'joining_date' => now()->toDateString(),
            'salary' => 45000,
            'status' => 'active',
        ]);

        $dept2Staff = Employee::create([
            'employee_code' => 'EMP-303',
            'first_name' => 'Sales',
            'last_name' => 'Staff',
            'email' => 'sales.staff@example.com',
            'designation' => 'Executive',
            'department_id' => $dept2->id,
            'joining_date' => now()->toDateString(),
            'salary' => 40000,
            'status' => 'active',
        ]);

        $mgrUser = User::create([
            'name' => 'Finance Lead',
            'email' => 'fin.mgr@example.com',
            'mobile' => '9333333333',
            'password' => bcrypt('password'),
            'role_title' => 'Department Manager',
            'role_id' => $deptManagerRole->id,
        ]);

        // 1. Employee list is scoped to Finance department
        $empResponse = $this->actingAs($mgrUser)->get(route('employees.index'));
        $empResponse->assertOk();
        $employees = $empResponse->viewData('employees');
        $this->assertEquals(2, $employees->total());
        $this->assertTrue($employees->contains('id', $mgrEmp->id));
        $this->assertTrue($employees->contains('id', $dept1Staff->id));
        $this->assertFalse($employees->contains('id', $dept2Staff->id));

        // 2. Leaves scoping and boundary
        $finLeave = \App\Models\Leave::create([
            'employee_id' => $dept1Staff->id,
            'type' => 'Casual',
            'from_date' => now()->toDateString(),
            'to_date' => now()->toDateString(),
            'days' => 1,
            'status' => 'pending',
            'reason' => 'Finance personal reason',
        ]);

        $salesLeave = \App\Models\Leave::create([
            'employee_id' => $dept2Staff->id,
            'type' => 'Sick',
            'from_date' => now()->toDateString(),
            'to_date' => now()->toDateString(),
            'days' => 1,
            'status' => 'pending',
            'reason' => 'Sales sick reason',
        ]);

        $leavesResponse = $this->actingAs($mgrUser)->get(route('leaves.index'));
        $leavesResponse->assertOk();
        $leaves = $leavesResponse->viewData('leaves');
        $this->assertTrue($leaves->contains('id', $finLeave->id));
        $this->assertFalse($leaves->contains('id', $salesLeave->id));

        // 3. Manager can approve Finance leave
        $approveFin = $this->actingAs($mgrUser)->post(route('leaves.approve', $finLeave->id));
        $approveFin->assertSessionHas('success');

        // 4. Manager CANNOT approve Sales leave (cross-department violation)
        $approveSales = $this->actingAs($mgrUser)->post(route('leaves.approve', $salesLeave->id));
        $approveSales->assertSessionHas('error');
    }

    public function test_permission_guards_block_unauthorized_actions(): void
    {
        // Custom role with ONLY view permissions
        $limitedRole = Role::create(['name' => 'Viewer Only', 'slug' => 'viewer-only', 'is_system' => false]);
        $permEmpView = Permission::firstOrCreate(['slug' => 'employees.view'], ['name' => 'Employees View', 'module' => 'Employees']);
        $permDeptView = Permission::firstOrCreate(['slug' => 'departments.view'], ['name' => 'Departments View', 'module' => 'Departments']);
        $limitedRole->permissions()->sync([$permEmpView->id, $permDeptView->id]);

        $limitedUser = User::create([
            'name' => 'Limited User',
            'email' => 'viewer@example.com',
            'mobile' => '9444444444',
            'password' => bcrypt('password'),
            'role_title' => 'Viewer Only',
            'role_id' => $limitedRole->id,
        ]);

        // Attempt employee create -> 403
        $this->actingAs($limitedUser)->get(route('employees.create'))->assertStatus(403);
        $this->actingAs($limitedUser)->post(route('employees.store'), [])->assertStatus(403);

        // Attempt department store -> 403
        $this->actingAs($limitedUser)->post(route('departments.store'), [
            'name' => 'Hacked Dept',
            'code' => 'HCK',
            'status' => 'active',
        ])->assertStatus(403);

        // Attempt settings access -> 403
        $this->actingAs($limitedUser)->get(route('settings.index'))->assertStatus(403);

        // Attempt roles access -> 403
        $this->actingAs($limitedUser)->get(route('roles.index'))->assertStatus(403);
    }
}
