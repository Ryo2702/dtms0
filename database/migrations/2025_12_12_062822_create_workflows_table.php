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
            $table->string('id')->primary();
            $table->string('transaction_name');
            $table->text('description')->nullable();
            $table->enum('difficulty', ['simple', 'complex', 'highly_technical'])->default('simple');
            $table->json('workflow_config')->nullable();
            $table->json('origin_departments')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });


        Schema::create('document_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('status');
            $table->index('slug');
        });

        Schema::create('department_document_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('document_tag_id')->constrained('document_tags');
            $table->timestamps();

            $table->unique(['department_id', 'document_tag_id']);
        });
        Schema::create('document_tag_workflow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_tag_id')->constrained('document_tags');
            $table->string('workflow_id');
            $table->boolean('is_required')->default(false);
            $table->timestamps();


            $table->unique(['document_tag_id', 'workflow_id']);
            $table->index('workflow_id');

            $table->foreign('workflow_id')
                ->references('id')
                ->on('workflows')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop pivot first to avoid foreign key issues
        Schema::dropIfExists('department_document_tag');
        Schema::dropIfExists('document_tag_workflow');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('workflows');
    }
};
