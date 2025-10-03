<?php

namespace App\Jobs;


use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Messages\App\Models\Outbox;

class HandleMessageStatusUpdate implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected array $messageParts;

  public function __construct(array $messageParts)
  {
    $this->messageParts = $messageParts;
  }

  public function handle(): void
  {
    $messageId = $this->messageParts[1];
    $statusCode = (int) $this->messageParts[2];
    $now = Carbon::now();

    $dlrStatusData = $this->getDlrStatusInfo($statusCode);

    $outbox = Outbox::find($messageId);
    if (!$outbox) {
      Log::channel('socketlisten')->error("Outbox message not found");
      //$this->info("Outbox message not found");
      return;
    }

    $infozillionStatusCode = null;
    $infozillionStatusMeaning = null;
    $infozillionDlrResponseDateTime = null;

    if (!in_array($statusCode, [1001, 1002])) {
      $response = $this->infozillionDlrCallback($outbox, $statusCode);
      if ($response) {
        $data = json_decode($response, true);
        $infozillionStatusCode = $data['statusInfo']['statusCode'] ?? null;
        $infozillionStatusMeaning = $data['statusInfo']['errordescription'] ?? null;
        $infozillionDlrResponseDateTime = Carbon::now();
      }
    }

    //$this->info("Message updating..");

    try {
      $outbox->update([
        'sent_time' => $now,
        'error_code' => 0,
        'error_message' => 'No error',
        'updated_at' => $now,
        'dlr_status_code' => $dlrStatusData['code'],
        'dlr_status' => $dlrStatusData['status'],
        'dlr_status_meaning' => $dlrStatusData['meaning'],
        'infozillion_dlr_status_code' => $infozillionStatusCode,
        'infozillion_dlr_status_meaning' => $infozillionStatusMeaning,
        'infozillion_dlr_request_datetime' => Carbon::now(),
        'infozillion_dlr_response_datetime' => $infozillionDlrResponseDateTime,
        'engine_dlr_response_datetime' => $now,
      ]);

      Log::channel('socketlisten')->info("Message updated: {$outbox->destmn} | {$messageId} | {$statusCode}");
      //$this->info("Message updated: {$outbox->destmn} | {$messageId} | {$statusCode}");
    } catch (\Exception $e) {
      Log::channel('socketlisten')->error("Message update error: {$outbox->destmn} | {$messageId} | {$statusCode} " . $e->getMessage());
    }
  }

  private function getDlrStatusInfo($code): array
  {
    $map = [
      200 => ['status' => 'Delivered', 'meaning' => 'Delivered in Handset'],
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
      9005 => ['status' => 'Wrong encoding', 'meaning' => 'Wrong encoding'],
    ];

    return [
      'code' => $code,
      'status' => $map[$code]['status'] ?? 'SMS Failed',
      'meaning' => $map[$code]['meaning'] ?? 'Network failure in SMSC Link',
    ];
  }

  private function infozillionDlrCallback($messageInfo, $statusCode)
  {
    $status = $statusCode == 0 ? 'Delivered' : 'Undelivered';
    $dlrResult = '';

    for ($key = 0; $key < $messageInfo->smscount; $key++) {
      $parts = explode('-', $messageInfo->sms_uniq_id);
      $last_part = end($parts);
      $incremented = str_pad((int)$last_part + $key, strlen($last_part), '0', STR_PAD_LEFT);
      $parts[count($parts) - 1] = $incremented;
      $new_str = implode('-', $parts);


      //Log::channel('socketlisten')->info('DLR_REQUEST_IP: ' . gethostbyname(gethostname()) . ' DLR_USERNAME ' . env('DLR_USERNAME') . ' DLR_PASSWORD: ' . env('DLR_PASSWORD') . ' DLR_URL: ' . env('DLR_URL'));

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
        //Log::channel('socketlisten')->info("DLR Response: " . $result);
        $dlrResult = $result ?: '';
      } catch (\Exception $e) {
        Log::channel('socketlisten')->error("DLR Error: " . now() . ' - ' . $e->getMessage());
      }
    }

    return $dlrResult;
  }
}
