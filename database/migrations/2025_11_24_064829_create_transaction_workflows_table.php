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
        Schema::create('transaction_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_type_id')->constrained('transaction_types')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments');
            $table->integer('sequence_order');
            $table->boolean('is_originating')->default(0);
            $table->integer('process_time_value');
            $table->enum('process_time_unit', ['minutes', 'days', 'weeks'])->default('minutes');

            $table->foreignId('next_step_on_approval')->nullable()->references('id')->on('transaction_workflows');
            $table->foreignId('next_step_on_rejection')->nullable()->references('id')->on('transaction_workflows');
            $table->boolean('allow_cycles')->default(0);
            $table->integer('max_cycle_count')->default(3);

            $table->timestamps();
            $table->unique(['transaction_type_id', 'sequence_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_workflows');
    }
};
