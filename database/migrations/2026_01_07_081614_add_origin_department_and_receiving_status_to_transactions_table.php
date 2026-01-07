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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('origin_department_id')->nullable()->after('department_id');
            $table->string('receiving_status')->nullable()->after('transaction_status'); // pending, received, not_received
            $table->timestamp('received_at')->nullable()->after('completed_at');
            
            $table->foreign('origin_department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['origin_department_id']);
            $table->dropColumn(['origin_department_id', 'receiving_status', 'received_at']);
        });
    }
};
