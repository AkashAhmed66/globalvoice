<?php

namespace App\Jobs;

use Modules\Messages\App\Models\Inbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Messages\App\Trait\SmsCountTrait;

class HandleIncomingMessage implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
  use SmsCountTrait;
  protected array $messageParts;

  public function __construct(array $messageParts)
  {
    $this->messageParts = $messageParts;
  }

  public function handle(): void
  {
    try {
      $payload = [
        'sender'          => $this->messageParts[1],
        'operator_prefix' => $this->getPrefix($this->messageParts[1]),
        'receiver'        => preg_match('/^4700/', $this->messageParts[2])
          ? preg_replace('/^4700/', '88', $this->messageParts[2], 1)
          : $this->messageParts[2],
        'message'         => $this->messageParts[3] ?? '',
        'smscount'        => $this->messageParts[4] ?? 1,
        'part_no'         => $this->messageParts[5] ?? 1,
        'total_parts'     => $this->messageParts[6] ?? 1,
        'reference_no'    => $this->messageParts[7] ?? 0,
        'read'             => $this->messageParts[8] ?? 0,
      ];

      Inbox::create($payload);

      IncommingMessageSaveApiJob::dispatch($payload);

    } catch (\Exception $e) {
      Log::channel('socketlisten')->error("Error saving inbox message: " . $e->getMessage());
    }
  }
}
