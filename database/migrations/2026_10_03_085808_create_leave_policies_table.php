<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_type_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_days_per_year')->nullable();
            $table->boolean('is_unlimited')->default(false);
            $table->boolean('is_paid')->default(true);
            $table->boolean('carry_forward')->default(false);
            $table->unsignedInteger('max_consecutive_days')->nullable();
            $table->unsignedInteger('min_notice_days')->default(0);
            $table->boolean('allow_cancel_approved')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
