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
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('assigned_date')->nullable()->after('assigned_to');
            $table->unsignedBigInteger('last_reassigned_by')->nullable()->after('assigned_date');
            $table->timestamp('last_reassigned_at')->nullable()->after('last_reassigned_by');

            $table->foreign('last_reassigned_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['last_reassigned_by']);
            $table->dropColumn(['assigned_date', 'last_reassigned_by', 'last_reassigned_at']);
        });
    }
};
