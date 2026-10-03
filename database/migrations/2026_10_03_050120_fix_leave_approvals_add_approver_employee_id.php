<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_approvals', function (Blueprint $table) {
            $table->foreignId('approver_employee_id')
                ->nullable()
                ->after('approver_id')
                ->constrained('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leave_approvals', function (Blueprint $table) {
            $table->dropForeign(['approver_employee_id']);
            $table->dropColumn('approver_employee_id');
        });
    }
};
