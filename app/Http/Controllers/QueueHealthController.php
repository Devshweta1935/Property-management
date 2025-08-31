<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueHealthController extends Controller
{
    /**
     * Get overall queue health status.
     */
    public function health(Request $request): JsonResponse
    {
        try {
            $healthData = $this->getQueueHealthData();
            
            return response()->json([
                'success' => true,
                'status_code' => 200,
                'data' => $healthData,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Queue health check failed', [
                'error' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve queue health data',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Get detailed queue statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $queueName = $request->query('queue', 'all');
            $detailed = $request->boolean('detailed', false);
            
            if ($queueName === 'all') {
                $stats = $this->getAllQueueStats($detailed);
            } else {
                $stats = $this->getSpecificQueueStats($queueName, $detailed);
            }

            return response()->json([
                'success' => true,
                'status_code' => 200,
                'data' => $stats,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Queue stats retrieval failed', [
                'error' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve queue statistics',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Get queue health data.
     */
    protected function getQueueHealthData(): array
    {
        $totalJobs = DB::table('jobs')->count();
        $totalFailed = DB::table('failed_jobs')->count();
        $totalReserved = DB::table('jobs')->whereNotNull('reserved_at')->count();
        
        $emailsQueueJobs = DB::table('jobs')->where('queue', 'emails')->count();
        $emailsQueueFailed = DB::table('failed_jobs')->where('queue', 'emails')->count();

        $overallStatus = $this->calculateOverallStatus($totalJobs, $totalFailed, $totalReserved);
        
        return [
            'overall_status' => $overallStatus,
            'summary' => [
                'total_jobs' => $totalJobs,
                'total_failed' => $totalFailed,
                'total_reserved' => $totalReserved,
                'total_pending' => $totalJobs - $totalReserved,
            ],
            'queues' => [
                'emails' => [
                    'pending' => $emailsQueueJobs,
                    'failed' => $emailsQueueFailed,
                    'status' => $this->calculateQueueStatus($emailsQueueJobs, $emailsQueueFailed),
                ],
                'default' => [
                    'pending' => DB::table('jobs')->where('queue', 'default')->count(),
                    'failed' => DB::table('failed_jobs')->where('queue', 'default')->count(),
                    'status' => 'unknown', // Will be calculated in detailed stats
                ],
            ],
            'recommendations' => $this->generateRecommendations($totalJobs, $totalFailed, $totalReserved),
        ];
    }

    /**
     * Get all queue statistics.
     */
    protected function getAllQueueStats(bool $detailed = false): array
    {
        $queues = ['default', 'emails', 'high', 'low'];
        $stats = [];

        foreach ($queues as $queueName) {
            $stats[$queueName] = $this->getSpecificQueueStats($queueName, $detailed);
        }

        return $stats;
    }

    /**
     * Get specific queue statistics.
     */
    protected function getSpecificQueueStats(string $queueName, bool $detailed = false): array
    {
        $pending = DB::table('jobs')->where('queue', $queueName)->count();
        $reserved = DB::table('jobs')->where('queue', $queueName)->whereNotNull('reserved_at')->count();
        $failed = DB::table('failed_jobs')->where('queue', $queueName)->count();
        
        $stats = [
            'pending' => $pending,
            'reserved' => $reserved,
            'failed' => $failed,
            'status' => $this->calculateQueueStatus($pending, $failed),
        ];

        if ($detailed) {
            $stats['recent_jobs'] = $this->getRecentJobs($queueName);
            $stats['recent_failures'] = $this->getRecentFailures($queueName);
            $stats['performance_metrics'] = $this->getPerformanceMetrics($queueName);
        }

        return $stats;
    }

    /**
     * Get recent jobs for a queue.
     */
    protected function getRecentJobs(string $queueName): array
    {
        $jobs = DB::table('jobs')
            ->where('queue', $queueName)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'created_at', 'attempts']);

        return $jobs->map(function ($job) {
            return [
                'id' => $job->id,
                'age_seconds' => now()->diffInSeconds(\Carbon\Carbon::createFromTimestamp($job->created_at)),
                'attempts' => $job->attempts,
            ];
        })->toArray();
    }

    /**
     * Get recent failures for a queue.
     */
    protected function getRecentFailures(string $queueName): array
    {
        $failures = DB::table('failed_jobs')
            ->where('queue', $queueName)
            ->orderBy('failed_at', 'desc')
            ->limit(3)
            ->get(['id', 'failed_at', 'exception']);

        return $failures->map(function ($failure) {
            return [
                'id' => $failure->id,
                'age_minutes' => now()->diffInMinutes(\Carbon\Carbon::parse($failure->failed_at)),
                'exception_summary' => $this->summarizeException($failure->exception),
            ];
        })->toArray();
    }

    /**
     * Get performance metrics for a queue.
     */
    protected function getPerformanceMetrics(string $queueName): array
    {
        // Calculate average processing time (basic implementation)
        $recentCompletedJobs = DB::table('jobs')
            ->where('queue', $queueName)
            ->whereNotNull('reserved_at')
            ->orderBy('reserved_at', 'desc')
            ->limit(10)
            ->get(['created_at', 'reserved_at']);

        $totalTime = 0;
        $count = 0;

        foreach ($recentCompletedJobs as $job) {
            $created = \Carbon\Carbon::createFromTimestamp($job->created_at);
            $reserved = \Carbon\Carbon::createFromTimestamp($job->reserved_at);
            $totalTime += $created->diffInSeconds($reserved);
            $count++;
        }

        return [
            'avg_processing_time_seconds' => $count > 0 ? round($totalTime / $count, 2) : 0,
            'jobs_analyzed' => $count,
        ];
    }

    /**
     * Calculate overall queue status.
     */
    protected function calculateOverallStatus(int $totalJobs, int $totalFailed, int $totalReserved): string
    {
        if ($totalFailed > 20) {
            return 'critical';
        } elseif ($totalFailed > 10) {
            return 'warning';
        } elseif ($totalJobs > 100) {
            return 'busy';
        } else {
            return 'healthy';
        }
    }

    /**
     * Calculate queue status.
     */
    protected function calculateQueueStatus(int $pending, int $failed): string
    {
        if ($failed > 10) {
            return 'critical';
        } elseif ($failed > 5) {
            return 'warning';
        } elseif ($pending > 50) {
            return 'busy';
        } else {
            return 'healthy';
        }
    }

    /**
     * Generate recommendations based on queue health.
     */
    protected function generateRecommendations(int $totalJobs, int $totalFailed, int $totalReserved): array
    {
        $recommendations = [];

        if ($totalFailed > 10) {
            $recommendations[] = 'High number of failed jobs detected. Review failed jobs and fix underlying issues.';
        }

        if ($totalJobs > 100) {
            $recommendations[] = 'Queue is getting busy. Consider scaling up queue workers.';
        }

        if ($totalReserved > 50) {
            $recommendations[] = 'Many jobs are stuck in reserved state. Check if queue workers are running properly.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Queue system is operating normally.';
        }

        return $recommendations;
    }

    /**
     * Summarize exception for display.
     */
    protected function summarizeException(string $exception): string
    {
        $lines = explode("\n", $exception);
        $firstLine = trim($lines[0] ?? '');
        
        if (strlen($firstLine) > 100) {
            return substr($firstLine, 0, 100) . '...';
        }
        
        return $firstLine;
    }
}
