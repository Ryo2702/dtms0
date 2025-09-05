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
        Schema::table('document_reviews', function (Blueprint $table) {
            $table->renameColumn('expires_at', 'due_at');
            $table->boolean('completed_on_time')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_reviews', function (Blueprint $table) {
            $table->renameColumn('due_at', 'expires_at');
            $table->dropColumn('completed_on_time');
        });
    }
};
