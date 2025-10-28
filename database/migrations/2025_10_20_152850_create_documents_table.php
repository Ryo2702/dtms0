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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_id')->unique();
            $table->string('client_name');
            $table->string('title');
            $table->unsignedBigInteger('reviewer_id');
            $table->integer('process_time');
            $table->enum('time_unit', ['minutes', 'days', 'weeks']);
            $table->integer('time_value');
            $table->enum('priority', ['low','normal', 'medium', 'high', 'urgent']);
            $table->string('assigned_staff');
            $table->string('attachment_path')->nullable();
            $table->string('created_via');
            $table->unsignedBigInteger('department_id');
            $table->enum('status', ['pending', 'under_review', 'approved', 'completed', 'canceled', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('reviewer_id')->references('id')->on('users');
            $table->foreign('department_id')->references('id')->on('departments');


            $table->index(['department_id', 'status']);
            $table->index(['reviewer_id', 'status']);
            $table->index('document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
