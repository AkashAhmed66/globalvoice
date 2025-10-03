<?php

namespace App\Queries;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function Illuminate\Log\log;

class OutboxQuery
{
    /**
     * Build a performant, sargable query. Ensure indexed where-clauses.
     */
    public static function build(array $filters, array $columns)
    {
        $q = DB::table('outbox as o')
            ->select(
                'o.id',
                'o.user_id',
                'o.destmn',
                'o.mask',
                'o.message',
                'o.created_at',
                'o.smscount',
                'o.sms_cost',
                'o.dlr_status_code'
            )
            ->whereBetween('o.created_at', [
                \Carbon\Carbon::parse($filters['date_from'])->toDateTimeString(),
                \Carbon\Carbon::parse($filters['date_to'])->toDateTimeString()
            ]);
        if($filters['type'] == 'archive_regular' || $filters['type'] == 'archive_failed') {
            $q = DB::table('outbox_history as o')
                ->select(
                    'o.id',
                    'o.user_id',
                    'o.destmn',
                    'o.mask',
                    'o.message',
                    'o.created_at',
                    'o.smscount',
                    'o.sms_cost',
                    'o.dlr_status_code'
                )
                ->whereBetween('o.created_at', [
                    \Carbon\Carbon::parse($filters['date_from'])->toDateTimeString(),
                    \Carbon\Carbon::parse($filters['date_to'])->toDateTimeString()
                ]);
        }

        $currentUser = User::find($filters['auth_user_id'] ?? Auth::id());

        if ($currentUser->user_group_id == 2) {
            $userIds = DB::table('users')
                ->where('created_by', $currentUser->id)
                ->pluck('id')
                ->toArray();
            $userIds[] = $currentUser->id;
            $q->whereIn('o.user_id', $userIds);
        } else if ($currentUser->user_group_id == 3) {
            $q->where('o.user_id', $currentUser->id);
        }

        if (!empty($filters['user_id'])) {
            $q->where('o.user_id', $filters['user_id'] ?? $currentUser->id);
        }
        if (!empty($filters['mask'])) {
            $q->where('o.mask', $filters['mask']);
        }
        if (!empty($filters['operator_prefix'])) {
            $q->where('o.operator_prefix', $filters['operator_prefix']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $q->where('o.status', $filters['status']);
        }
        if (!empty($filters['destmn'])) {
            $q->where('o.destmn', $filters['destmn']);
        }
        if ($filters['type'] == 'regular_failed' || $filters['type'] == 'archive_failed') {
            $q->where('o.dlr_status_code', '!=', 200);
        }

        Log::channel('exportLog')->info("sql and bindings", [
            'sql' => $q->toSql(),
            'bindings' => $q->getBindings()
        ]);

        return $q->orderBy('o.id'); // stable ordering for chunking
    }

}
