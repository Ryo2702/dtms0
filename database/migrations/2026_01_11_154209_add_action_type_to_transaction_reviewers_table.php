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
        Schema::table('transaction_reviewers', function (Blueprint $table) {
            // Add action type for approval path
            $table->enum('action_type', [
                'review',           // Checks correctness without assuming liability
                'validate',         // Confirms compliance with rules, plans, or law
                'endorse',          // Passes responsibility upward with recommendation
                'approve',          // Exercises legal authority. This is a binding signature
                'certify',          // Attests to a specific fact (funds, delivery, inspection)
                'lock',             // Freezes content automatically after approval/certification
                'release',          // Makes the document actionable by the next office
                'complete',         // Marks the transaction finished for that stage
                'return_revision',  // Sends document back with findings; keeps it alive
                'resubmit'          // Re-enters the approval path after corrections
            ])->nullable()->after('status');
            
            // Add remarks field
            $table->text('remarks')->nullable()->after('rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_reviewers', function (Blueprint $table) {
            $table->dropColumn(['action_type', 'remarks']);
        });
    }
};
