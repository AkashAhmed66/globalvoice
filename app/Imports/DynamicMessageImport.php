<?php

namespace App\Imports;

use App\Jobs\SendMaskedMessageJob;
use App\Jobs\SendMessageToInfozillionJob;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Messages\App\Trait\AggregatorTrait;
use Modules\Messages\App\Trait\ReveApiTrait;
use Modules\Messages\App\Trait\SmsCountTrait;
use Maatwebsite\Excel\Row;
use Modules\Messages\App\Models\Outbox;
use Illuminate\Support\Str;
use Modules\Messages\App\Models\SMSRecord;

class DynamicMessageImport implements OnEachRow, WithChunkReading, WithHeadingRow, ShouldQueue
{
  use AggregatorTrait;
  use ReveApiTrait;
  use SmsCountTrait;

  protected $messageResponse;
  protected $client;
  protected $user;
  protected $dnd;
  protected $dndnumbers;
  protected $excelHeaders;
  protected $updated_message;

  public function __construct($messageResponse, int $userId = null, $dnd = null, $dndnumbers = [], $excelHeaders = [])
  {
    $this->messageResponse = $messageResponse;
    $this->user = User::find($userId);
    $this->dnd = $dnd;
    $this->dndnumbers = $dndnumbers;
    $this->excelHeaders = $excelHeaders;
  }

  public function onRow(Row $row) {
    Log::channel('import')->info("Processing row for message import 1");

    $row = $row->toArray();
    $phone = $row['phone'] ?? $row[0] ?? null;
    $numbers = [];

    if (
      !empty($phone) &&
      (preg_match('/^\d{11}$/', $phone) || preg_match('/^\d{13}$/', $phone))
    ) {

      if (!str_starts_with($phone, '88')) {
        $phone = '88' . $phone;
      }

      if (str_starts_with($phone, '+88')) {
        $phone = substr($phone, 1); // Remove the '+' only
      } elseif (!str_starts_with($phone, '88')) {
        $phone = '88' . $phone;
      }

      Log::channel('import')->info("Processing row for message import phone: " . $phone);
      Log::channel('import')->info("Message content: " . $this->messageResponse);
      Log::channel('import')->info("headers: " . json_encode($this->excelHeaders));
      Log::channel('import')->info("row content 0: " . $row["phone"]);

      $dynamicMessage = $this->getCustomMessage($this->messageResponse->message, $this->excelHeaders, $row);

      $this->updated_message = $dynamicMessage;

      Log::channel('import')->info("Dynamic message: " . $dynamicMessage);

      if(!$this->dnd || !in_array($phone, $this->dndnumbers)) {
        $numbers[] = $phone;
  
        $outboxMessage = Outbox::create([
          "srcmn" => $this->messageResponse->senderid,
          "mask" => $this->messageResponse->senderid,
          "destmn" => trim($phone),
          "message" => $dynamicMessage,
          "country_code" => '880',
          "operator_prefix" => $this->getPrefix($phone),
          "status" => 'Queue',
          "created_at" => date('Y-m-d H:i:s'),
          "sent_time" => null,
          "ton" => 5,
          "npi" => 1,
          "message_type" => 'text',
          "is_unicode" =>  $this->messageResponse->is_unicode == 1 ? 1 : 0,
          "smscount" => $this->messageResponse->sms_count,
          "esm_class" => '',
          "data_coding" => $this->messageResponse->is_unicode == 1 ? 8 : 0,
          "reference_id" => $this->messageResponse->id,
          "last_updated" => date('Y-m-d H:i:s'),
          "schedule_time" => null,
          "retry_count" => 0,
          "user_id" => $this->user->id,
          "remarks" => '',
          "uuid" => hex2bin(str_replace('-', '', Str::uuid()->toString())),
          "priority" => $this->messageResponse->priority ?? 0,
          "blocked_status" => null,
          "created_at" => date('Y-m-d H:i:s'),
          "updated_at" => date('Y-m-d H:i:s'),
          "error_code" => null,
          "error_message" => null,
          // "dlr_status_code" => null,
          "dlr_status" => null,
          "dlr_status_meaning" => null,
          "sms_cost" => doubleval(($this->user->smsRate->nonmasking_rate ?? 0) * $this->messageResponse->sms_count),
          "sms_uniq_id" => getenv('MESSAGE_PREFIX') . " " . date('Ymdhis') . '-' . trim($phone) . '-' . '0000000001',
        ]);
      } else {
        Log::channel('sms')->info("Phone number is in DND list: " . $phone);
        return;
      }

      /*if (getenv('APP_TYPE') !== 'Aggregator' && !getenv('IS_API_BASED')) {
        Log::channel('sms')->info("!Aggregator & !IS_API_BASED");
        $this->sendToSocket($outboxMessage);
      }*/

      Log::channel('sms')->info("Message sent using : " . $outboxMessage->mask);

     if (is_null($this->messageResponse->scheduleDateTime)) {
        if (env('APP_TYPE') == 'Aggregator') {
          // if(($this->messageResponse->masking_type == '1')){
          //   $company = "grameenphone"; //Will be changed later
          //   $recipients = $numbers;
          //   $message = $dynamicMessage;
          //   $messageType = 1;
          //   $cli = $this->messageResponse->senderid;
          //   if($this->messageResponse->content_type == 'Flash'){
          //     $messageType = 2;
          //   }else{
          //     if($this->messageResponse->is_unicode == '1'){
          //       $messageType = 3;
          //     }
          //   }
          //   SendMaskedMessageJob::dispatch($company, $recipients, $message, $messageType, $cli);
          // } else {
            if($this->messageResponse->senderid == '10000000000000000'){
              Log::channel('sms')->info("Sending to Smsc Api");
              $this->sendToSmscApi($outboxMessage);
            } else {
              Log::channel('sms')->info("Sending to Infozillion");
              $this->sendMessageToInfozilion($numbers, $this->messageResponse->id, $this->messageResponse->senderid);
            }
          // }
        }
      }
    }
  }

  private function getCustomMessage($syntaxedMessage, $excelHeaders, $row) {
    foreach ($excelHeaders as $item) {
        // Replace {ColumnName} with the value from $row
        $syntaxedMessage = str_replace("{" . $item . "}", $row[$item] ?? '', $syntaxedMessage);
    }

    return $syntaxedMessage;
  }


  private function sendMessageToInfozilion($numbers, $sendMessageId, $senderid)
  {
    try {
      $infozillion_array = array('msisdnList' => $numbers, 'message' => $this->updated_message, 'campaign_id' => $this->messageResponse->campaign_id);
      $infozillion_array['transactionType'] = count($numbers) > 1 ? 'P' : 'T';
      // $infozillion_array['transactionType'] = 'T';
      $infozillion_array['cli'] = $senderid;
      $infozillion_array['isunicode'] = $this->messageResponse->is_unicode == '1' ? '1' : '0';

      //$this->sendMessageToInfozilion($infozillion_array);

      SendMessageToInfozillionJob::dispatch($infozillion_array, $sendMessageId);

      return true;
    } catch (\Exception $exception) {
      Log::channel('sms')->error("Error sending message to Infozillion: " . $exception->getMessage(), [
        'infozillion_array' => $infozillion_array,
        'sendMessageId' => $sendMessageId,
        'senderid' => $senderid,
      ]);
      return false;
    }
  }

  private function sendToReveApi($outboxMessage)
  {
    try {
      $response = Http::get(env('SMS_API_URL'), [
        'apikey' => $outboxMessage->user->user_reve_api_key, //env('SMS_API_KEY'),
        'secretkey' => $outboxMessage->user->user_reve_secret_key, //env('SMS_SECRET_KEY'),
        'content' => json_encode([
          [
            'callerID' => $outboxMessage->mask,
            'toUser' => $outboxMessage->destmn,
            'messageContent' => $outboxMessage->message,
          ]
        ]),
      ]);
    } catch (\Exception $e) {
      Log::channel('sms')->info($e->getMessage());
    }

    if ($response->successful()) {
      try {
        $responsedata = $response->json();
        $messageIds = explode(',', $responsedata['Message_ID']);
        $messageId = $messageIds[0];

        $payload = [
          'user_id' => $outboxMessage->user_id,
          'outbox_id' => $outboxMessage->id,
          'status' => $responsedata['Status'],
          'text' => $responsedata['Text'],
          'message_id' => $messageId,
          'created_at' => now(),
          'updated_at' => now(),
        ];

        SMSRecord::insert($payload);
      } catch (\Exception $e) {
        Log::channel('sms')->info($e->getMessage());
      }
    }

    return true;
  }


  public function sendToSmscApi($outboxMessage)
  {
    $payload = [
      "username" => 'LoadTest',
      "password" => '123456',
      "cli" => '1000000000000',
      "msisdn" => $outboxMessage->destmn,
      "message" => $outboxMessage->message,
      "clienttransid" => $outboxMessage->id . '' . time(),
      "rn_code" => '71',
      "type" => 'P',
      "longSMS" => '',
      "isLongSMS" => false,
      "dataCoding" => $outboxMessage->data_coding ?? '0',
      "isUnicode" => $outboxMessage->is_unicode,
      "unicode" => '',
      "isFlash" => false,
      "flash" => ''
    ];

    try {
      if (empty($outboxMessage->destmn) || empty($outboxMessage->message)) {
        Log::channel('sms')->info('Recipient or message content is missing.', ['message' => $outboxMessage->message]);
        return false;
      }

      Log::channel('sms')->info('Sending load test SMS to ' . $outboxMessage->destmn, ['payload' => $payload]);

      //$response = Http::post('https://smsc.metro.net.bd/api/v2/load-test/promo/sendsms', $payload);

      //https://smsc.metro.net.bd:8443/api/v2/promo/sendsms

      $client = app(Client::class);
      $response = $client->post('https://smsc.metro.net.bd/api/v2/load-test/promo/sendsms', [
        'json' => $payload,
      ]);

      Log::channel('sms')->info("Response from SmscApi: " . $response->getBody());

      if ($response->getStatusCode() !== 200) {
        Log::channel('sms')->error("Failed to send load test SMS. Status code: " . $response->getStatusCode(), [
          'payload' => $payload,
        ]);
        return false;
      }
    } catch (\Exception $e) {
      Log::channel('sms')->error("Error sending load test SMS: " . $e->getMessage(), [
        'exception' => $e,
        'payload' => $payload,
      ]);
    }

    return true;
  }

  public
    function chunkSize(
  ): int {
    return 500; // Process 1000 rows at a time
  }
}
