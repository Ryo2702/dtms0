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
        Schema::create('document_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('document_id')->unique();
            $table->string('document_type');
            $table->string('client_name');
            $table->json('document_data'); // Store form data
            $table->string('official_receipt_number')->nullable();
            $table->unsignedBigInteger('created_by'); // who created
            $table->unsignedBigInteger('assigned_to')->nullable(); // Department reviewer
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected'])->default('pending');
            $table->text('review_notes')->nullable();
            $table->integer('process_time_minutes')->default(5); // 1-10 minutes
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('assigned_to')->references('id')->on('users');
        });

        Schema::table('document_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('current_department_id')->nullable()->after('assigned_to');
            $table->unsignedBigInteger('original_department_id')->nullable()->after('current_department_id');
            $table->text('forwarding_notes')->nullable()->after('review_notes');
            $table->json('forwarding_chain')->nullable()->after('forwarding_notes');
            $table->boolean('is_final_review')->default(false)->after('forwarding_chain');

            $table->foreign('current_department_id')->references('id')->on('departments');
            $table->foreign('original_department_id')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_reviews', function (Blueprint $table) {
            $table->dropForeign(['current_department_id']);
            $table->dropForeign(['original_department_id']);
            $table->dropColumn([
                'current_department_id',
                'original_department_id',
                'forwarding_notes',
                'forwarding_chain',
                'is_final_review'
            ]);
        });
    }
};
