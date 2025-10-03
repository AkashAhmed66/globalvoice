<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Messages\App\Models\Outbox;
use Modules\Messages\App\Trait\SmsCountTrait;

class RetryRequestService
{
  use SmsCountTrait;
  public function handleRetryRequest(string $mobile = null): void
  {


    //query from outbox where retry_count 0 and time duration from created_at is more than 5 minutes.
    $outboxMessagesForRetry1 = Outbox::where('retry_count', 0)
      ->where('created_at', '>', now()->subMinutes(5))
      ->whereNotIn('dlr_status_code', [200, 1001, 1002])
      ->get();

    //query from outbox where retry_count 1 and time duration from created_at is more than 15 minutes.
    $outboxMessagesForRetry2 = Outbox::where('retry_count', 1)
      ->where('created_at', '>', now()->subMinutes(15))
      ->whereNotIn('dlr_status_code', [200, 1001, 1002])
      ->get();

    //query from outbox where retry_count 2 and time duration from created_at is more than 30 minutes.
    $outboxMessagesForRetry3 = Outbox::where('retry_count', 2)
      ->where('created_at', '>', now()->subMinutes(30))
      ->whereNotIn('dlr_status_code', [200, 1001, 1002])
      ->get();


    //merge all the outbox messages
    $outboxMessages = $outboxMessagesForRetry1->merge($outboxMessagesForRetry2)
      ->merge($outboxMessagesForRetry3);


    if (empty($outboxMessages)) {
      Log::channel('socketlisten')->info("No messages to retry.");
    }

    $ids = [];
    $retryHistory = [];
    foreach ($outboxMessages as $outboxMessage) {
      $countSmsInfo = $this->countSms($outboxMessage->message);
      if ($outboxMessage->is_unicode) $encoding = 8;
      else $encoding = 0;

      $socketData = [
        'messageId' => (string)$outboxMessage->id,
        'senderId' => $outboxMessage->srcmn,
        'userId' => $outboxMessage->user_id,
        'recepient' => $outboxMessage->destmn,
        'type' => 'single',
        'encoding' => $encoding,
        "parts" => (array)$countSmsInfo->parts,
        "has_gsm_extended" => 0,
        "no_of_sms" => (int)$countSmsInfo->count
      ];

      $dataString = json_encode($socketData) . "\n";
      app(SocketService::class)->sendData($dataString);

      $retryHistory[] = [
        'outbox_id' => $outboxMessage->id,
        'mobile' => $outboxMessage->destmn,
        'user_id' => $outboxMessage->user_id,
        'current_dlr_code' => $outboxMessage->dlr_status_code,
        'current_dlr_status' => $outboxMessage->dlr_status,
        'retry_at' => now(),
      ];

      $ids[] = $outboxMessage->id;
    }

    // when done
    app(SocketService::class)->disconnect();

    //save retry history
    if (count($retryHistory) > 0) {
      DB::table('message_retry_history')->insert($retryHistory);

      //UPDATE retry_count in outbox
      $cases = "";
      foreach ($ids as $id) {
        $cases .= "WHEN $id THEN `retry_count` + 1 ";
      }

      $sql = "
          UPDATE `outbox` SET
          `retry_count` = CASE `id`
              $cases
          END
          WHERE `id` IN (" . implode(',', $ids) . ")
      ";

      DB::statement($sql);

    }
  }
}
