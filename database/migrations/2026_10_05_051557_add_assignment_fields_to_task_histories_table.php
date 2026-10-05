<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('old_assigned_to')->nullable()->after('new_value');
            $table->unsignedBigInteger('new_assigned_to')->nullable()->after('old_assigned_to');

            $table->foreign('old_assigned_to')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('new_assigned_to')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_histories', function (Blueprint $table) {
            $table->dropForeign(['old_assigned_to']);
            $table->dropForeign(['new_assigned_to']);
            $table->dropColumn(['old_assigned_to', 'new_assigned_to']);
        });
    }
};
