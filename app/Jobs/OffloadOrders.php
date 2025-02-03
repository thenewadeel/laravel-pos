<?php

namespace App\Jobs;

use App\Http\Controllers\OrderHistoryController;
use App\Models\JobLog;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class OffloadOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 20;
    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 6;
    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 3;
    /**
     * Create a new job instance.
     */
    protected $jobLogId;
    public function __construct(public Collection $orders, public User $user, public bool $optionalSwitch = true)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobId = $this->job->getJobId();
        if ($jobId) { // If the job is queued
            $jobLog = JobLog::where('job_id', $jobId)->first();
            if ($jobLog) {
                $this->jobLogId = $jobLog->id;
                $jobLog->status = 'processing';
                $jobLog->save();
            } else {
                // Store the job details and user ID in the database
                $jobLog = new JobLog();
                $jobLog->user_id = $this->user->id;
                $jobLog->job_name = 'OffloadOrders';
                $jobLog->job_id = $jobId; // Store the job ID
                $jobLog->status = 'pending';
                $jobLog->save();
                $this->jobLogId = $jobLog->id;
            }
        }
        // ... your podcast processing logic ...
        $batchSize = 10;
        $orders = $this->orders->chunk($batchSize);
        foreach ($orders as $key => $batch) {
            Log::debug("Processing batch #{$key} of {$batchSize}");
            foreach ($batch as $order) {
                if (!$order->isBurnt()) {

                    // Log::alert($this->order);
                    if ($this->optionalSwitch) {
                        Log::debug("Executing base job for order with POS # " . $order->POS_number);
                        $order->bakeOrder();
                        $order->save();
                    } else {
                        Log::debug("Executing unimplemented job for order with POS # ");
                    }
                } else {
                    Log::warning("Burnt order, skipping ");
                }
            }
            if ($this->jobLogId) { // If the job is queued
                $jobLog = JobLog::find($this->jobLogId);
                $jobLog->progress = (($key + 1) * $batchSize) / $this->orders->count() * 100;
                $jobLog->save();
            }
        }
        if ($this->jobLogId) { // If the job is queued
            $jobLog = JobLog::find($this->jobLogId);
            $jobLog->status = 'completed';
            $jobLog->save();
        }
    }
}
