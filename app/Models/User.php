<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Modules\Smsconfig\App\Models\Rate;
use Modules\Transactions\App\Models\UserWallet;
use Modules\Users\App\Models\Reseller;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

  protected $table = "user";

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function reseller()
  {
    return $this->belongsTo(Reseller::class, 'reseller_id', 'id');
  }

  public function createBy()
  {
    return $this->belongsTo(\Modules\Users\App\Models\User::class, 'created_by', 'id');
  }

  public function creator()
  {
    return $this->hasOne(User::class, 'id', 'created_by');
  }

  public function getIsAdminAttribute()
  {
    return in_array($this->user_group_id, [1, 2]);
  }

  public function getIsResellerAttribute()
  {
    return $this->user_group_id == 3;
  }

  public function getIsUserAttribute()
  {
    return $this->user_group_id > 3;
  }

  public function isSuperAdmin()
  {
    return $this->user_group_id == 1;
  }

  public function isAdmin()
  {
    return $this->user_group_id == 2;
  }

  public function isReseller()
  {
    return $this->user_group_id == 3;
  }

  public function isCustomer()
  {
    return $this->user_group_id == 4;
  }

  public function getChildrenIdWithMyId()
  {
    $userId = [];
    if (Auth::user()->user_group_id == 4) {
      $userId = [Auth::user()->id];
    } else {
      if (Auth::user()->user_group_id == 3) {
        $resellerCreatedUserId = User::where('created_by', '=', Auth::user()->id)->pluck('id');
        $userId = $resellerCreatedUserId->push(Auth::user()->id);
      } else {
        if (Auth::user()->user_group_id == 2) {
          $adminCreatedUserId = User::where('created_by', '=', Auth::user()->id)->pluck('id');
          $resellerCreatedUserId = User::whereIn('created_by', $adminCreatedUserId)->pluck('id');
          $userId = $adminCreatedUserId->merge($resellerCreatedUserId)->push(Auth::user()->id);
        } else {
          if (Auth::user()->user_group_id == 1) {
            $supperAdminCreatedUserId = User::where('created_by', '=', Auth::user()->id)->pluck('id');
            $adminCreatedUserId = User::whereIn('created_by', $supperAdminCreatedUserId)->pluck('id');
            $resellerCreatedUserId = User::whereIn('created_by', $adminCreatedUserId)->pluck('id');
            $userId = $supperAdminCreatedUserId->merge($adminCreatedUserId)->merge($resellerCreatedUserId)->push(
              Auth::user()->id
            );
          }
        }
      }
    }

    return $userId;
  }
}
