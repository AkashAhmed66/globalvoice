<?php

namespace Modules\Users\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Transactions\App\Models\UserWallet;
use Modules\Users\Database\factories\ResellerFactory;
use Modules\Smsconfig\App\Models\Rate;

class Reseller extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'resellers';
    protected $fillable = ['reseller_name', 'phone', 'email', 'address', 'thana', 'district', 'sms_rate_id', 'tps', 'url'];
    
    protected static function newFactory(): ResellerFactory
    {
        //return ResellerFactory::new();
    }

    public function sms_rate()
    {
        return $this->belongsTo(Rate::class, 'sms_rate_id', 'id');
    }

  public function wallet()
  {
    return $this->hasOne(UserWallet::class, 'user_id', 'id');
  }
}
