<?php


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class IncommingMessageSaveApiJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $payload;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($payload)
  {
    $this->payload = $payload;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      $client = new Client();
      $client->post(env("GENNET_INCOMING_MESSAGES_STORE_URL"), [
        'json' => $this->payload
      ]);
      Log::channel('socketlisten')->info("Incoming message send successfully");
    } catch (\Exception $e) {
      Log::channel('socketlisten')->error("Error sending incoming message: " . $e->getMessage());
    }
  }
}
