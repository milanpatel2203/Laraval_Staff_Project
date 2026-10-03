<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_id')
                ->constrained('leaves')
                ->cascadeOnDelete();

            // User ID of the approver
            $table->foreignId('approver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Employee ID of the approver
            $table->foreignId('approver_employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('level')->default(1);

            $table->string('action')->default('pending');

            $table->text('remarks')->nullable();

            $table->timestamp('acted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');
    }
};
