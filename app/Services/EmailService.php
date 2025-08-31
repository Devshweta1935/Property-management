<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Jobs\SendPropertyCreatedEmail;
use App\Mail\PropertyCreatedMail;
use App\Models\Property;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send a property created email notification.
     */
    public function sendPropertyCreatedEmail(Property $property, string $recipientEmail): void
    {
        try {
            Log::info('Dispatching property created email job', [
                'property_id' => $property->id,
                'recipient_email' => $recipientEmail
            ]);

            // Dispatch the specific job for property created emails
            SendPropertyCreatedEmail::dispatch($property, $recipientEmail);

            Log::info('Property created email job dispatched successfully', [
                'property_id' => $property->id,
                'recipient_email' => $recipientEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to dispatch property created email job', [
                'property_id' => $property->id,
                'recipient_email' => $recipientEmail,
                'error' => $e->getMessage()
            ]);

            // Re-throw to let the calling code handle the error
            throw $e;
        }
    }

    /**
     * Send a generic email using the generic email job.
     */
    public function sendEmail(\Illuminate\Mail\Mailable $mailable, string $recipientEmail, string $emailType = 'general'): void
    {
        try {
            Log::info('Dispatching generic email job', [
                'email_type' => $emailType,
                'recipient_email' => $recipientEmail
            ]);

            // Dispatch the generic email job
            SendEmailJob::dispatch($mailable, $recipientEmail, $emailType);

            Log::info('Generic email job dispatched successfully', [
                'email_type' => $emailType,
                'recipient_email' => $recipientEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to dispatch generic email job', [
                'email_type' => $emailType,
                'recipient_email' => $recipientEmail,
                'error' => $e->getMessage()
            ]);

            // Re-throw to let the calling code handle the error
            throw $e;
        }
    }

    /**
     * Send multiple emails in batch.
     */
    public function sendBatchEmails(array $emails): void
    {
        foreach ($emails as $email) {
            if (!isset($email['mailable'], $email['recipient_email'])) {
                Log::warning('Invalid email data in batch', ['email' => $email]);
                continue;
            }

            $this->sendEmail(
                $email['mailable'],
                $email['recipient_email'],
                $email['email_type'] ?? 'batch'
            );
        }
    }

    /**
     * Check if email service is available.
     */
    public function isAvailable(): bool
    {
        // You could add health checks here
        // - Check queue connection
        // - Check mail configuration
        // - Check if queue workers are running
        return true;
    }
}
