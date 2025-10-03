<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Messages\App\Models\Outbox;
use Modules\Messages\App\Models\OutboxHistory;
use Modules\Users\App\Models\User;

class MessageStatusUpdateService
{
  public function handleUpdate(array $messageParts): void
  {
    $messageId = $messageParts[1];
    $statusCode = (int)$messageParts[2];
    $now = Carbon::now();

    $status = $this->getMessageStatusByCode($statusCode);
    $dlrStatusData = $this->getDlrStatusInfo($statusCode);

    $outboxSingleMessage = Outbox::find($messageId);
    if (!$outboxSingleMessage) {
      Log::channel('socketlisten')->info("Outbox message not found");
      return;
    }

    $user = User::find($outboxSingleMessage->user_id);
    $smsRate = (@$user->smsRate->nonmasking_rate ?? 0) * $outboxSingleMessage->smscount;

    $infozillionStatusCode = null;
    $infozillionStatusMeaning = null;

    if (!in_array($statusCode, [1001, 1002])) {
      $infozillionDlrCallbackResponse = $this->infozillionDlrCallback($user, $outboxSingleMessage, $statusCode);

      if ($infozillionDlrCallbackResponse) {
        $data = json_decode($infozillionDlrCallbackResponse, true);
        $infozillionStatusCode = $data['statusInfo']['statusCode'] ?? null;
        $infozillionStatusMeaning = $data['statusInfo']['errordescription'] ?? null;
      }
    }

    try {
      $updateData = [
        'status' => $status,
        'sent_time' => $now,
        'sms_cost' => $smsRate,
        'error_code' => 0,
        'error_message' => 'No error',
        'updated_at' => $now,
        'dlr_status_code' => $dlrStatusData['code'] == 0 ? 200 : $dlrStatusData['code'],
        'dlr_status' => $dlrStatusData['status'],
        'dlr_status_meaning' => $dlrStatusData['meaning'],
        'infozillion_dlr_status_code' => $infozillionStatusCode,
        'infozillion_dlr_status_meaning' => $infozillionStatusMeaning,
      ];

      Outbox::where('id', $messageId)->update($updateData);
      OutboxHistory::where('id', $messageId)->update($updateData);

      if ($statusCode === 0) {
        $user->available_balance -= $smsRate;
        $user->updated_at = $now;
        $user->save();
      }

      $this->info("Message status updated: {$outboxSingleMessage->destmn} | {$dlrStatusData['status']} | {$dlrStatusData['code']}");

      Log::channel('socketlisten')->info("Message update completed: {$outboxSingleMessage->destmn} | {$messageId} | {$statusCode}");
    } catch (\Exception $e) {
      Log::channel('socketlisten')->error("DB update error: " . $e->getMessage());
    }
  }

  private function getMessageStatusByCode($code): string
  {
    return match ($code) {
      0 => 'Delivered',
      1001 => 'Sent',
      1002 => 'Queue',
      default => 'Failed',
    };
  }

  private function getDlrStatusInfo($code): array
  {
    $map = [
      0 => ['status' => 'Delivered', 'meaning' => 'Delivered in Handset'],
      6 => ['status' => 'Absent subscriber for SM', 'meaning' => 'Subscriber handset is not logged onto the network...'],
      32 => ['status' => 'Undelivered', 'meaning' => 'No memory capacity on handset...'],
      31 => ['status' => 'Subscriber Busy', 'meaning' => 'MSC is busy handling an existing transaction...'],
      5 => ['status' => 'Unidentified subscriber', 'meaning' => 'MT number is unknown in the MT network’s MSC'],
      13 => ['status' => 'Barred subscriber', 'meaning' => 'A Barred Number is a number that cannot receive SMS...'],
      9 => ['status' => 'Illegal subscriber', 'meaning' => 'Sender ID Blocked by operators for Illegal SMS Traffic'],
      36 => ['status' => 'SMS Failed', 'meaning' => 'Sender ID Blocked by operators for Illegal SMS Traffic'],
      34 => ['status' => 'System failure', 'meaning' => 'Rejection due to SS7 protocol or network failure'],
      8 => ['status' => 'SMS Failed', 'meaning' => 'Network failure in SMSC Link'],
      400 => ['status' => 'SMSC Timeout-abort', 'meaning' => 'SMSC timeout (Network problem)'],
      456 => ['status' => 'SMSC Timeout-abort', 'meaning' => 'SMSC timeout (Network problem)'],
      8001 => ['status' => 'SRI Response not found', 'meaning' => 'SRI Response not found'],
      9001 => ['status' => 'FSM Response not found', 'meaning' => 'FSM Response not found'],
      1 => ['status' => 'Message sent to Engine', 'meaning' => 'Message sent to Engine'],
      1001 => ['status' => 'Message received at Engine', 'meaning' => 'Message received at Engine'],
      1002 => ['status' => 'Message sent to Queue', 'meaning' => 'Message sent to Queue'],
    ];

    return [
      'code' => $code,
      'status' => $map[$code]['status'] ?? 'SMS Failed',
      'meaning' => $map[$code]['meaning'] ?? 'Network failure in SMSC Link',
    ];
  }

  private function infozillionDlrCallback($user, $messageInfo, $statusCode)
  {
    $status = $statusCode == 0 ? 'Delivered' : 'Undelivered';
    $dlrResult = '';

    for ($key = 0; $key < $messageInfo->smscount; $key++) {
      $parts = explode('-', $messageInfo->sms_uniq_id);
      $last_part = end($parts);
      $incremented = str_pad((int)$last_part + $key, strlen($last_part), '0', STR_PAD_LEFT);
      $parts[count($parts) - 1] = $incremented;
      $new_str = implode('-', $parts);

      try {
        $data = [
          "username" => env('DLR_USERNAME'),
          "password" => env('DLR_PASSWORD'),
          "messageId" => $new_str,
          "status" => $status,
          "errorCode" => "0",
          "mobile" => $messageInfo->destmn,
          "shortMessage" => $messageInfo->message,
          "submitDate" => $messageInfo->created_at,
          "doneDate" => date('Y-m-d H:i:s'),
        ];

        $options = [
          'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true
          ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents(env('DLR_URL'), false, $context);
        $dlrResult = $result ?: '';
      } catch (\Exception $e) {
        Log::channel('socketlisten')->info("DLR Error: " . now() . ' - ' . $e->getMessage());
      }
    }

    return $dlrResult;
  }
}
