<?php

namespace App\Imports;

use Modules\Phonebook\App\Models\Phonebook;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactImport implements OnEachRow, WithChunkReading, WithHeadingRow, ShouldQueue
{
  protected $groupId;
  protected $userId;

  public function __construct($groupId, $userId)
  {
    $this->groupId = $groupId;
    $this->userId = $userId;
  }

  public function onRow(Row $row)
  {
    $row = $row->toArray();

    $phone = $row['phone'] ?? $row[0] ?? null; // Column name or first column

    if (!empty($phone) &&
      (preg_match('/^\d{11}$/', $phone) || preg_match('/^\d{13}$/', $phone))) {
      Phonebook::create([
        'group_id' => $this->groupId,
        'phone' => $phone,
        'user_id' => $this->userId,
      ]);
    }
  }

  public function chunkSize(): int
  {
    return 1000; // Process 1000 rows at a time
  }
}
