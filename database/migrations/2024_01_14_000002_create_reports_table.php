<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('report_type'); // 'transaction', 'workflow', 'user', 'department'
            $table->foreignId('template_id')->nullable()->constrained('report_templates')->onDelete('set null');
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->string('sort_by')->nullable();
            $table->datetime('date_range_start')->nullable();
            $table->datetime('date_range_end')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_public')->default(false);
            $table->string('schedule_frequency')->nullable(); // 'daily', 'weekly', 'monthly'
            $table->datetime('last_generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
