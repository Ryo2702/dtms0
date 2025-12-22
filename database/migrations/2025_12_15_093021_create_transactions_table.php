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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();

            $table->enum('level_of_urgency', ['normal', 'urgent', 'highly_urgent'])->default('normal');

            $table->string('workflow_id');
            $table->foreignId('document_tags_id')->constrained('document_tags');
            $table->foreignId('assign_staff_id')->constrained('assign_staff');
            $table->foreignId('department_id')->constrained('departments');

            $table->enum('transaction_status', ['in_progress', 'completed', 'overdue'])->default('in_progress');

            // Current state: pending_{dept}_review, returned_to_{dept}, completed, cancelled
            $table->string('current_state')->default('pending');
            $table->integer('revision_number')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->integer('current_workflow_step')->default(1);
            $table->integer('total_workflow_steps')->default(1);
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();


            $table->json('workflow_history')->nullable();
            $table->json('workflow_snapshot')->nullable(); 
            $table->timestamps();

            $table->foreign('workflow_id')->references('id')->on('workflows')->cascadeOnDelete();
            $table->index(['department_id', 'transaction_status']);
            $table->index('submitted_at');
        });

        Schema::create('transaction_reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users');
            $table->foreignId('department_id')->constrained('departments');
            $table->enum('status', ['pending', 're_submit', 'return_to_orginating', 'approved', 'rejected', 'cancelled'])->default('pending');
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

        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->string('from_state');
            $table->string('to_state');
            $table->string('action'); // approve, reject, resubmit, cancel
            $table->foreignId('action_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_reviewers');
        Schema::dropIfExists('transaction_logs');
        Schema::dropIfExists('transactions');
    }
};
