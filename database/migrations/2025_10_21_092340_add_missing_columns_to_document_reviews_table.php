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
            $table->string('difficulty')->default('normal')->after('client_name');
            $table->integer('time_value')->nullable()->after('process_time_minutes');
            $table->string('time_unit')->default('minutes')->after('time_value');
            $table->string('attachment_path')->nullable()->after('document_data');
            $table->string('assigned_staff')->required()->after('assigned_to');

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
