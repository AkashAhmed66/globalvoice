<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PaymentFailedJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    private $tranId;
    private $requestData;

    /**
     * Create a new job instance.
     */
    public function __construct($tranId, $requestData = [])
    {
        $this->tranId = $tranId;
        $this->requestData = $requestData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payment = Payment::where('order_id', $this->tranId)->first();
        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'tran_id' => $this->tranId,
                'gateway_response' => json_encode($this->requestData)
            ]);
        }
    }
}
