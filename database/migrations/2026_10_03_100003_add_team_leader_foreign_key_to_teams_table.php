<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('team_leader_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();

            $table->unique('team_leader_id');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['team_leader_id']);
            $table->dropForeign(['team_leader_id']);
        });
    }
};
