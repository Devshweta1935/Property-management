<?php

namespace App\Jobs;

use App\Mail\PropertyCreatedMail;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPropertyCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60; // 60 seconds timeout
    public $tries = 3; // Retry up to 3 times
    public $maxExceptions = 2; // Max exceptions before marking as failed

    /**
     * The property instance.
     */
    protected Property $property;

    /**
     * The recipient email address.
     */
    protected string $recipientEmail;

    /**
     * Create a new job instance.
     */
    public function __construct(Property $property, string $recipientEmail)
    {
        $this->property = $property;
        $this->recipientEmail = $recipientEmail;
        
        // Set queue name for better organization
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting to send property created email', [
                'job_id' => $this->job->getJobId(),
                'property_id' => $this->property->id,
                'recipient_email' => $this->recipientEmail,
                'queue' => $this->queue
            ]);

            // Send the email
            Mail::to($this->recipientEmail)->send(new PropertyCreatedMail($this->property));

            Log::info('Property created email sent successfully', [
                'job_id' => $this->job->getJobId(),
                'property_id' => $this->property->id,
                'recipient_email' => $this->recipientEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send property created email', [
                'job_id' => $this->job->getJobId(),
                'property_id' => $this->property->id,
                'recipient_email' => $this->recipientEmail,
                'error' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            // Re-throw the exception so the job can be retried
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Property created email job failed permanently', [
            'job_id' => $this->job->getJobId(),
            'property_id' => $this->property->id,
            'recipient_email' => $this->recipientEmail,
            'error' => $exception->getMessage(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine()
        ]);

        // Here you could implement additional failure handling:
        // - Send notification to admin
        // - Store in failed emails table
        // - Trigger alternative notification method
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryAfter(): int
    {
        // Exponential backoff: 30s, 60s, 120s
        return 30 * (2 ** ($this->attempts() - 1));
    }
}
