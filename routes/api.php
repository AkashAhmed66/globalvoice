<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

//SSLCommerz payment routes
Route::post('/initiate-payment', [PaymentController::class, 'initiate'])->name('initiate-payment');
Route::post('/sslcommerz/success', [PaymentController::class, 'success'])->name('sslc.success');
Route::post('/sslcommerz/failure', [PaymentController::class, 'failure'])->name('sslc.failure');
Route::post('/sslcommerz/cancel', [PaymentController::class, 'cancel'])->name('sslc.cancel');
Route::post('/sslc/ipn', [PaymentController::class, 'ipn'])->name('sslc.ipn');
Route::post('/payment-response', [PaymentController::class, 'paymentResponse'])->name('payment.response');
