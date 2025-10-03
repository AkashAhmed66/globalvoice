<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Messages\App\Models\Message;
use Modules\Messages\App\Models\Outbox;
use Modules\Messages\App\Trait\SmsCountTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InfozillionApiResponseJob implements ShouldQueue
{
  use SmsCountTrait;
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $requestData;
  protected $user;
  protected $isPromotional;

  public function __construct(array $requestData, $user, $isPromotional)
  {
    $this->requestData = $requestData;
    $this->user = $user;
    $this->isPromotional = $isPromotional;
  }

  public function handle()
  {
    //$this->send($this->requestData, $this->user, $this->isPromotional);

    Log::channel('sms')->info('InfozillionApiResponseJob started for user: ' . $this->user->id . ' with CLI: ' . $this->requestData['cli'] );

    if ($this->requestData['cli'] == '1000000000000'){
      Log::channel('sms')->info('CLI is 1000000000000, skipping job for user: ' . $this->user->id);
    }else{
      try {
        DB::beginTransaction();
        $totalMessage = $this->countSms($this->requestData['message'])->count;
        $recipientList = explode(',', $this->requestData['msisdn']);
        $totalRecipient = count($recipientList);

        //$requestIp = \Request::getClientIp() ?? "";
        $orderId = $this->user->id . $this->microseconds();

        //PREPARE PAYLOAD FOR SEND MESSAGE TABLE
        $payload = [
          "user_id" => $this->user->id,
          "recipient" => $this->requestData['msisdn'],
          "senderid" => $this->requestData['cli'],
          "message" => $this->requestData['message'],
          "client_transaction_id" => $this->requestData['clienttransid'],
          "orderid" => $orderId,
          "rn_code" => strrev($this->requestData['rn_code']),
          "type" => $this->requestData['type'] ?? '',
          "long_sms" => $this->requestData['message'] ?? '',
          "is_long_sms" => $this->requestData['isLongSMS'] ?? false,
          "is_flash" => $this->requestData['isFlash'] ?? false,
          "flash" => $this->requestData['flash']?? '',
          "date" => date('Y-m-d H:i:s'),
          "source" => 'IPTSP',
          "sms_count" => $totalMessage,
          "IP" => $requestIp ?? "",
          "sms_type" => 'sendSms',
          "file" => '',
          "is_unicode" => $this->requestData['isUnicode'] ?? false,
          "unicode" => $this->requestData['unicode'] ?? '',
          "data_coding" => $this->requestData['dataCoding'] ?? '',
          "status" => 'Queue',
          "total_recipient" => $totalRecipient,
          "is_promotional" => $this->isPromotional,
          "template_type" => $this->requestData['template_type'] ?? '1',
        ];

        $sendMessage = Message::create($payload);

        $priority = $this->getPriority($recipientList);

        $outboxPayloadArray = [];
        $messageIDs = [];

        foreach ($recipientList as $key => $destmn) {
          $messageDateTime = date('YmdHis');
          $prefix = getenv('MESSAGE_PREFIX');

          $outboxPayload = [
            "srcmn" => $this->requestData['cli'],
            "mask" => $this->requestData['cli'],
            "destmn" => trim($destmn),
            "message" => $this->requestData['message'],
            "country_code" => null,
            "operator_prefix" => strrev($this->requestData['rn_code']),
            "status" => 'Queue',
            "created_at" => date('Y-m-d H:i:s'),
            "sent_time" => null,
            "ton" => 5,
            "npi" => 1,
            "message_type" => 'text',
            "is_unicode" => $this->requestData['isUnicode'] ?? false,
            "smscount" => $totalMessage,
            "esm_class" => '',
            "data_coding" => $this->requestData['dataCoding'] ?? '',
            "reference_id" => $sendMessage->id,
            "last_updated" => date('Y-m-d H:i:s'),
            "schedule_time" => null,
            "retry_count" => 0,
            "user_id" => $this->user->id,
            "remarks" => '',
            "uuid" => hex2bin(str_replace('-', '', Str::uuid()->toString())),
            "priority" => $priority,
            "blocked_status" => null,
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
            "error_code" => null,
            "error_message" => null,
            "sms_cost" => doubleval(@$this->user->smsRate->nonmasking_rate),
            "sms_uniq_id" => $prefix . " " . $messageDateTime . '-' . trim($destmn) . '-' . '0000000001',
            "rn_code" => strrev($this->requestData['rn_code']),
            "type" => $this->requestData['type']
          ];

          $outboxPayloadArray[] = $outboxPayload;

          for ($i = 1; $i <= $totalMessage; $i++) {
            $messageIDs[trim($destmn)][] = $prefix . ' ' .
              $messageDateTime . '-' .
              trim($destmn) . '-' . sprintf('%010d', $i);
          }
        }

        //SAVE OUTBOX TABLE
        Outbox::insert($outboxPayloadArray);

        $totalCostForAllMessage = doubleval(@$this->user->smsRate->nonmasking_rate * intval($totalMessage * $totalRecipient));
        $this->user->available_balance -= $totalCostForAllMessage;
        $this->user->save();

        DB::commit();

      } catch (\Exception $e) {
        DB::rollBack();
        //dd($e->getMessage());
        //Log::channel('sms')->info($e->getMessage());
      }
    }
  }

  public function microseconds()
  {
    $mt = explode(' ', microtime());
    return intval($mt[1] * 1E6) + intval(round($mt[0] * 1E6));
  }
}

