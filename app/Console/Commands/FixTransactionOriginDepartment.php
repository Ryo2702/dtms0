<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class FixTransactionOriginDepartment extends Command
{
    protected $signature = 'transactions:fix-origin-department';
    protected $description = 'Set origin_department_id for existing transactions based on creator department';

    public function handle()
    {
        $transactions = Transaction::whereNull('origin_department_id')
            ->with('creator')
            ->get();

        $count = 0;
        foreach ($transactions as $transaction) {
            $originDeptId = $transaction->creator?->department_id ?? $transaction->department_id;
            
            if ($originDeptId) {
                $transaction->update(['origin_department_id' => $originDeptId]);
                $count++;
                $this->info("Updated transaction {$transaction->transaction_code}");
            }
        }

        $this->info("Updated {$count} transactions with origin_department_id");

        return Command::SUCCESS;
    }
}
