<?php

namespace App\Jobs;

use App\Services\RetryRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessRetryRequest implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $mobile;

  public function __construct($mobile)
  {
    $this->mobile = $mobile;
  }

  public function handle(RetryRequestService $retryRequestService)
  {
    try {
      DB::table('retry_request_number')->insert([
        'mobile' => $this->mobile,
        'retry_at' => now(),
      ]);

      $retryRequestService->handleRetryRequest($this->mobile);
    } catch (\Exception $e) {
      Log::channel('socketlisten')->error("ProcessRetryRequest error: " . $e->getMessage());
    }
  }
}
