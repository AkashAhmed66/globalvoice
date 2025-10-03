<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Messages\App\Models\Outbox;

class InfozilionDLRStatusUpdateJob implements ShouldQueue
{
    use Queueable;
    protected $outbox;
    /**
     * Create a new job instance.
     */
    public function __construct($outbox)
    {
        $this->outbox = $outbox;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('infozilionStatusUpdate')->info("Infozilion DLR Status Update Job started.");
        foreach ($this->outbox as $messageInfo) {
            $status = $messageInfo->dlr_status_code == 200 ? 'Delivered' : 'Undelivered';

            for ($key = 0; $key < $messageInfo->smscount; $key++) {
                if (!$messageInfo->sms_uniq_id)
                    continue;
                $parts = explode('-', $messageInfo->sms_uniq_id);
                $last_part = end($parts);
                $incremented = str_pad((int) $last_part + $key, strlen($last_part), '0', STR_PAD_LEFT);
                $parts[count($parts) - 1] = $incremented;
                $new_str = implode('-', $parts);

                Log::channel('infozilionStatusUpdate')->info("Sending DLR for message ID: {$new_str} with status: {$status} to Infozilion. Payload: " . json_encode([
                    "username" => env('DLR_USERNAME'),
                    "password" => env('DLR_PASSWORD'),
                    "messageId" => $new_str,
                    "status" => $status,
                    "errorCode" => "0",
                    "mobile" => $messageInfo->destmn,
                    "shortMessage" => $messageInfo->message,
                    "submitDate" => $messageInfo->created_at,
                    "doneDate" => date('Y-m-d H:i:s'),
                ]));
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
                    Log::channel('infozilionStatusUpdate')->info("DLR Response from infozilion after sending the status update: " . $result);

                    $messageInfo->infozillion_dlr_status_sent = 1;
                    $messageInfo->save();

                    Log::channel('infozilionStatusUpdate')->info("Found " . $messageInfo->id . " messages for Infozillion DLR status update.");


                } catch (\Exception $e) {
                    Log::channel('infozilionStatusUpdate')->error("DLR Error: " . now() . ' - ' . $e->getMessage());
                }
            }
        }
    }
}
