<?php
namespace Modules\Dashboard\App\Repositories;
use Illuminate\Database\Eloquent\Collection;
use Modules\Dashboard\App\Models\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Transactions\App\Models\UserWallet;
use Modules\Users\App\Models\User;
use Modules\Messages\App\Models\Message;
use Modules\Messages\App\Models\Outbox;
use Modules\Smsconfig\App\Models\SenderId;
use Modules\Transactions\App\Models\Transaction;
use Carbon\Carbon;

class DashboardRepository implements DashboardRepositoryInterface
{
  protected $model;
  private $user_id;

  public function __construct(Dashboard $model)
  {
    $this->model = $model;
    $this->user_id = Auth::user()->id ?? null;
    $this->user_group_id = Auth::user()->user_group_id ?? null;
  }

  public function all(array $filters = []): Collection
  {
    $query = $this->model->query();

    if (isset($filters['title'])) {
      $query->where('title', 'like', '%' . $filters['title'] . '%');
    }

    if (isset($filters['content'])) {
      $query->where('content', 'like', '%' . $filters['content'] . '%');
    }

    return $query->get();
  }

  public function create(array $data): Dashboard
  {
    return $this->model->create($data);
  }

  public function update(array $data, int $id): Dashboard
  {
    $dashboard = $this->model->find($id);
    $dashboard->update($data);

    return $dashboard;
  }

  public function find(int $id): Dashboard
  {
    return $this->model->find($id);
  }

  public function delete(int $id): bool
  {
    return $this->model->destroy($id);
  }

  public function countTotalUsers(): int
  {
    if($this->user_group_id != 1){
      return User::where('created_by', $this->user_id)->count();
    }else{
      return User::count();
    }
  }

  public function calculateUserPercentage()
  {
    $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
    $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
    $currentMonthStart = Carbon::now()->startOfMonth();
    $currentMonthEnd = Carbon::now()->endOfMonth();

    $result = User::where('id', $this->user_id)
      ->selectRaw("
            SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as last_month_users,
            SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as current_month_users
        ", [$lastMonthStart, $lastMonthEnd, $currentMonthStart, $currentMonthEnd])
      ->first();

    $lastMonthUsers = $result->last_month_users;
    $currentMonthUsers = $result->current_month_users;

    if ($lastMonthUsers == 0) {
      $percentageChange = $currentMonthUsers > 0 ? 100 : 0;
    } else {
      $percentageChange = (($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100;
    }

    if ($currentMonthUsers > $lastMonthUsers) {
      $formattedPercentageChange = '+' . round($percentageChange, 2) . '%';
    } elseif ($currentMonthUsers < $lastMonthUsers) {
      $formattedPercentageChange = '-' . round(abs($percentageChange), 2) . '%';
    } else {
      $formattedPercentageChange = '0%'; // No change if the numbers are equal
    }


    return [
      'last_month_users' => $lastMonthUsers,
      'current_month_users' => $currentMonthUsers,
      'percentage_change' => $formattedPercentageChange
    ];
  }

  public function countDailyTotalMessages(): int
  {
    return 0;
  }

  public function countTotalMessages(): int
  {
    return 0;
  }
  public function calculateMessagePercentage()
  {
    return [
      'last_month_message' => $lastMonthMessage = 0,
      'current_month_message' => $currentMonthMessage = 0,
      'percentage_change' => $formattedPercentageChange = '0%'
    ];
  }

  public function countTotalTransections(): int
  {
    return 0;
  }

  public function calculateTransecionPercentage()
  {

    return [
      'last_month_amount' => $lastMonthAmount = 0,
      'current_month_amount' => $currentMonthAmount = 0,
      'percentage_change' => $formattedAmountPercentageChange = '0%'
    ];

  }


  public function countTotalSenderId(): int
  {
    return 0;
  }

  public function calculateSenderIdPercentage()
  {

    return [
      'last_month_amount' => $lastMonthAmount = 0,
      'current_month_amount' => $currentMonthAmount = 0,
      'percentage_change' => $formattedAmountPercentageChange = '0%'
    ];
  }

  // public function getStatusWiseMessages(array $filters = []): Collection
  // {

  //   $query = Outbox::query();

  //   $query->whereDate('created_at', Carbon::today()->toDateString());

  //   $totalMessages = $query->count();
  //   $messageCounts = $query->select('dlr_status_code', DB::raw('count(*) as count'))
  //     ->groupBy('dlr_status_code')
  //     ->get();
  //   $messageCountsWithPercentage = $messageCounts->map(function ($item) use ($totalMessages) {
  //     $item->percentage = round(($item->count / $totalMessages) * 100, 2);
  //     return $item;
  //   });

  //   return $messageCountsWithPercentage;
  // }

  // public function getLast7DaysTransections(array $filters = []): Collection
  // {

  //   $transactionsQuery  = UserWallet::selectRaw('DATE(created_at) as date, DATE_FORMAT(created_at, "%a") as day, COUNT(*) as total_transactions, SUM(balance) as total_amount')
  //     ->where('created_at', '>=', Carbon::now()->subDays(7));
	//   if($this->user_group_id !=1 || $this->user_group_id !=2){
	// 	  $transactionsQuery ->where('user_id', $this->user_id);
	//   }

  //     $transactions = $transactionsQuery->groupByRaw('DATE(created_at), DATE_FORMAT(created_at, "%a")')
	// 	  ->orderByRaw('DATE(created_at)')
	// 	  ->get();

  //   return $transactions;
  // }

  // public function getLast7DaysTransectionsAmount()
  // {

	// $currentAmount = UserWallet::selectRaw('SUM(balance) as total_amount')
	// 	  ->where('created_at', '>=', Carbon::now()->subDays(7))
	// 	  ->where('created_at', '<=', Carbon::now());
	//   if($this->user_group_id !=1 || $this->user_group_id !=2){
	// 	  $currentAmount->where('user_id', $this->user_id);
	//   }
  //   $currentAmount = $currentAmount->first();

  //   $previousAmount = UserWallet::selectRaw('SUM(balance) as total_amount')
  //     ->where('created_at', '>=', Carbon::now()->subDays(14))
  //     ->where('created_at', '<', Carbon::now()->subDays(7));
	//   if($this->user_group_id !=1 || $this->user_group_id !=2){
	// 		  $previousAmount->where('user_id', $this->user_id);
	// 	  }
  //     $previousAmount = $previousAmount->first();

  //   // Calculate the percentage change
  //   $currentTotalAmount = $currentAmount->total_amount ?? 0;
  //   $previousTotalAmount = $previousAmount->total_amount ?? 0;

  //   if ($previousTotalAmount == 0) {
  //     $percentageChange = $currentTotalAmount > 0 ? 100 : 0; // If previous amount is 0, and current is greater than 0, it's 100%.
  //   } else {
  //     $percentageChange = (($currentTotalAmount - $previousTotalAmount) / $previousTotalAmount) * 100;
  //   }

  //   // Format the percentage change with a + or - sign
  //   $formattedPercentageChange = $percentageChange > 0
  //     ? '+' . round($percentageChange, 2) . '%'
  //     : ($percentageChange < 0
  //       ? '-' . round(abs($percentageChange), 2) . '%'
  //       : '0%');

  //   // Return the result
  //   return [
  //     'current_total_amount' => $currentTotalAmount,
  //     'previous_total_amount' => $previousTotalAmount,
  //     'percentage_change' => $formattedPercentageChange
  //   ];
  // }

  // public function getLast7DaysMessages(array $filters = []): Collection
  // {

  //   $messagesQuery = Outbox::selectRaw('DATE(created_at) as date, DATE_FORMAT(created_at, "%a") as day, COUNT(*) as total_messages')
	// 	->where('created_at', '>=', Carbon::now()->subDays(7))
	// 	->groupByRaw('DATE(created_at), DATE_FORMAT(created_at, "%a")');

	// 	if($this->user_group_id !=1 || $this->user_group_id !=2){
	// 		$messagesQuery->where('user_id', $this->user_id);
	// 	}

	// $messages = $messagesQuery->get();

	// return $messages;

  // }

  // public function getLast7DaysMessagesTotal()
  // {

  //   $currentCount = Outbox::selectRaw('COUNT(*) as total_count')
	// 	  ->where('created_at', '>=', Carbon::now()->subDays(7))
	// 	  ->where('created_at', '<=', Carbon::now());
	//   if($this->user_group_id !=1 || $this->user_group_id !=2){
	// 	  $currentCount->where('user_id', $this->user_id);
	//   }
  //   $currentCount = $currentCount->first();


  //   $previousCount = Outbox::selectRaw('COUNT(*) as total_count')
	// 	  ->where('created_at', '>=', Carbon::now()->subDays(14))
	// 	  ->where('created_at', '<', Carbon::now()->subDays(7));
	//   if($this->user_group_id !=1 || $this->user_group_id !=2){
	// 	  $previousCount->where('user_id', $this->user_id);
	//   }
	// $previousCount = $previousCount->first();

  //   $currentTotalCount = $currentCount->total_count ?? 0;
  //   $previousTotalCount = $previousCount->total_count ?? 0;

  //   if ($previousTotalCount == 0) {
  //     $percentageChange = $currentTotalCount > 0 ? 100 : 0;
  //   } else {
  //     $percentageChange = (($currentTotalCount - $previousTotalCount) / $previousTotalCount) * 100;
  //   }

  //   $formattedPercentageChange = $percentageChange > 0
  //     ? '+' . round($percentageChange, 2) . '%'
  //     : ($percentageChange < 0
  //       ? '-' . round(abs($percentageChange), 2) . '%'
  //       : '0%');

  //   return [
  //     'current_total_count' => $currentTotalCount,
  //     'previous_total_count' => $previousTotalCount,
  //     'percentage_change' => $formattedPercentageChange
  //   ];
  // }

  public function countTotalSentMessages(): int
  {
      $today = Carbon::today();
      return 0;
  }

  public function countTotalFailedMessages()
  {
      //Get Reason
      return 0;

  }

}
