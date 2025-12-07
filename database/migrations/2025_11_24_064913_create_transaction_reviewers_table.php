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
        Schema::create('transaction_reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users');
            $table->foreignId('department_id')->constrained('departments');
            $table->enum('status', ['pending', 're_submit', 'return_to_orginating', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('reviewer_notes')->nullable();
            $table->integer('process_time_value');
            $table->enum('process_time_unit', ['minutes', 'days', 'weeks'])->default('days');
            $table->timestamp('due_date');
            $table->boolean('is_overdue')->default(0);
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->integer('iteration_number')->default(1);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('resubmission_deadline')->nullable();
            $table->foreignId('previous_reviewer_id')->nullable()->references('id')->on('users');

            $table->index(['transaction_id', 'status']);
            $table->index(['is_overdue', 'status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_reviewers');
    }
};
