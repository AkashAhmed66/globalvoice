<?php

namespace App\Jobs;

use App\Queries\OutboxQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Reports\App\Models\Export;
use Illuminate\Support\Facades\Log;
use Throwable;
use ZipArchive;

class ExportOutboxCsvZipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Map DLR status codes to status strings.
     */
    protected static array $dlrStatusMap = [
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

    public int $tries = 3;
    public int $timeout = 3600; // 1 hour

    public function __construct(public int $exportId) {}

    public function handle(): void
    {
        /** @var Export $export */
        $export = Export::findOrFail($this->exportId);
        if ($export->status === 'canceled') return;

        $export->update(['status' => 'running', 'rows_written' => 0]);

        $filters = $export->filters;
        $columns = $export->columns ?: [
            'id', 'destmn', 'mask', 'message', 'created_at', 'smscount', 'sms_cost','dlr_status_code'
        ];

        // Working paths
        $dir = storage_path("app/exports/{$export->user_id}/{$export->id}");
        if (!is_dir($dir)) mkdir($dir, 0775, true);


        //directory name datetime
        $datetime = now()->format('Ymd_His');
        $csvPath = "{$dir}/{$datetime}_{$export->id}.csv";
        $zipPath = "{$dir}/{$datetime}_{$export->id}.zip";

        // Create CSV (streaming)
        $fh = fopen($csvPath, 'w');
        // Optional: BOM for Excel-friendly UTF-8
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, $columns);

        $chunk = 10000; // tune 5k–20k based on env
        $count = 0;

        Log::channel('exportLog')->info("Export started", [
            'format' => 'csv',
            'columns' => $columns,
            'filters' => $filters
        ]);

        $filters['auth_user_id'] = $export->user_id;

        /** @var Builder $query */
        $query = OutboxQuery::build($filters, $columns);

        // Use chunkById for memory safety
        $query->chunkById($chunk, function ($rows) use ($fh, $columns, $export, &$count) {
            foreach ($rows as $r) {
                $line = [];
                foreach ($columns as $c) {
                    $val = $r->$c ?? null;

                    // Format created_at as Y-m-d H:i:s
                    if ($c === 'created_at' && $val !== null) {
                        $val = \Carbon\Carbon::parse($val)->format('Y-m-d H:i:s');
                    }

                    // Map dlr_status_code to status string
                    if ($c === 'dlr_status_code') {
                        $val = self::$dlrStatusMap[$val]['status'] ?? $val;
                    }

                    // Normalize newlines
                    $line[] = is_string($val) ? preg_replace("/\r\n|\r|\n/u", " ", $val) : $val;
                }
                fputcsv($fh, $line);
            }

            $batch = count($rows);
            $count += $batch;
            $export->increment('rows_written', $batch);
        });


        fclose($fh);

        // Zip the CSV
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot create zip at {$zipPath}");
        }
        $zip->addFile($csvPath, basename($csvPath));
        $zip->close();

        // Store on local disk (storage/app)
        $storagePath = "exports/{$export->user_id}/{$export->id}/{$datetime}_{$export->id}.zip";
        // Move file under storage/app path
        if (!is_dir(storage_path('app/exports/'.$export->user_id.'/'.$export->id))) {
            @mkdir(storage_path('app/exports/'.$export->user_id.'/'.$export->id), 0775, true);
        }
        // Copy zip into storage/app path
        copy($zipPath, storage_path('app/'.$storagePath));

        // Optionally remove the raw csv after zipping
        @unlink($csvPath);

        $export->update([
            'status' => 'completed',
            'storage_path' => $storagePath,
            'available_until' => now()->addDays(7),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Export::whereKey($this->exportId)->update([
            'status' => 'failed',
            'error' => $e->getMessage(),
        ]);
    }
}
