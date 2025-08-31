<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartQueues extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'queue:start {--emails-only : Start only email queue worker}';

    /**
     * The console command description.
     */
    protected $description = 'Start queue workers with helpful instructions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Property Management Backend Queue Workers');
        $this->info('==========================================');

        if ($this->option('emails-only')) {
            $this->info("\n📧 To start email queue worker, run:");
            $this->line('   php artisan queue:work --queue=emails --verbose');
        } else {
            $this->info("\n📧 To start email queue worker, run:");
            $this->line('   php artisan queue:work --queue=emails --verbose');
            
            $this->info("\n🔄 To start default queue worker, run:");
            $this->line('   php artisan queue:work --verbose');
            
            $this->info("\n🌐 To start all queues, run:");
            $this->line('   php artisan queue:work');
        }

        $this->info("\n📋 Other useful commands:");
        $this->line('   - Monitor queues: php artisan queue:monitor');
        $this->line('   - View failed jobs: php artisan queue:failed');
        $this->line('   - Retry failed jobs: php artisan queue:retry all');
        $this->line('   - Clear failed jobs: php artisan queue:flush');

        $this->info("\n💡 Tips:");
        $this->line('   - Use --verbose flag for detailed output');
        $this->line('   - Use --tries=1 for development (no retries)');
        $this->line('   - Use --timeout=60 for longer job processing');
        $this->line('   - Use --memory=512 for memory limit control');

        $this->info("\n🛑 To stop workers, use Ctrl+C in the terminal");

        return Command::SUCCESS;
    }
}
