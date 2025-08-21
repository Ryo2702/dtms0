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
        Schema::table('users', function (Blueprint $table) {
            // Drop the current enum constraint and recreate with Admin option
            $table->dropColumn('type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['Staff', 'Head', 'Admin'])->nullable()->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['Staff', 'Head'])->nullable()->after('department');
        });
    }
};
