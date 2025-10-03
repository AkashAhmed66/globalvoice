<?php

namespace App\Http\Controllers;

use App\Jobs\PaymentCanceledJob;
use App\Jobs\PaymentFailedJob;
use App\Jobs\PaymentSuccessJob;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Transactions\App\Models\UserWallet;
use Raziul\Sslcommerz\Facades\Sslcommerz;
use Symfony\Component\Uid\Uuid;
use Modules\Users\App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
  public function initiate(Request $request)
  {
    $request->validate([
      'api_key' => 'required|string',
      'amount' => 'required|numeric|min:1',
      'callback_url' => 'required'
    ]);

    if($request->callback_url != 'metronet'){
      $request->validate([
        'callback_url' => 'url'
      ]);
    }

    //get user by api_key
    $customer = User::where('apikey', $request->api_key)->first();
    if (!$customer) {
      return response()->json(['error' => 'Invalid API key'], 401);
    }

    $response = Sslcommerz::setOrder(
      $request->amount,
      $request->order_id = Uuid::v4()->toBase32(),
      'Bulk SMS Purchase'
    )->setCustomer(
      $request->customer_name = $customer->name,
      $request->customer_email = $customer->email,
      $request->customer_phone = $customer->mobile
    )->setShippingInfo(
      1, // Assuming quantity is required and set to 1
      $request->shipping_name = $customer->name,
      $request->shipping_address = $customer->address,
      $request->shipping_city = 'Dhaka',
      $request->shipping_state = 'Dhaka',
      $request->shipping_postcode = '1212',
      $request->shipping_country = 'Bangladesh'
      )->makePayment();

    if ($response->success()) {

      Payment::create([
        'order_id' => $request->order_id,
        'customer_id' => $customer->id,
        'amount' => $request->amount,
        'status' => 'pending',
        'callback_url' => $request->callback_url
      ]);

      return response()->json(['payment_url' => $response->gatewayPageURL()]);
    }
    // return response()->json(['error' => $response], 400);
    return response('Invalid payment', 400);
  }

  public function success(Request $request)
  {
    try {
      $valid = Sslcommerz::validatePayment(
        $request->all(),
        $request->tran_id,
        $request->amount
      );

      if ($valid) {
        $payment = Payment::where('order_id', $request->tran_id)->first();
        if ($payment) {
          $payment->update([
            'status' => 'paid',
            'tran_id' => $request->tran_id,
            'gateway_response' => json_encode($request->all())
          ]);

          PaymentSuccessJob::dispatch($payment->customer_id, $payment->amount);
          sleep(3);
          if($payment->callback_url == 'metronet'){
            return redirect()->route('dashboard')->with('success', 'Payment completed successfully');
          }else{
            $callbackUrl = $payment->callback_url;
            $params = http_build_query([
                'status' => 'success'
            ]);
            // dd($callbackUrl . '?' . $params);
            return redirect()->away($callbackUrl . '?' . $params);
          }
        }

        return response()->json(['status' => 'error', 'message' => 'Payment record not found'], 404);
      }

      return response()->json(['status' => 'error', 'message' => 'Invalid payment'], 400);

    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Payment processing failed'], 500);

    }
  }

  public function failure(Request $request)
  {
    try {
      $payment = Payment::where('order_id', $request->tran_id)->first();
      if ($payment) {
        // Update payment status directly here for faster response
        $payment->update([
          'status' => 'failed',
          'tran_id' => $request->tran_id,
          'gateway_response' => json_encode($request->all())
        ]);

        // Dispatch job for any additional processing
        PaymentFailedJob::dispatch($request->tran_id, $request->all());
        sleep(1);
        // Handle callback URL similar to success function
        if($payment->callback_url == 'metronet'){
          return redirect()->route('dashboard')->with('error', 'Payment failed. Please try again.');
        } else {
          $callbackUrl = $payment->callback_url;
          $params = http_build_query([
              'status' => 'failed'
          ]);
          return redirect()->away($callbackUrl . '?' . $params);
        }
      }

      return response()->json(['status' => 'error', 'message' => 'Payment record not found'], 404);

    } catch (\Exception $e) {
      Log::channel('payment')->error('Payment failure error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Payment processing failed'], 500);
    }
  }

  public function cancel(Request $request)
  {
    try {
      $payment = Payment::where('order_id', $request->tran_id)->first();
      if ($payment) {
        // Update payment status directly here for faster response
        $payment->update([
          'status' => 'cancelled',
          'tran_id' => $request->tran_id,
          'gateway_response' => json_encode($request->all())
        ]);

        // Dispatch job for any additional processing
        PaymentCanceledJob::dispatch($request->tran_id, $request->all());
        sleep(1);
        // Handle callback URL similar to success function
        if($payment->callback_url == 'metronet'){
          return redirect()->route('dashboard')->with('warning', 'Payment was cancelled.');
        } else {
          $callbackUrl = $payment->callback_url;
          $params = http_build_query([
              'status' => 'cancelled'
          ]);
          return redirect()->away($callbackUrl . '?' . $params);
        }
      }

      return response()->json(['status' => 'error', 'message' => 'Payment record not found'], 404);

    } catch (\Exception $e) {
      Log::channel('payment')->error('Payment cancellation error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Payment processing failed'], 500);
    }
  }


  public function updateUserBalance($userId, $amount)
  {
    try {
      DB::beginTransaction();

      $user = User::find($userId);
      
      UserWallet::create(
        [
          'user_id' => $userId,
          'balance' => $amount,
          'balance_type' => 'Debit',
          'transaction_via' => 'online'
        ]
      );

      if ($user) {
        $user->available_balance += $amount;
        $user->save();
      }

      //IF USER HAS PARENT USER THEN UPDATE PARENT USER BALANCE
      if ($user->created_by) {
        $parentUser = User::find($user->created_by);
        if ($parentUser) {

          //CALCULATE PARENT USER BALANCE FOR NON MASKING MESSAGES
          $parentUserSmsRate = $parentUser->smsRate->nonmasking_rate;
          $userSmsRate = $user->smsRate->nonmasking_rate;

          $totalUserSms = $amount / $userSmsRate;
          $parentUserCost = $totalUserSms * $parentUserSmsRate;
          $parentUserProfit = $amount - $parentUserCost;

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


  public function paymentResponse(Request $request)
  {
      // Handle the payment response from the payment gateway
      $data = $request->all();

      // dd($data); 
      // Process the payment response (e.g., update order status, send notifications, etc.)

      return response()->json(['status' => 'success', 'message' => 'Payment response processed successfully']);
  }

}
