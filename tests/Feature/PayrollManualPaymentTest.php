<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollManualPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUserWithRole(string $roleSlug, array $permissionSlugs): User
    {
        $role = Role::create([
            'name' => ucwords(str_replace('-', ' ', $roleSlug)),
            'slug' => $roleSlug,
            'is_system' => true,
        ]);

        foreach ($permissionSlugs as $slug) {
            $perm = Permission::firstOrCreate(['slug' => $slug], [
                'name' => ucwords(str_replace('.', ' ', $slug)),
                'module' => 'Payroll',
            ]);
            $role->permissions()->attach($perm->id);
        }

        return User::create([
            'name' => 'HR Admin',
            'email' => 'admin_' . uniqid() . '@example.com',
            'mobile' => '98765' . rand(10000, 99999),
            'password' => bcrypt('password'),
            'role_title' => $role->name,
            'role_id' => $role->id,
        ]);
    }

    protected function createPayrollRecord(float $basic = 50000, float $allowances = 10000, float $deductions = 5000): Payroll
    {
        $dept = Department::create(['name' => 'Tech', 'code' => 'TECH', 'status' => 'active']);
        $emp = Employee::create([
            'employee_code' => 'EMP-701',
            'first_name' => 'Aarav',
            'last_name' => 'Mehta',
            'email' => 'aarav.mehta@example.com',
            'department_id' => $dept->id,
            'designation' => 'Senior Developer',
            'joining_date' => '2024-01-01',
            'salary' => $basic,
            'status' => 'active',
        ]);

        $net = $basic + $allowances - $deductions;

        return Payroll::create([
            'employee_id' => $emp->id,
            'month' => '2026-10',
            'basic_salary' => $basic,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_salary' => $net,
            'paid_amount' => 0,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_disburse_manual_partial_payment(): void
    {
        $admin = $this->makeUserWithRole('super-admin', ['payroll.view', 'payroll.disburse']);
        $payroll = $this->createPayrollRecord(50000, 10000, 5000); // Net: 55,000

        // Disburse custom partial amount of ₹20,000
        $response = $this->actingAs($admin)->post(route('payroll.pay', $payroll->id), [
            'payment_type' => 'custom',
            'paid_amount' => 20000,
            'payment_method' => 'Cash',
            'remarks' => 'Part payment advance',
        ]);

        $response->assertRedirect();
        $payroll->refresh();

        $this->assertEquals(20000.00, (float) $payroll->paid_amount);
        $this->assertEquals('partial', $payroll->status);
        $this->assertEquals(35000.00, $payroll->remaining_amount);
        $this->assertTrue($payroll->is_partially_paid);
        $this->assertFalse($payroll->is_fully_paid);
        $this->assertEquals('Cash', $payroll->payment_method);
    }

    public function test_admin_can_disburse_full_payment(): void
    {
        $admin = $this->makeUserWithRole('super-admin', ['payroll.view', 'payroll.disburse']);
        $payroll = $this->createPayrollRecord(50000, 10000, 5000); // Net: 55,000

        // Disburse full net salary amount
        $response = $this->actingAs($admin)->post(route('payroll.pay', $payroll->id), [
            'payment_type' => 'full',
            'paid_amount' => 55000,
            'payment_method' => 'Bank Transfer',
            'remarks' => 'Full salary settlement',
        ]);

        $response->assertRedirect();
        $payroll->refresh();

        $this->assertEquals(55000.00, (float) $payroll->paid_amount);
        $this->assertEquals('paid', $payroll->status);
        $this->assertEquals(0, $payroll->remaining_amount);
        $this->assertTrue($payroll->is_fully_paid);
        $this->assertFalse($payroll->is_partially_paid);
    }

    public function test_admin_can_manually_adjust_payroll_figures(): void
    {
        $admin = $this->makeUserWithRole('super-admin', ['payroll.view', 'payroll.generate', 'payroll.disburse']);
        $payroll = $this->createPayrollRecord(50000, 10000, 5000);

        // Adjust basic, allowances, deductions manually
        $response = $this->actingAs($admin)->put(route('payroll.update', $payroll->id), [
            'basic_salary' => 52000,
            'allowances' => 12000,
            'deductions' => 4000,
            'net_salary' => 60000,
            'paid_amount' => 30000,
            'status' => 'partial',
            'remarks' => 'Bonus adjusted manually',
        ]);

        $response->assertRedirect();
        $payroll->refresh();

        $this->assertEquals(52000.00, (float) $payroll->basic_salary);
        $this->assertEquals(12000.00, (float) $payroll->allowances);
        $this->assertEquals(4000.00, (float) $payroll->deductions);
        $this->assertEquals(60000.00, (float) $payroll->net_salary);
        $this->assertEquals(30000.00, (float) $payroll->paid_amount);
        $this->assertEquals('partial', $payroll->status);
        $this->assertEquals(30000.00, $payroll->remaining_amount);
    }

    public function test_payslip_displays_manual_paid_amount_and_remaining_balance(): void
    {
        $admin = $this->makeUserWithRole('super-admin', ['payroll.view']);
        $payroll = $this->createPayrollRecord(50000, 10000, 5000); // Net: 55,000

        $payroll->update([
            'paid_amount' => 25000,
            'status' => 'partial',
            'remarks' => '1st Installment paid',
        ]);

        $response = $this->actingAs($admin)->get(route('payroll.payslip', $payroll->id));
        $response->assertOk();
        $response->assertSee('PARTIALLY PAID');
        $response->assertSee('25,000.00'); // Amount Disbursed
        $response->assertSee('30,000.00'); // Remaining Due
        $response->assertSee('1st Installment paid');
    }

    public function test_payroll_export_includes_paid_amount_and_remaining_balance(): void
    {
        $admin = $this->makeUserWithRole('super-admin', ['payroll.view']);
        $payroll = $this->createPayrollRecord(50000, 10000, 5000); // Net: 55,000

        $payroll->update([
            'paid_amount' => 30000,
            'status' => 'partial',
        ]);

        $response = $this->actingAs($admin)->get(route('payroll.export', ['month' => '2026-10']));
        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Paid Amount', $content);
        $this->assertStringContainsString('Remaining Balance', $content);
        $this->assertStringContainsString('30000', $content);
        $this->assertStringContainsString('25000', $content);
    }
}
