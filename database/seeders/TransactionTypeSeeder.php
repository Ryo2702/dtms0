<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('transaction_types')->insert([
            // Personnel & Compensation
            ['transaction_name' => 'Payroll Processing', 'description' => 'End-to-end payroll transaction across HR, Budget, Accounting, and Treasury'],
            ['transaction_name' => 'Overtime and Honorarium Processing', 'description' => 'Processing of overtime pay and honoraria'],
            ['transaction_name' => 'Salary Adjustment and Step Increment', 'description' => 'Salary increases and step increments'],
            ['transaction_name' => 'Terminal Leave and Retirement Processing', 'description' => 'Final compensation for retiring or separating employees'],
            ['transaction_name' => 'Personnel Appointment with Budget Impact', 'description' => 'Appointments affecting plantilla and payroll'],

            // Procurement & Property
            ['transaction_name' => 'Purchase Order Processing', 'description' => 'Procurement of goods and services'],
            ['transaction_name' => 'Small Value Procurement', 'description' => 'Procurement below threshold'],
            ['transaction_name' => 'Bidding and Contract Award', 'description' => 'Competitive procurement and awarding'],
            ['transaction_name' => 'Infrastructure Project Procurement', 'description' => 'Procurement for infrastructure projects'],
            ['transaction_name' => 'Asset Acquisition', 'description' => 'Purchase of assets'],
            ['transaction_name' => 'Asset Issuance and Transfer', 'description' => 'Issuance or transfer of LGU assets'],
            ['transaction_name' => 'Asset Disposal and Condemnation', 'description' => 'Disposal of unserviceable assets'],

            // Financial Management
            ['transaction_name' => 'Disbursement Voucher Processing', 'description' => 'Payment processing and approval'],
            ['transaction_name' => 'Cash Advance Release and Liquidation', 'description' => 'Cash advance issuance and liquidation'],
            ['transaction_name' => 'Progress Billing and Final Billing', 'description' => 'Billing for project milestones'],
            ['transaction_name' => 'Refund and Adjustment Processing', 'description' => 'Financial corrections and refunds'],

            // Planning & Budget
            ['transaction_name' => 'PPMP Consolidation', 'description' => 'Office PPMP consolidation'],
            ['transaction_name' => 'Annual Procurement Plan Processing', 'description' => 'APP preparation and approval'],
            ['transaction_name' => 'Budget Realignment', 'description' => 'Movement of budget allocations'],
            ['transaction_name' => 'Supplemental Budget Processing', 'description' => 'Processing of supplemental budgets'],
            ['transaction_name' => 'Fund Allocation and Control', 'description' => 'Budget fund control'],

            // Programs & Projects
            ['transaction_name' => 'Project Implementation Monitoring', 'description' => 'Tracking project implementation'],
            ['transaction_name' => 'Program Fund Release', 'description' => 'Release of funds for programs'],
            ['transaction_name' => 'MOA and MOU Fund Utilization', 'description' => 'Use of funds under MOA/MOU'],

            // Compliance & Control
            ['transaction_name' => 'COA Audit Compliance', 'description' => 'Audit findings and compliance actions'],
            ['transaction_name' => 'Financial Reconciliation', 'description' => 'Cross-office financial reconciliation'],
            ['transaction_name' => 'Internal Control Review', 'description' => 'Internal audit and control checks'],

            // Administrative Support
            ['transaction_name' => 'Travel Authority and Claims', 'description' => 'Travel approval and claims'],
            ['transaction_name' => 'Training and Seminar Processing', 'description' => 'Staff development transactions'],
            ['transaction_name' => 'Logistics and Supplies Request', 'description' => 'Internal logistics and supplies'],
        ]);
    }
}
