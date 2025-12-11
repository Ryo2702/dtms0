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
            $table->string('document_name');
            $table->text('description')->nullable();
            $table->foreignId('transaction_type_id')->constrained('transaction_types');
            $table->foreignId('assign_staff_id')->constrained('assign_staff');
            $table->foreignId('department_id')->constrained('departments');
            $table->enum('transaction_status', ['in_progress', 'completed', 'overdue'])->default('in_progress');
            // Current state: pending_{dept}_review, returned_to_{dept}, completed, cancelled
            $table->string('current_state')->default('pending');
            $table->integer('revision_number')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->integer('current_workflow_step')->default(1);
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();


            $table->json('workflow_history')->nullable();
            $table->timestamps();

            $table->index(['department_id', 'transaction_status']);
            $table->index(['transaction_type_id', 'transaction_status']);
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
