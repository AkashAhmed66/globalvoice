<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Messages\App\Trait\AggregatorTrait; // import your trait here

class SendMessageToInfozillionJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
  use AggregatorTrait;  // Use the trait here

  protected $infozillion_array;
  protected $sendMessageId;

  public function __construct(array $infozillion_array, $sendMessageId)
  {
    $this->infozillion_array = $infozillion_array;
    $this->sendMessageId = $sendMessageId;
  }

  public function handle()
  {
    // Now you can call the trait method directly
    Log::channel('sms')->info("SendMessageToInfoZillionJob: Sending message with ID {$this->sendMessageId}");
    $this->sendMessageToInfozilion($this->infozillion_array, $this->sendMessageId);
  }
}
