<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMaskedMessageJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected string $company;
  protected array $recipients;
  protected string $message;
  protected string $messageType;
  protected string $cli;

  public function __construct(string $company, array $recipients, string $message, string $messageType, string $cli)
  {
    $this->company = strtolower($company); // e.g., grameenphone, banglalink
    $this->recipients = $recipients;
    $this->message = $message;
    $this->messageType = $messageType; // Default message type, can be overridden if needed
    $this->cli = $cli;
  }

  public function handle(): void
  {
    match ($this->company) {
      'grameenphone' => $this->sendViaGrameenphone(),
      'banglalink' => $this->sendViaBanglalink(),
      'robi' => $this->sendViaRobi(),
      'teletalk' => $this->sendViaTeletalk(),
      'airtel' => $this->sendViaAirtel(),
      default => Log::warning("Unknown company: {$this->company}"),
    };
  }

  protected function sendViaGrameenphone(): void
  {
    $chunks = collect($this->recipients)
      ->map(function ($number) {
        // Ensure number is prefixed with 0 (not 88 or +88)
        $number = preg_replace('/^\+?88/', '', $number);
        return preg_match('/^0\d+$/', $number) ? $number : '0' . ltrim($number, '0');
      })
      ->chunk(100); // Max 100 per API call

    foreach ($chunks as $chunk) {
      $formattedRecipients = $chunk->implode(','); // comma-separated string

      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
      ])->post(env('GRAMEENPHONE_API_URL'), [
        "username"     => env('GRAMEENPHONE_API_USERNAME'),
        "password"     => env('GRAMEENPHONE_API_PASSWORD'),
        "apicode"      => "5",
        "msisdn"       => $formattedRecipients,
        "countrycode"  => "880",
        "cli"          => $this->cli,
        "messagetype"  => $this->messageType ?? "1",
        "message"      => $this->message,
        "messageid"    => "0",
      ]);

      $this->logResponse('Grameenphone', $response);
    }
  }

  protected function sendViaBanglalink(): void
  {
    $response = Http::post('https://example.com/banglalink/api', [
      'api_key' => 'your_api_key',
      'recipient' => $this->recipients,
      'message' => $this->message,
    ]);

    $this->logResponse('Banglalink', $response);
  }

  protected function sendViaRobi(): void
  {
    // implement Robi API logic
  }

  protected function sendViaTeletalk(): void
  {
    // implement Teletalk API logic
  }

  protected function sendViaAirtel(): void
  {
    // implement Airtel API logic
  }

  protected function logResponse(string $provider, $response): void
  {
    if ($response->successful()) {
      Log::info("{$provider} message sent", $response->json());
    } else {
      Log::error("{$provider} message failed", [
        'status' => $response->status(),
        'body' => $response->body(),
      ]);
    }
  }
}
