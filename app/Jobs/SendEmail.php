<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\AppMailer;
use Illuminate\Support\Facades\Mail;


class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $recipients;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $data, array $recipients)
    {
        $this->data = $data;
        $this->recipients = $recipients;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new AppMailer($this->data));
            }
            catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        // Send user notification of failure, etc...
    }
}
