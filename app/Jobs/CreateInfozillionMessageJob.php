<?php

namespace App\Jobs;

use App\Models\InfozillionMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateInfozillionMessageJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected array $data;
  public $tries = 3;
  public $timeout = 10;
  /**
   * Create a new job instance.
   */
  public function __construct(array $data)
  {
    $this->data = $data;
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    try {
      InfozillionMessage::create($this->data);
    } catch (\Exception $e) {
      Log::error("InfozillionMessage job failed: " . $e->getMessage());
      throw $e;
    }
  }
}
