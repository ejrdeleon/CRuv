<?php

namespace App\Console\Commands;

use App\Models\Report;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResetReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:reset
                            {--dry-run : Preview what will be deleted without modifying data}
                            {--force : Force execution without prompt in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely wipe all community reports, attachments, timelines, and reset auto-increments';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $isForce = (bool) $this->option('force');

        // 1. Production safety guard
        if (app()->environment('production') && ! $isForce) {
            $this->error('CRITICAL: The application is running in PRODUCTION mode.');
            $this->line('You must pass the --force flag to execute this destructive command in production.');

            return self::FAILURE;
        }

        $this->info('Scanning database and disk storage for report data...');

        // 2. Inspect record counts across affected tables
        $counts = [
            'reports' => DB::table('reports')->count(),
            'report_images' => DB::table('report_images')->count(),
            'report_status_histories' => DB::table('report_status_histories')->count(),
            'report_comments' => DB::table('report_comments')->count(),
            'report_assignments' => DB::table('report_assignments')->count(),
            'notifications (report-related)' => DB::table('notifications')->whereNotNull('report_id')->count(),
            'activity_logs (report-related)' => DB::table('activity_logs')->where('subject_type', Report::class)->count(),
        ];

        // Inspect report storage files (excluding any backup directory)
        $disk = Storage::disk('public');
        $allReportFiles = collect($disk->allFiles('reports'))
            ->reject(fn (string $path) => str_starts_with($path, 'reports_backup'))
            ->values();

        $fileCount = $allReportFiles->count();
        $totalBytes = $allReportFiles->sum(fn (string $path) => $disk->exists($path) ? $disk->size($path) : 0);
        $formattedBytes = round($totalBytes / 1024 / 1024, 2).' MB ('.$totalBytes.' bytes)';

        $this->newLine();
        $this->line('<fg=cyan;options=bold>=========================================================</>');
        $this->line('<fg=cyan;options=bold>  COMMUNITY REPORTING SYSTEM — DATA RESET AUDIT</>');
        $this->line('<fg=cyan;options=bold>=========================================================</>');
        $this->newLine();

        $this->line('<fg=yellow;options=bold>TABLES & RECORDS SCHEDULED FOR PURGE:</>');
        $tableRows = [];
        foreach ($counts as $table => $count) {
            $tableRows[] = [$table, number_format($count)];
        }
        $this->table(['Database Table / Query', 'Rows to Delete'], $tableRows);

        $this->newLine();
        $this->line('<fg=yellow;options=bold>STORAGE ATTACHMENTS SCHEDULED FOR PURGE:</>');
        $this->table(['Target Storage Location', 'Files', 'Size on Disk'], [
            ['public disk: storage/app/public/reports/*', number_format($fileCount), $formattedBytes],
        ]);

        $this->newLine();
        $this->line('<fg=green;options=bold>PROTECTED ENTITIES (KEPT UNTOUCHED):</>');
        $this->line(' - Users and Roles (Admin, Staff, Residents)');
        $this->line(' - Departments and Report Categories');
        $this->line(' - Municipal Geographic & System Settings');
        $this->line(' - Migrations, Sessions, and Personal Access Tokens');
        $this->newLine();

        // 3. Dry-run early exit
        if ($isDryRun) {
            $this->warn('[DRY-RUN ENABLED]: Simulation complete. No database records or storage files were deleted.');

            return self::SUCCESS;
        }

        // 4. Interactive Confirmation Guard
        if (! $isForce) {
            $this->warn('WARNING: This will permanently delete all '.number_format($counts['reports']).' reports and their media.');
            $confirmation = $this->ask('Type "RESET" to confirm and proceed with permanent deletion');

            if ($confirmation !== 'RESET') {
                $this->info('Operation aborted by user. No changes were made.');

                return self::SUCCESS;
            }
        }

        // 5. Database Transaction Execution
        $this->info('Executing atomic database purge inside transaction...');

        DB::beginTransaction();

        try {
            // Delete dependent child tables in proper foreign-key hierarchy
            DB::table('report_assignments')->delete();
            DB::table('report_comments')->delete();
            DB::table('report_status_histories')->delete();
            DB::table('report_images')->delete();
            DB::table('notifications')->whereNotNull('report_id')->delete();
            DB::table('activity_logs')->where('subject_type', Report::class)->delete();

            // Break self-referencing duplicate pointers before wiping the parent reports table
            DB::table('reports')->update(['duplicate_of_id' => null]);
            DB::table('reports')->delete();

            DB::commit();
            $this->info('✔ Database transaction committed successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Transaction failed! All database changes have been rolled back: '.$e->getMessage());

            return self::FAILURE;
        }

        // Reset auto-increment primary keys to 1 (DDL causes implicit commit in MySQL, run post-transaction)
        try {
            $connectionDriver = DB::getDriverName();
            if ($connectionDriver === 'mysql') {
                DB::statement('ALTER TABLE report_assignments AUTO_INCREMENT = 1');
                DB::statement('ALTER TABLE report_comments AUTO_INCREMENT = 1');
                DB::statement('ALTER TABLE report_status_histories AUTO_INCREMENT = 1');
                DB::statement('ALTER TABLE report_images AUTO_INCREMENT = 1');
                DB::statement('ALTER TABLE reports AUTO_INCREMENT = 1');
            } elseif ($connectionDriver === 'sqlite') {
                DB::statement("DELETE FROM sqlite_sequence WHERE name IN ('report_assignments', 'report_comments', 'report_status_histories', 'report_images', 'reports')");
            }
            $this->info('✔ Auto-increment sequences reset to 1.');
        } catch (\Throwable $e) {
            $this->warn('Notice: Could not reset auto-increment sequences: '.$e->getMessage());
        }

        // 6. Delete Evidence Files in Storage
        $this->info('Cleaning up storage evidence files...');
        $deletedFileCount = 0;
        foreach ($allReportFiles as $filePath) {
            if ($disk->exists($filePath)) {
                $disk->delete($filePath);
                $deletedFileCount++;
            }
        }

        // Clean up empty directories under reports/ (e.g. thumbnails)
        $subdirectories = $disk->directories('reports');
        foreach ($subdirectories as $dir) {
            if (! str_starts_with($dir, 'reports/reports_backup')) {
                $disk->deleteDirectory($dir);
            }
        }

        $this->info("✔ Deleted {$deletedFileCount} evidence files and purged empty storage subdirectories.");

        // 7. Invalidate Specific Report-Related Cache Keys
        $specificCacheKeys = [
            'admin.dashboard.stats',
            'admin.dashboard.metrics',
            'admin.map.markers',
            'admin.reports.categories_count',
            'reports.stats',
        ];

        foreach ($specificCacheKeys as $key) {
            Cache::forget($key);
        }

        $this->info('✔ Invalidated report dashboard and map spatial cache keys.');

        $this->newLine();
        $this->line('<fg=green;options=bold>=========================================================</>');
        $this->line('<fg=green;options=bold>  REPORTS RESET COMPLETE: SYSTEM IS CLEAN & READY</>');
        $this->line('<fg=green;options=bold>=========================================================</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
