<?php

namespace App\Jobs;

use App\Jobs\HandleIncomingMessage;
use App\Jobs\HandleMessageStatusUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSocketListenerMessage implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected string $message;

  public function __construct(string $message)
  {
    $this->message = $message;
  }

  public function handle(): void
  {
    try {
      $messageParts = explode("|", $this->message);
      $command = $messageParts[0] ?? null;

      if (!$command || count($messageParts) < 2) {
        Log::channel('socketlisten')->error("Malformed message: {$this->message}");
        return;
      }

      match (strtoupper($command)) {
        'SMS_STATUS' => HandleMessageStatusUpdate::dispatch($messageParts),
        'INCOMING' => HandleIncomingMessage::dispatch($messageParts),
        default => Log::channel('socketlisten')->error("Unknown command '{$command}' in message: {$this->message}"),
      };
    } catch (\Throwable $e) {
      Log::channel('socketlisten')->error("Exception in ProcessSocketListenerMessage: " . $e->getMessage());
    }
  }
}
