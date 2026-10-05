<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyRegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\MobileOtpController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

// Login page
Route::get('/', [AuthController::class, 'showLogin'])
    ->name('login');

// Email Login
Route::post('/login/email', [AuthController::class, 'emailLogin'])
    ->name('login.email');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Mobile OTP Login
|--------------------------------------------------------------------------
*/

Route::post('/login/send-otp', [MobileOtpController::class, 'sendOtp'])
    ->name('login.sendOtp');

Route::get('/login/verify-otp', [MobileOtpController::class, 'showVerifyForm'])
    ->name('mobile.otp.verify');

Route::post('/login/verify-otp', [MobileOtpController::class, 'verifyOtp'])
    ->name('mobile.otp.verify.submit');

/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
*/

Route::get('/forgot-password', function () {
    return view('auth.forgot_password');
})->name('password.request');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset_password', [
        'token' => $token,
        'email' => request('email'),
    ]);
})->name('password.reset');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->name('password.update');

/*
|--------------------------------------------------------------------------
| Company Registration
|--------------------------------------------------------------------------
*/

Route::get('/register', [CompanyRegistrationController::class, 'create'])
    ->name('company.register.form');

Route::post('/register', [CompanyRegistrationController::class, 'store'])
    ->name('company.register');

/*
|--------------------------------------------------------------------------
| Protected HRMS Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    */

    Route::resource('departments', DepartmentController::class)
        ->except(['create', 'show', 'edit'])
        ->middlewareFor('index', 'permission:departments.view')
        ->middlewareFor('store', 'permission:departments.create')
        ->middlewareFor('update', 'permission:departments.edit')
        ->middlewareFor('destroy', 'permission:departments.delete');

    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    Route::resource('employees', EmployeeController::class)
        ->middlewareFor('index', 'permission:employees.view')
        ->middlewareFor('create', 'permission:employees.create')
        ->middlewareFor('store', 'permission:employees.create')
        ->middlewareFor('edit', 'permission:employees.edit')
        ->middlewareFor('update', 'permission:employees.edit')
        ->middlewareFor('destroy', 'permission:employees.delete');

    /*
    |--------------------------------------------------------------------------
    | Teams
    |--------------------------------------------------------------------------
    */

    Route::resource('teams', TeamController::class)
        ->middlewareFor('index', 'permission:teams.view')
        ->middlewareFor('create', 'permission:teams.create')
        ->middlewareFor('store', 'permission:teams.create')
        ->middlewareFor('show', 'permission:teams.view')
        ->middlewareFor('edit', 'permission:teams.edit')
        ->middlewareFor('update', 'permission:teams.edit')
        ->middlewareFor('destroy', 'permission:teams.delete');

    Route::post('/teams/{team}/assign-employee', [TeamController::class, 'assignEmployee'])
        ->middleware('permission:teams.edit')
        ->name('teams.assign-employee');

    Route::post('/teams/{team}/remove-employee/{employee}', [TeamController::class, 'removeEmployee'])
        ->middleware('permission:teams.edit')
        ->name('teams.remove-employee');

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->middleware('permission:attendance.view')
        ->name('attendance.index');

    Route::post('/attendance/mark', [AttendanceController::class, 'mark'])
        ->middleware('permission:attendance.create')
        ->name('attendance.mark');

    /*
    |--------------------------------------------------------------------------
    | Leaves
    |--------------------------------------------------------------------------
    */

    Route::get('/leaves/create', [LeaveController::class, 'create'])
        ->name('leaves.create');

    Route::post('/leaves', [LeaveController::class, 'store'])
        ->name('leaves.store');

    Route::get('/leaves', [LeaveController::class, 'index'])
        ->middleware('permission:leaves.view')
        ->name('leaves.index');

    Route::get('/leaves/{leave}', [LeaveController::class, 'show'])
        ->middleware('permission:leaves.view')
        ->name('leaves.show');

    Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])
        ->middleware('permission:leaves.approve')
        ->name('leaves.approve');

    Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])
        ->middleware('permission:leaves.reject')
        ->name('leaves.reject');

    Route::post('/leaves/{leave}/cancel', [LeaveController::class, 'cancel'])
        ->name('leaves.cancel');

    /*
    |--------------------------------------------------------------------------
    | Leave Types
    |--------------------------------------------------------------------------
    */

    Route::resource('leave-types', LeaveTypeController::class)
        ->middlewareFor('index', 'permission:leave-types.view')
        ->middlewareFor('create', 'permission:leave-types.create')
        ->middlewareFor('store', 'permission:leave-types.create')
        ->middlewareFor('edit', 'permission:leave-types.edit')
        ->middlewareFor('update', 'permission:leave-types.edit')
        ->middlewareFor('destroy', 'permission:leave-types.delete');

    /*
    |--------------------------------------------------------------------------
    | Payroll
    |--------------------------------------------------------------------------
    */

    Route::get('/payroll', [PayrollController::class, 'index'])
        ->middleware('permission:payroll.view')
        ->name('payroll.index');

    Route::post('/payroll/generate', [PayrollController::class, 'generate'])
        ->middleware('permission:payroll.create')
        ->name('payroll.generate');

    Route::post('/payroll/{payroll}/pay', [PayrollController::class, 'markPaid'])
        ->middleware('permission:payroll.edit')
        ->name('payroll.pay');

    Route::get('/payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])
        ->middleware('permission:payroll.view')
        ->name('payroll.payslip');

    /*
    |--------------------------------------------------------------------------
    | Holidays
    |--------------------------------------------------------------------------
    */

    Route::resource('holidays', HolidayController::class)
        ->except(['create', 'show', 'edit'])
        ->middlewareFor('index', 'permission:holidays.view')
        ->middlewareFor('store', 'permission:holidays.create')
        ->middlewareFor('update', 'permission:holidays.edit')
        ->middlewareFor('destroy', 'permission:holidays.delete');

    /*
    |--------------------------------------------------------------------------
    | Tasks
    |--------------------------------------------------------------------------
    */

    Route::get('/tasks', [TaskController::class, 'index'])
        ->middleware('permission:tasks.view')
        ->name('tasks.index');

    Route::get('/tasks/employees-by-team', [TaskController::class, 'getEmployeesByTeam'])
        ->name('tasks.employees-by-team');

    Route::post('/tasks/notifications/mark-read', [TaskController::class, 'markNotificationsRead'])
        ->name('tasks.notifications.mark-read');

    Route::get('/tasks/create', [TaskController::class, 'create'])
        ->middleware('permission:tasks.create')
        ->name('tasks.create');

    Route::post('/tasks', [TaskController::class, 'store'])
        ->middleware('permission:tasks.create')
        ->name('tasks.store');

    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->middleware('permission:tasks.view')
        ->name('tasks.show');

    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])
        ->middleware('permission:tasks.edit')
        ->name('tasks.edit');

    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->middleware('permission:tasks.edit')
        ->name('tasks.update');

    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->middleware('permission:tasks.delete')
        ->name('tasks.destroy');

    Route::post('/tasks/{task}/start', [TaskController::class, 'start'])
        ->name('tasks.start');

    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])
        ->name('tasks.complete');

    Route::post('/tasks/{task}/cancel', [TaskController::class, 'cancel'])
        ->name('tasks.cancel');

    Route::post('/tasks/{task}/reassign', [TaskController::class, 'reassign'])
        ->name('tasks.reassign');

    Route::post('/tasks/{task}/upload', [TaskController::class, 'uploadAttachment'])
        ->name('tasks.upload');

    Route::get('/tasks/attachments/{attachment}', [TaskController::class, 'downloadAttachment'])
        ->name('tasks.download');

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions
    |--------------------------------------------------------------------------
    */

    Route::resource('roles', RoleController::class)
        ->middlewareFor('index', 'permission:roles.view')
        ->middlewareFor('create', 'permission:roles.create')
        ->middlewareFor('store', 'permission:roles.create')
        ->middlewareFor('show', 'permission:roles.view')
        ->middlewareFor('edit', 'permission:roles.edit')
        ->middlewareFor('update', 'permission:roles.edit')
        ->middlewareFor('destroy', 'permission:roles.delete');

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', [SettingController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('settings.index');

    Route::post('/settings', [SettingController::class, 'update'])
        ->middleware('permission:settings.edit')
        ->name('settings.update');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('permission:profile.view')
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->middleware('permission:profile.edit')
        ->name('profile.update');

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->middleware('permission:profile.edit')
        ->name('profile.password');

});
