<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Transactions\App\Models\UserWallet;

class PaymentSuccessJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $userId = null;
    private $amount = null;

    public function __construct($customerId, $amount)
    {
        $this->userId = $customerId;
        $this->amount = $amount;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $user = User::find($this->userId);

            UserWallet::create(
                [
                'user_id' => $this->userId,
                'balance' => $this->amount,
                'balance_type' => 'Debit',
                'transaction_via' => 'online'
                ]
            );

            if ($user) {
                $user->available_balance += $this->amount;
                $user->save();
            }

            //IF USER HAS PARENT USER THEN UPDATE PARENT USER BALANCE
            if ($user->created_by) {
                $parentUser = User::find($user->created_by);
                if ($parentUser) {

                //CALCULATE PARENT USER BALANCE FOR NON MASKING MESSAGES
                $parentUserSmsRate = $parentUser->smsRate->nonmasking_rate;
                $userSmsRate = $user->smsRate->nonmasking_rate;

                $totalUserSms = $this->amount / $userSmsRate;
                $parentUserCost = $totalUserSms * $parentUserSmsRate;
                $parentUserProfit = $this->amount - $parentUserCost;

                UserWallet::create(
                    [
                    'user_id' => $parentUser->id,
                    'balance' => $parentUserProfit,
                    'balance_type' => 'Debit',
                    'transaction_via' => 'online'
                    ]
                );

                $parentUser->available_balance += $parentUserProfit;
                $parentUser->save();
                }
            }
            DB::commit(); 
            } catch (\Exception $exception) {
            Log::channel('payment')->error('Error updating user balance: ' . $exception->getMessage());
            DB::rollBack();
            }
    }
}