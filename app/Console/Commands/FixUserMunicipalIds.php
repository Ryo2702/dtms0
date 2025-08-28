<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixUserMunicipalIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-municipal-ids {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix users with invalid municipal_id formats';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Find users with invalid municipal_id formats
        $usersToFix = User::with('department')
            ->where(function ($query) {
                $query->whereNull('department_id')
                    ->orWhereNull('municipal_id')
                    ->orWhere('municipal_id', 'not like', '%-%');
            })
            ->get()
            ->filter(function ($user) {
                return !$user->hasValidMunicipalId();
            });

        if ($usersToFix->isEmpty()) {
            $this->info('✅ All users have valid municipal IDs.');
            return 0;
        }

        $this->info("Found {$usersToFix->count()} users with invalid municipal IDs:");
        $this->newLine();

        foreach ($usersToFix as $user) {
            $this->line("👤 {$user->name} (ID: {$user->id})");
            $this->line("   Current Municipal ID: {$user->municipal_id}");
            $this->line("   Department: " . ($user->department?->name ?? 'None'));
            $this->line("   Type: {$user->type}");

            if (!$user->department_id) {
                $this->warn("   ⚠️  Missing department - cannot fix automatically");
                $this->newLine();
                continue;
            }

            if (!$dryRun) {
                if ($user->regenerateMunicipalId()) {
                    $this->info("   ✅ Fixed! New Municipal ID: {$user->municipal_id}");
                } else {
                    $this->error("   ❌ Failed to fix municipal ID");
                }
            } else {
                $department = $user->department;
                $newId = $department->generateMunicipalId($user->type);
                $this->info("   🔄 Would change to: {$newId}");
            }

            $this->newLine();
        }

        if ($dryRun) {
            $this->warn('This was a dry run. Use --no-dry-run to apply changes.');
        } else {
            $this->info('✨ Municipal ID fix process completed!');
        }

        return 0;
    }
}
