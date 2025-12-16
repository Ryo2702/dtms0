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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_type_id')->constrained();
            $table->text('description')->nullable();
            $table->enum('difficulty', ['simple', 'complex', 'highly_technical'])->default('simple');
            $table->json('workflow_config')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['transaction_type_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
