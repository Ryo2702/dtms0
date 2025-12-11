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
        Schema::create('transaction_types', function (Blueprint $table) {
            $table->id();
            $table->string('document_name')->unique();
            $table->text('description')->nullable();
            $table->json('workflow_config')->nullable();
            // workflow_config structure:
            // {
            //   "steps": [
            //     {"order": 1, "department_id": 1, "department_name": "Budget Office", "can_return_to": []},
            //     {"order": 2, "department_id": 2, "department_name": "Accounting", "can_return_to": [1]},
            //     {"order": 3, "department_id": 3, "department_name": "Treasury", "can_return_to": [1, 2]}
            //   ],
            //   "transitions": { ... auto-generated ... }
            // }
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_types');
    }
};
