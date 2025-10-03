<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutboxExportRequest;
use App\Jobs\ExportOutboxCsvZipJob;
use Modules\Reports\App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OutboxExportController extends Controller
{
    /**
     * POST /exports/outbox
     * Body: { filters:{date_from, date_to, ...}, columns?:[], format?:'csv' }
     */
    public function store(Request $request)
    {

        Log::channel('exportLog')->info('Export started'.json_encode($request->toArray()));
        $user = $request->user();

        $export = Export::create([
            'user_id' => $user->id,
            'format' => 'csv',
            'filters' => $request->input('filters'),
            'columns' => $request->input('columns', [
               'id', 'username', 'destmn', 'mask', 'message', 'created_at', 'smscount', 'nonmasking_rate', 'sms_cost','dlr_status_code'
            ]),
            'status' => 'pending',
        ]);

        dispatch(new ExportOutboxCsvZipJob($export->id))
            ->onQueue('exports');

        return response()->json(['id' => $export->id], 202);
    }

    /**
     * GET /exports/{id}
     * Returns status + download link (when completed)
     */
    public function show(Request $request, int $id)
    {
        $export = Export::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $payload = [
            'id' => $export->id,
            'status' => $export->status,
            'rows_written' => $export->rows_written,
            'error' => $export->error,
        ];

        if ($export->status === 'completed' && $export->storage_path) {
            // route-based signed URL
            $payload['download_url'] = route('exports.download', ['id' => $export->id]);
        }

        return response()->json($payload);
        
    }

    /**
     * GET /exports/{id}/download
     * Streams the .zip (local disk). Protects by ownership + status check.
     */
    public function download(Request $request, int $id): StreamedResponse
    {
        $export = Export::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($export->status === 'completed' && $export->storage_path, 404);

        // optionally enforce expiry:
        if ($export->available_until && now()->greaterThan($export->available_until)) {
            abort(410, 'Link expired');
        }

        return Storage::disk('local')->download(
            $export->storage_path,
            basename($export->storage_path),
            ['Content-Type' => 'application/zip']
        );
    }
}
