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
            // Modify the status ENUM to include 'canceled'
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected', 'canceled'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_reviews', function (Blueprint $table) {
            // Revert back to original ENUM values
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected'])
                ->default('pending')
                ->change();
        });
    }
};
