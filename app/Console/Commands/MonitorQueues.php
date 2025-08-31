<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitorQueues extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'queue:monitor {--queue= : Specific queue to monitor} {--detailed : Show detailed information}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor queue health and performance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $queue = $this->option('queue');
        $detailed = $this->option('detailed');

        $this->info('📊 Queue Health Monitor');
        $this->info('=====================');

        if ($queue) {
            $this->monitorSpecificQueue($queue, $detailed);
        } else {
            $this->monitorAllQueues($detailed);
        }

        return Command::SUCCESS;
    }

    /**
     * Monitor a specific queue.
     */
    protected function monitorSpecificQueue(string $queueName, bool $detailed): void
    {
        $this->info("\n🔍 Monitoring queue: {$queueName}");
        
        $stats = $this->getQueueStats($queueName);
        $this->displayQueueStats($queueName, $stats, $detailed);
        
        if ($detailed) {
            $this->displayDetailedQueueInfo($queueName);
        }
    }

    /**
     * Monitor all queues.
     */
    protected function monitorAllQueues(bool $detailed): void
    {
        $queues = ['default', 'emails', 'high', 'low'];
        
        foreach ($queues as $queueName) {
            $this->info("\n🔍 Queue: {$queueName}");
            $stats = $this->getQueueStats($queueName);
            $this->displayQueueStats($queueName, $stats, $detailed);
        }

        if ($detailed) {
            $this->displaySystemOverview();
        }
    }

    /**
     * Get queue statistics.
     */
    protected function getQueueStats(string $queueName): array
    {
        $pending = DB::table('jobs')
            ->where('queue', $queueName)
            ->count();

        $reserved = DB::table('jobs')
            ->where('queue', $queueName)
            ->whereNotNull('reserved_at')
            ->count();

        $failed = DB::table('failed_jobs')
            ->where('queue', $queueName)
            ->count();

        $recentJobs = DB::table('jobs')
            ->where('queue', $queueName)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'created_at', 'attempts']);

        return [
            'pending' => $pending,
            'reserved' => $reserved,
            'failed' => $failed,
            'recent_jobs' => $recentJobs,
        ];
    }

    /**
     * Display queue statistics.
     */
    protected function displayQueueStats(string $queueName, array $stats, bool $detailed): void
    {
        $status = $this->getQueueStatus($stats);
        
        $this->line("Status: {$status}");
        $this->line("Pending jobs: {$stats['pending']}");
        $this->line("Reserved jobs: {$stats['reserved']}");
        $this->line("Failed jobs: {$stats['failed']}");

        if ($detailed && $stats['recent_jobs']->isNotEmpty()) {
            $this->line("\nRecent jobs:");
            foreach ($stats['recent_jobs'] as $job) {
                $age = now()->diffInSeconds(\Carbon\Carbon::createFromTimestamp($job->created_at));
                $this->line("  - ID: {$job->id}, Age: {$age}s, Attempts: {$job->attempts}");
            }
        }
    }

    /**
     * Get queue status based on statistics.
     */
    protected function getQueueStatus(array $stats): string
    {
        if ($stats['failed'] > 10) {
            return '🔴 Critical';
        } elseif ($stats['failed'] > 5) {
            return '🟡 Warning';
        } elseif ($stats['pending'] > 50) {
            return '🟠 Busy';
        } else {
            return '🟢 Healthy';
        }
    }

    /**
     * Display detailed queue information.
     */
    protected function displayDetailedQueueInfo(string $queueName): void
    {
        $this->info("\n📋 Detailed Information for {$queueName}:");

        // Oldest pending job
        $oldestJob = DB::table('jobs')
            ->where('queue', $queueName)
            ->orderBy('created_at', 'asc')
            ->first(['id', 'created_at', 'attempts']);

        if ($oldestJob) {
            $age = now()->diffInMinutes(\Carbon\Carbon::createFromTimestamp($oldestJob->created_at));
            $this->line("Oldest pending job: ID {$oldestJob->id}, Age: {$age} minutes, Attempts: {$oldestJob->attempts}");
        }

        // Failed jobs analysis
        $recentFailures = DB::table('failed_jobs')
            ->where('queue', $queueName)
            ->orderBy('failed_at', 'desc')
            ->limit(3)
            ->get(['id', 'failed_at', 'exception']);

        if ($recentFailures->isNotEmpty()) {
            $this->line("\nRecent failures:");
            foreach ($recentFailures as $failure) {
                $age = now()->diffInMinutes(\Carbon\Carbon::parse($failure->failed_at));
                $this->line("  - ID: {$failure->id}, Failed {$age} minutes ago");
                if (str_contains($failure->exception, 'PropertyCreatedMail')) {
                    $this->line("    Type: Property Created Email");
                }
            }
        }
    }

    /**
     * Display system overview.
     */
    protected function displaySystemOverview(): void
    {
        $this->info("\n🏗️  System Overview:");

        $totalJobs = DB::table('jobs')->count();
        $totalFailed = DB::table('failed_jobs')->count();
        $totalReserved = DB::table('jobs')->whereNotNull('reserved_at')->count();

        $this->line("Total jobs in system: {$totalJobs}");
        $this->line("Total failed jobs: {$totalFailed}");
        $this->line("Total reserved jobs: {$totalReserved}");

        // Queue worker status (basic check)
        $this->info("\n👷 Queue Workers:");
        $this->line("To start workers: php artisan queue:work");
        $this->line("To monitor in real-time: php artisan queue:work --verbose");
        $this->line("To process specific queue: php artisan queue:work --queue=emails");
    }
}
