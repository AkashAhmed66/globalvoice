<?php

namespace Modules\Dashboard\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Users\App\Models\User;

class DashboardController_bk_v1 extends Controller
{
    public function index()
    {

        $last90Days = Carbon::now()->subDays(90);
        $messageStatusQuery = DB::table('sentmessages')
            ->join('outbox', 'sentmessages.id', '=', 'outbox.reference_id')
            ->join('users', 'sentmessages.user_id', '=', 'users.id')
            ->select('sentmessages.user_id', 'users.name',
                DB::raw("SUM(outbox.status = 'Failed') as failed_count"),
                DB::raw("SUM(outbox.status = 'Delivered') as delivered_count"),
                DB::raw("SUM(outbox.status = 'Sent') as sent_count"),
                DB::raw("SUM(outbox.status = 'Processing') as processing_count"),
                DB::raw("SUM(outbox.status = 'Queue') as queue_count"),
                DB::raw("SUM(outbox.status = 'Hold') as hold_count")
            )
            ->where('sentmessages.created_at', '>=', $last90Days);
            if (Auth::user()->user_group_id != 1) {
                $messageStatusQuery->where('sentmessages.user_id', Auth::user()->id);
            }
            $messageStatusQuery->groupBy('sentmessages.user_id');
            $message_status = $messageStatusQuery->get();


          $messageRequestQuery = DB::table('sentmessages')
              ->join('users', 'sentmessages.user_id', '=', 'users.id')
              ->select('sentmessages.user_id', 'users.name',
                      DB::raw('count(case when sentmessages.source = "WEB" then 1 end) as web_count'),
                      DB::raw('count(case when sentmessages.source = "API" then 1 end) as api_count'),
                      DB::raw('count(case when sentmessages.source = "IPTSP" then 1 end) as iptsp_count'))
              ->where('sentmessages.created_at', '>=', $last90Days);
              if (Auth::user()->user_group_id != 1) {
                  $messageRequestQuery->where('sentmessages.user_id', Auth::user()->id);
              }
              $messageRequestQuery->groupBy('sentmessages.user_id');
              $message_request = $messageRequestQuery->get();

        $balance_info = User::where('id', Auth::user()->id)->first();
        //$sms_rate = Rate::where('id', $balance_info->sms_rate_id)->first();
        //$sms_rate = DB::table('rates')->where('id', $balance_info->sms_rate_id)->first();

        //$masking_balance = round($balance_info->available_balance / $sms_rate->masking_rate);
        //$nonmasking_balance = round($balance_info->available_balance / $sms_rate->nonmasking_rate);

        //return view('dashboard::superadmin', compact('message_status', 'message_request', 'nonmasking_balance', 'masking_balance', 'balance_info'));
        return view('dashboard::superadmin', compact('message_status', 'message_request', 'balance_info'));

    }
}
