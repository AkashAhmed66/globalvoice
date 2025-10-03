<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Messages\App\Trait\AggregatorTrait;

class CheckMessageDeliveryStatusJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
  use AggregatorTrait;

  protected $data;
  protected $orderId;
  protected $sendMessageId;
  public function __construct(array $data, $orderId, $sendMessageId)
  {
    $this->data = $data;
    $this->orderId = $orderId;
    $this->sendMessageId = $sendMessageId;
  }

  public function handle()
  {
    $result = $this->checkDelivery($this->data, $this->orderId, $this->sendMessageId);
  }
}
