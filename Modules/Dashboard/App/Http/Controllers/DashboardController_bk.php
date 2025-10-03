<?php

namespace Modules\Dashboard\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\App\Repositories\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Modules\Users\App\Models\User;
use Modules\Messages\App\Models\Message;
use Modules\Messages\App\Models\Outbox;

class DashboardController extends Controller
{

  protected DashboardRepositoryInterface $dashboardRepository;

  public function __construct(DashboardRepositoryInterface $dashboardRepository)
  {
    $this->dashboardRepository = $dashboardRepository;
	$this->user_id = Auth::user()->id ?? null;
	$this->user_group_id = Auth::user()->user_group_id ?? null;
  }

  public function index()
  {
    $totalUsers = $this->dashboardRepository->countTotalUsers();
    $userStatusCalculate = $this->dashboardRepository->calculateUserPercentage();

    $totalMessages = $this->dashboardRepository->countTotalMessages();
    $messageStatusCalculate = $this->dashboardRepository->calculateMessagePercentage();

    $totalTransections = $this->dashboardRepository->countTotalTransections();
    $transectionStatusCalculate = $this->dashboardRepository->calculateTransecionPercentage();

    $totalSenderId = $this->dashboardRepository->countTotalSenderId();
    $senderIdStatusCalculate = $this->dashboardRepository->calculateSenderIdPercentage();
    //dd($senderIdStatusCalculate);

    $statusWiseMessages = $this->dashboardRepository->getStatusWiseMessages();

    $statusList = ['Failed', 'Delivered', 'Sent', 'Processing', 'Queue', 'Hold'];
    $currentYear = Carbon::now()->year;
    $monthlyStatusCounts = collect();

    foreach (range(1, 12) as $month) {

      $monthlyCounts = DB::table('outbox')
        ->select(DB::raw('MONTH(created_at) as month'), 'status', DB::raw('count(*) as count'))
        ->whereYear('created_at', $currentYear)
        ->whereMonth('created_at', $month)
        ->whereIn('status', $statusList)
		->when($this->user_group_id != 1 || $this->user_group_id != 2, function ($query) {
			return $query->where('user_id', $this->user_id);
		})
        ->groupBy(DB::raw('MONTH(created_at)'), 'status')
        ->get();


      $statusCountsForMonth = [];
      foreach ($statusList as $status) {
        $statusCount = $monthlyCounts->firstWhere('status', $status);
        $statusCountsForMonth[$status] = $statusCount ? $statusCount->count : 0;
      }
      $monthlyStatusCounts->put($month, $statusCountsForMonth);
    }

    $last7DaysTransections = $this->dashboardRepository->getLast7DaysTransections();
    $last7DaysTransectionsAmount = $this->dashboardRepository->getLast7DaysTransectionsAmount();

    $last7DaysMessages = $this->dashboardRepository->getLast7DaysMessages();
    $last7DaysMessagesTotal = $this->dashboardRepository->getLast7DaysMessagesTotal();

    $totalSentMessages = $this->dashboardRepository->countTotalSentMessages();
    $totalFailedMessages = $this->dashboardRepository->countTotalFailedMessages();
    $userInfo = Auth::user();
    $userSmsRate = $userInfo ? $userInfo->smsRate() : null;

    $remainingNonmaskingmessageCount =  round($userInfo->available_balance / ($userSmsRate ? $userInfo->smsRate->nonmasking_rate : 1));
    $remainingMaskingMessageCount = round($userInfo->available_balance / ($userSmsRate ? $userInfo->smsRate->masking_rate : 1));
    $api_key = $userInfo->apikey;


    //dd($totalFailedMessages);
    return view(
      'dashboard::superadmin',
      compact(
        'totalUsers', 'totalMessages',
        'totalTransections', 'totalSenderId', 'last7DaysMessagesTotal',
        'monthlyStatusCounts', 'last7DaysTransections',
        'last7DaysMessages', 'userStatusCalculate', 'last7DaysTransectionsAmount',
        'messageStatusCalculate', 'transectionStatusCalculate', 'senderIdStatusCalculate',
        'userInfo', 'totalSentMessages', 'totalFailedMessages',
        'remainingNonmaskingmessageCount', 'remainingMaskingMessageCount', 'api_key'
      )
    );
  }

  public function getStatusWiseMessage(Request $request)
  {
    $range = $request->input('range');
    $query = Outbox::query();
	if($this->user_group_id !=1 || $this->user_group_id != 2){
	  $query->where('user_id', $this->user_id);
	}
    switch ($range) {
      case 'today':
        $query->whereDate('created_at', Carbon::today());
        break;
      case 'yesterday':
        $query->whereDate('created_at', Carbon::yesterday()->toDateString());
        break;
      case 'last_7_days':
        $query->where('created_at', '>=', Carbon::now()->subDays(7));
        break;
      case 'last_30_days':
        $query->whereDate('created_at', '>=', Carbon::now()->subDays(30)->toDateString());
        break;
      case 'current_month':
        $query->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year);
        break;
      case 'last_month':
        $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
          ->whereYear('created_at', Carbon::now()->subMonth()->year);
        break;
      default:
        break;
    }

    $baseQuery = clone $query;
    $totalMessages = $query->count();

    if ($totalMessages > 0) {
      $statusList = ['Failed', 'Delivered', 'Sent', 'Processing', 'Queue', 'Hold'];
      $messageCounts = collect();

      foreach ($statusList as $status) {

        $statusQuery = clone $baseQuery;
        $count = $statusQuery->where('status', $status)->count();

        $messageCounts->push((object)[
          'status' => $status,
          'count' => $count,
          'percentage' => round(($count / $totalMessages) * 100)
        ]);
      }
    } else {

      $messageCounts = collect();
      $statusList = ['Failed', 'Delivered', 'Sent', 'Processing', 'Queue', 'Hold'];
      foreach ($statusList as $status) {
        $messageCounts->push((object)[
          'status' => $status,
          'count' => 0,
          'percentage' => 0
        ]);
      }
    }

    $messageCountsWithPercentage = $messageCounts;

    return response()->json($messageCountsWithPercentage)->header('Content-Type', 'application/json; charset=utf-8');
  }

}
