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
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('verification_code')->unique();
            $table->string('document_id');
            $table->string('document_type');
            $table->string('client_name');
            $table->string('issued_by'); // Employee name
            $table->string('issued_by_id'); // Employee ID
            $table->timestamp('issued_at');
            $table->json('document_data'); // Store key document details
            $table->string('official_receipt_number')->nullable();
            $table->integer('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['verification_code', 'is_active']);
            $table->index('document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
