<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\App\Http\Controllers\BalanceController;
use Modules\Users\App\Http\Controllers\CdrController;
use Modules\Users\App\Http\Controllers\ClientController;
use Modules\Users\App\Http\Controllers\MnpController;
use Modules\Users\App\Http\Controllers\NumberController;
use Modules\Users\App\Http\Controllers\ResellerController;
use Modules\Users\App\Http\Controllers\TarifController;
use Modules\Users\App\Http\Controllers\UserGroupController;
use Modules\Users\App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware('auth')->group(function () {

  //profile route
  Route::get('profile', [UsersController::class, 'profile'])->name('profile');
  Route::post('profile-update', [UsersController::class, 'profileUpdate'])->name('profile-update');


  Route::group(['prefix' => 'users'], function () {
    // user group routes
    Route::get('user-group-list', [UserGroupController::class, 'index'])->name('user-group-list');
    Route::post('user-group-store', [UserGroupController::class, 'store'])->name('user-group-store');
    Route::get('user-group/{id}/edit', [UserGroupController::class, 'edit'])->name('user-group-edit');
    Route::put('user-group-update/{id}', [UserGroupController::class, 'update'])->name('user-group-update');
    Route::delete('user-group-delete/{id}', [UserGroupController::class, 'destroy'])->name('user-group-delete');

    // user routes
    Route::get('users-list', [UsersController::class, 'index'])->name('users-list');
    Route::post('users-store', [UsersController::class, 'store'])->name('users-store');
    Route::get('users/{id}/edit', [UsersController::class, 'edit'])->name('users-edit');
    Route::put('users-update/{id}', [UsersController::class, 'update'])->name('users-update');
    Route::delete('users-delete/{id}', [UsersController::class, 'destroy'])->name('users-delete');

    // cdr routes
    Route::get('cdr-list', [CdrController::class, 'index'])->name('cdr-list');
    Route::post('cdr-store', [CdrController::class, 'store'])->name('cdr-store');
    Route::get('cdr/{id}/edit', [CdrController::class, 'edit'])->name('cdr-edit');
    Route::put('cdr-update/{id}', [CdrController::class, 'update'])->name('cdr-update');
    Route::delete('cdr-delete/{id}', [CdrController::class, 'destroy'])->name('cdr-delete');

    // client routes
    Route::get('client-list', [ClientController::class, 'index'])->name('client-list');
    Route::post('client-store', [ClientController::class, 'store'])->name('client-store');
    Route::get('client/{id}/edit', [ClientController::class, 'edit'])->name('client-edit');
    Route::put('client-update/{id}', [ClientController::class, 'update'])->name('client-update');
    Route::delete('client-delete/{id}', [ClientController::class, 'destroy'])->name('client-delete');

    // number routes
    Route::get('number-list', [NumberController::class, 'index'])->name('number-list');
    Route::post('number-store', [NumberController::class, 'store'])->name('number-store');
    Route::get('number/{id}/edit', [NumberController::class, 'edit'])->name('number-edit');
    Route::put('number-update/{id}', [NumberController::class, 'update'])->name('number-update');
    Route::delete('number-delete/{id}', [NumberController::class, 'destroy'])->name('number-delete');

    // balance routes
    Route::get('balance-list', [BalanceController::class, 'index'])->name('balance-list');
    Route::post('balance-store', [BalanceController::class, 'store'])->name('balance-store');
    Route::get('balance/{id}/edit', [BalanceController::class, 'edit'])->name('balance-edit');
    Route::put('balance-update/{id}', [BalanceController::class, 'update'])->name('balance-update');
    Route::delete('balance-delete/{id}', [BalanceController::class, 'destroy'])->name('balance-delete');

    // tarif routes
    Route::get('tarif-list', [TarifController::class, 'index'])->name('tarif-list');
    Route::post('tarif-store', [TarifController::class, 'store'])->name('tarif-store');
    Route::get('tarif/{id}/edit', [TarifController::class, 'edit'])->name('tarif-edit');
    Route::put('tarif-update/{id}', [TarifController::class, 'update'])->name('tarif-update');
    Route::delete('tarif-delete/{id}', [TarifController::class, 'destroy'])->name('tarif-delete');

    // mnp routes
    Route::get('mnp-list', [MnpController::class, 'index'])->name('mnp-list');
    Route::post('mnp-store', [MnpController::class, 'store'])->name('mnp-store');
    Route::get('mnp/{id}/edit', [MnpController::class, 'edit'])->name('mnp-edit');
    Route::put('mnp-update/{id}', [MnpController::class, 'update'])->name('mnp-update');
    Route::delete('mnp-delete/{id}', [MnpController::class, 'destroy'])->name('mnp-delete');

    
    Route::get('check-transection/{user_id}', [UsersController::class, 'checkTransection'])->name('check-transection');
    Route::get('login-as/{id}', [UsersController::class, 'loginAs'])->name('users-login-as');
    Route::get('users-redis-list', [UsersController::class, 'usersRedisList'])->name('users-redis-list');
    Route::get('users-redis-user-data/{username}', [UsersController::class, 'checkRedisUser'])->name('users-redis-user-data');
  });
});
