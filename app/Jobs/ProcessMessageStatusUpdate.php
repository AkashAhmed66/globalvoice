<?php

namespace App\Jobs;

use Illuminate\Console\Command;
use App\Services\MessageStatusUpdateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessMessageStatusUpdate implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public array $messageParts;

  public function __construct(array $messageParts)
  {
    $this->messageParts = $messageParts;
  }

  public function handle(MessageStatusUpdateService $service): void
  {
   $service->handleUpdate($this->messageParts);
  }
}
