<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Messages\App\Models\Inbox;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessIncomingMessage implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $messageParts;

  public function __construct(array $messageParts)
  {
    $this->messageParts = $messageParts;
  }

  public function handle()
  {
    try {
      Inbox::create([
        'sender' => $this->messageParts[1],
        'receiver' => $this->messageParts[2],
        'message' => $this->messageParts[3] ?? '',
        'smscount' => $this->messageParts[4] ?? 1,
        'part_no' => $this->messageParts[5] ?? 1,
        'total_parts' => $this->messageParts[6] ?? 1,
        'reference_no' => $this->messageParts[7] ?? 0,
        'red' => $this->messageParts[8] ?? 0,
      ]);
    } catch (\Exception $e) {
      Log::channel('socketlisten')->error("Error saving inbox message: " . $e->getMessage());
    }
  }
}

