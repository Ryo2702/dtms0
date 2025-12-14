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
        Schema::create('document_tag_workflow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_tag_id')->constrained('document_tags');
            $table->foreignId('workflow_id')->constrained('workflows');
            $table->boolean('is_required')->default(false);
            $table->timestamps();


            $table->unique(['document_tag_id', 'workflow_id']);
            $table->index('workflow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_tag_workflow');
    }
};
