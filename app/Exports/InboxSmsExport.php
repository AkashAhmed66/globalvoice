<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Messages\App\Models\Inbox;

class InboxSmsExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{
    protected $param;

  public function __construct($param = null)
  {
    $this->param = $param;
  }

  public function query()
  {
    $fromDate = $this->param['from_date']
        ? Carbon::parse($this->param['from_date'])->startOfDay()
        : now()->startOfMonth()->startOfDay();

    $toDate = $this->param['to_date']
        ? Carbon::parse($this->param['to_date'])->endOfDay()
        : now()->endOfMonth()->endOfDay();


    $query = Inbox::query()->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate]);

    if (Auth::user()->user_group_id != 1) {
        $senderIds = DB::table('senderid')
            ->where('user_id', Auth::user()->id)
            ->pluck('senderid');

        if ($senderIds->isEmpty()) {
            // No sender IDs means no accessible inbox messages
            // Return an empty query result by forcing a false condition
            return Inbox::query()->whereRaw('0 = 1');
        }

        $query->whereIn('receiver', $senderIds);
    }

    return $query->orderBy('created_at', 'desc');
  }

  public function chunkSize(): int
  {
    return 1000;
  }

  public function headings(): array
  {
    return ['SL No', 'Sender', 'Receiver', 'Operator', 'Message', 'Received Time'];
  }

  public function map($row): array
  {
    static $serialNumber = 0;
    $serialNumber++;
    
    return [
      $serialNumber,
      $row->sender,
      $row->receiver,
      $row->operator_prefix,
      $row->message,
      $row->created_at ? date("Y-m-d H:i:s", strtotime($row->created_at)) : ''
    ];
  }
}
