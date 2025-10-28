<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('document_reviews', 'priority')) {
                $table->string('priority')->after('client_name');
            }
            if (!Schema::hasColumn('document_reviews', 'time_value')) {
                $table->integer('time_value')->nullable()->after('process_time_minutes');
            }
            if (!Schema::hasColumn('document_reviews', 'time_unit')) {
                $table->string('time_unit')->default('minutes')->after('time_value');
            }
            if (!Schema::hasColumn('document_reviews', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('document_data');
            }
            if (!Schema::hasColumn('document_reviews', 'assigned_staff')) {
                $table->string('assigned_staff')->nullable()->after('assigned_to');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_reviews', function (Blueprint $table) {
            //
        });
    }
};
