<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::post('/login/email', [AuthController::class, 'emailLogin'])
    ->name('login.email');

Route::post('/login/send-otp', [AuthController::class, 'sendOtp'])
    ->name('login.sendOtp');

Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp'])
    ->name('login.verifyOtp');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


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

// Dashboard
Route::get('/', [DashboardController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Departments
Route::resource('departments', DepartmentController::class)->except(['create', 'show', 'edit']);

// Employees
Route::resource('employees', EmployeeController::class);

// Attendance
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');

// Leaves & Workflow Actions
Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');

// Payroll
Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
Route::post('/payroll/{payroll}/pay', [PayrollController::class, 'markPaid'])->name('payroll.pay');
Route::get('/payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payroll.payslip');

// Holidays
Route::resource('holidays', HolidayController::class)->except(['create', 'show', 'edit']);

// Roles & Permissions
Route::resource('roles', RoleController::class);

// Settings
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

// Profile Management
Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
