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
        Schema::create('user_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('municipal_id');
            $table->string('name');
            $table->string('email');
            $table->unsignedBigInteger('department_id');
            $table->enum('type', ['Staff', 'Head', 'Admin'])->nullable();
            $table->text('reason')->nullable(); // Reason for deactivation
            $table->timestamp('deactivated_at');
            $table->foreignId('deactivated_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_archives');
    }
};
