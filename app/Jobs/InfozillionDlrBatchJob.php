<?php

// app/Jobs/InfozillionDlrBatchJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Client\Pool as HttpPool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Messages\App\Models\Outbox;

class InfozillionDlrBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int[] */
    public array $ids;

    public $timeout = 300; // allow enough time for a 100+ batch
    public $tries = 3;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function handle(): void
    {
        Log::channel('infozilionStatusUpdate')->info('DLR batch job start', ['count' => count($this->ids)]);

        /** @var Collection<int, Outbox> $rows */
        $rows = Outbox::whereIn('id', $this->ids)->get()->keyBy('id');

        // Prepare expanded "parts" (messageId per smscount)
        $items = [];
        foreach ($rows as $o) {

            $status = ((int) $o->dlr_status_code === 200) ? 'Delivered' : 'Undelivered';
            if (!$o->sms_uniq_id)
                continue;

            for ($k = 0; $k < max(1, (int) $o->smscount); $k++) {
                $items[] = [
                    'row_id' => $o->id,
                    'messageId' => $this->incrementSuffix($o->sms_uniq_id, $k),
                    'status' => $status,
                    'destmn' => $o->destmn,
                    'message' => $o->message,
                    'submitAt' => $o->created_at?->format('Y-m-d H:i:s'),
                ];
            }
        }


        // Chunk requests to cap concurrency (e.g., 100 at a time)
        $chunkSize = 100;
        foreach (array_chunk($items, $chunkSize) as $chunk) {

            $responses = Http::timeout(seconds: 10)
                ->connectTimeout(5)
                ->pool(function (HttpPool $pool) use ($chunk) {
                    return collect($chunk)->map(function ($it) use ($pool) {
                        $payload = [
                            "username" => env('DLR_USERNAME'),
                            "password" => env('DLR_PASSWORD'),
                            "messageId" => $it['messageId'],
                            "status" => $it['status'],
                            "errorCode" => $it['status'] === 'Delivered' ? "0" : "1",
                            "mobile" => $it['destmn'],
                            "shortMessage" => $it['message'],
                            "submitDate" => $it['submitAt'],
                            "doneDate" => now()->format('Y-m-d H:i:s'),
                        ];

                        return $pool
                            ->as($it['messageId'])
                            ->retry(5, 1000, throw: false) // retry without throwing
                            ->post(env('DLR_URL'), $payload);
                    })->all();
                });

            // Handle results safely
            foreach ($responses as $messageId => $resp) {
                try {
                    if ($resp instanceof \Illuminate\Http\Client\Response && $resp->successful()) {
                        Log::channel('infozilionStatusUpdate')->info('DLR ok', [
                            'messageId' => $messageId,
                            'status' => $resp->status(),
                            'body' => $resp->body(),
                        ]);
                    } else {
                        $code = $resp instanceof \Illuminate\Http\Client\Response ? $resp->status() : 0;
                        $body = $resp instanceof \Illuminate\Http\Client\Response ? $resp->body() : null;

                        Log::channel('infozilionStatusUpdate')->error('DLR failed', [
                            'messageId' => $messageId,
                            'status' => $code,
                            'body' => $body,
                            'error' => $resp instanceof \Throwable ? $resp->getMessage() : null,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::channel('infozilionStatusUpdate')->error('DLR exception', [
                        'messageId' => $messageId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        }

        // Mark rows as SENT if all went through for that row
        // (Conservative: mark sent even if some parts failed? Better: require all parts success.
        // Below marks sent; adjust if you want per-part strictness.)
        DB::table('outbox')
            ->whereIn('id', $this->ids)
            ->update([
                'infozillion_dlr_status_sent' => 1,
                'infozillion_dlr_status_processing' => false,
                'updated_at' => now(),
            ]);

        Log::channel('infozilionStatusUpdate')->info('DLR batch job done', ['count' => count($this->ids)]);
    }

    private function incrementSuffix(string $smsUniqId, int $offset): string
    {
        $parts = explode('-', $smsUniqId);
        $last = end($parts);
        if (!ctype_digit((string) $last))
            return $smsUniqId; // fallback, no change
        $next = str_pad(((int) $last) + $offset, strlen($last), '0', STR_PAD_LEFT);
        $parts[count($parts) - 1] = $next;
        return implode('-', $parts);
    }

    public function failed(\Throwable $e): void
    {
        // Release processing flag so a future batch can retry
        DB::table('outbox')->whereIn('id', $this->ids)->update([
            'infozillion_dlr_status_processing' => false,
            'updated_at' => now(),
        ]);
        Log::channel('infozilionStatusUpdate')->error('DLR batch job failed', [
            'error' => $e->getMessage(),
            'count' => count($this->ids),
        ]);
    }
}

