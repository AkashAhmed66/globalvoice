<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Messages\App\Models\Outbox;
use Modules\Messages\App\Models\OutboxHistory;

class ArchivedSmsReportExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{
  protected $param;

  public function __construct($param = null)
  {
    $this->param = $param;
  }

  public function query()
  {
    // dd($this->param->all());
    if($this->param->report_type == 'archived' || $this->param->report_type == 'failed_archived'){
      $query = OutboxHistory::query()
      ->with(['user', 'sendMessage'])
      ->select('mask', 'destmn', 'message', 'created_at', 'last_updated', 'smscount', 'sms_cost', 'user_id');
    }else{
      $query = Outbox::query()
        ->with(['user', 'sendMessage'])
        ->select('mask', 'destmn', 'message', 'created_at', 'last_updated', 'smscount', 'sms_cost', 'user_id');
    }

    if (Auth::user()->user_group_id != 1) {
      $query->where('user_id', Auth::user()->id);
    }

    if ($this->param->message) {
      $query->where('message', 'like', '%' . $this->param->message . '%');
    }

    if ($this->param->mobile) {
      $query->where('destmn', $this->param->mobile);
    }

    if ($this->param->source) {
      $query->whereHas('sendMessage', function ($q) {
        $q->where('source', $this->param->source);
      });
    }

    if ($this->param->from_date) {
        $query->where('created_at', '>=', Carbon::parse($this->param->from_date)->startOfDay());
    }

    if ($this->param->to_date) {
        $query->where('created_at', '<=', Carbon::parse($this->param->to_date)->endOfDay());
    }

    if ($this->param->user_id) {
      $query->where('user_id', $this->param->user_id);
    }

    if ($this->param->operator) {
      if ($this->param->operator == 'gp') {
        $query->where(function ($q) {
          $q->where('operator_prefix', 'like', '%17%')
            ->orWhere('operator_prefix', 'like', '%13%');
        });
      }
      if ($this->param->operator == 'bl') {
        $query->where(function ($q) {
          $q->where('operator_prefix', 'like', '%19%')
            ->orWhere('operator_prefix', 'like', '%14%');
        });
      }
      if ($this->param->operator == 'rb') {
        $query->where(function ($q) {
          $q->where('operator_prefix', 'like', '%16%')
            ->orWhere('operator_prefix', 'like', '%18%');
        });
      }
      if ($this->param->operator == 'tt') {
        $query->where('operator_prefix', 'like', '%15%');
      }
    }

    if ($this->param->senderId) {
      $query->where('mask', $this->param->senderId);
    }
    // dd($query->getBindings(), $query->toSql());
    return $query;
  }

  public function chunkSize(): int
  {
    return 1000;
  }

  public function headings(): array
  {
    return ['Mask', 'Destination', 'Message', 'Write Time', 'Last Updated', 'SMS Count', 'SMS Cost', 'User', 'Source'];
  }

  public function map($row): array
  {
    return [
      $row->mask,
      $row->destmn,
      $row->message,
      $row->created_at,
      $row->last_updated,
      $row->smscount,
      $row->sms_cost,
      $row->user ? $row->user->name : '',
      $row->sendMessage ? $row->sendMessage->source : ''
    ];
  }
}
