<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
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

// Mobile OTP
Route::post('/login/send-otp', [AuthController::class, 'sendOtp'])
    ->name('login.sendOtp');

Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp'])
    ->name('login.verifyOtp');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


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
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    */

    Route::resource('departments', DepartmentController::class)
        ->except(['create', 'show', 'edit']);


    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    Route::get('/employees/export', [EmployeeController::class, 'export'])
        ->name('employees.export');

    Route::resource('employees', EmployeeController::class);


    /*
    |--------------------------------------------------------------------------
    | Teams
    |--------------------------------------------------------------------------
    */

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
        ->name('attendance.index');

    Route::get('/attendance/export', [AttendanceController::class, 'export'])
        ->name('attendance.export');

    Route::post('/attendance/mark', [AttendanceController::class, 'mark'])
        ->name('attendance.mark');


    /*
    |--------------------------------------------------------------------------
    | Leaves
    |--------------------------------------------------------------------------
    */

    Route::get('/leaves', [LeaveController::class, 'index'])
        ->name('leaves.index');

    Route::get('/leaves/export', [LeaveController::class, 'export'])
        ->name('leaves.export');

    Route::post('/leaves', [LeaveController::class, 'store'])
        ->name('leaves.store');

    Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])
        ->name('leaves.approve');

    Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])
        ->name('leaves.reject');


    /*
    |--------------------------------------------------------------------------
    | Payroll
    |--------------------------------------------------------------------------
    */

    Route::get('/payroll', [PayrollController::class, 'index'])
        ->name('payroll.index');

    Route::get('/payroll/export', [PayrollController::class, 'export'])
        ->name('payroll.export');

    Route::get('/payroll/configuration', [PayrollController::class, 'configuration'])
        ->name('payroll.configuration');

    Route::post('/payroll/configuration', [PayrollController::class, 'updateConfiguration'])
        ->name('payroll.configuration.update');

    Route::post('/payroll/generate', [PayrollController::class, 'generate'])
        ->name('payroll.generate');

    Route::post('/payroll/{payroll}/pay', [PayrollController::class, 'markPaid'])
        ->name('payroll.pay');

    Route::put('/payroll/{payroll}', [PayrollController::class, 'update'])
        ->name('payroll.update');

    Route::get('/payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])
        ->name('payroll.payslip');


    /*
    |--------------------------------------------------------------------------
    | Holidays
    |--------------------------------------------------------------------------
    */

    Route::resource('holidays', HolidayController::class)
        ->except(['create', 'show', 'edit']);


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

    Route::post('/roles/assign-employee', [RoleController::class, 'assignEmployeeRole'])
        ->name('roles.assignEmployee');

    Route::post('/roles/bulk-assign', [RoleController::class, 'bulkAssignEmployees'])
        ->name('roles.bulkAssign');

    Route::resource('roles', RoleController::class);


    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', [SettingController::class, 'index'])
        ->name('settings.index');

    Route::post('/settings', [SettingController::class, 'update'])
        ->name('settings.update');


    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    Route::post('/profile/theme', [ProfileController::class, 'updateTheme'])
        ->name('profile.theme');
});
