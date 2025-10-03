<?php

namespace Modules\Users\App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Modules\Smsconfig\App\Models\SenderId;
use Modules\Users\App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Smsconfig\App\Models\Rate;

class UserRepository implements UserRepositoryInterface
{
  protected $model;
  private $reseller_id;
  private $user_group;
  private $user_id;

  public function __construct(User $model)
  {
    $this->model = $model;
    $this->reseller_id = Auth::user()->reseller_id ?? null;
    $this->user_group = Auth::user()->user_group_id ?? null;
    $this->user_id = Auth::user()->id ?? null;
  }

  public function all(array $filters = []): Collection
  {
    $query = $this->model->query();

    // if (isset($this->user_group) && $this->user_group == 1) {
    //   $query->with('creator')->where('id', '!=', null);
    // } elseif (isset($this->user_group) && $this->user_group == 2) {
    //   $query->with('creator')->whereIn('user_group_id', [3, 4])->where('created_by', $this->user_id);
    // } elseif (isset($this->user_group) && $this->user_group == 3) {
    //   $query->with('creator')->whereIn('user_group_id', [4])->where('created_by', $this->user_id);
    // } else {
    //   $query->with('creator')->whereIn('user_group_id', [4])->where('created_by', $this->user_id);
    // }

    if (isset($filters['search_info'])) {
      $query = $query->where(function ($query) use ($filters) {
        $query->orWhere('name', 'like', '%' . $filters['search_info'] . '%')
          ->orWhere('mobile', 'like', '%' . $filters['search_info'] . '%')
          ->orWhere('username', 'like', '%' . $filters['search_info'] . '%')
          ->orWhere('email', 'like', '%' . $filters['search_info'] . '%');
      });
    }

    if (isset($filters['user_group'])) {
      $query->whereHas('userType', function ($query) use ($filters) {
        $query->where('title', 'like', '%' . $filters['user_group'] . '%');
      });
    }

    $query = $query->orderBy('id', 'DESC');

    return $query->get();
  }

  public function allUser(array $filters = []): Collection
  {
    $query = $this->model->query();
    if( $this->user_group != 1){
      $query->where(function ($q) {
        $q->where('created_by', $this->user_id);
      });
    }
    return $query->get();
  }

  public function create(array $data): User
  {
    $unhashedPassword = $data['password'];

    $data['password'] = Hash::make($data['password']);
    $data['created_by'] = Auth::user()->id;
    $data['apikey'] = Hash::make($data['password'].$data['username']);
    $data['tps'] = 50; // Default TPS value
    $data['status'] = "ACTIVE"; // Default status active

    $rate = 0;

    $data['masking_rate'] = $rate->masking_rate ?? 0;
    $data['nonmasking_rate'] = $rate->nonmasking_rate ?? 0;

    $user = $this->model->create($data);


    //get senderid by user_id
    $senderId = SenderId::where('user_id', $user->id)->first();

    if (env('APP_TYPE') != 'Aggregator') {
      $data = [
        'id' => $user->id,
        'username' => $user->username,
        'password' => $unhashedPassword,
        'available_balance' => $user->available_balance ?? 0,
        'cli' => $senderId->senderid ?? '',
        'rate' => $user->smsRate->nonmasking_rate ?? 0,
      ];

      Redis::set("user:{$user->username}", json_encode($data));

    }

    return $user;

  }

  public function update(array $data, int $id): User
  {
    $reseller = $this->model->find($id);
    $reseller->update($data);

    return $reseller;
  }

  public function find(int $id): User
  {
    return $this->model->find($id);
  }

  public function delete(int $id): bool
  {
    return $this->model->destroy($id);
  }

  public function getUserByGroupId(int $id): Collection
  {
    if ($id == 1) {
      return $this->model->whereIn('user_group_id', [3, 4])->get();
    } elseif ($id == 2) {
      return $this->model->whereIn('user_group_id', [3, 4])->where('created_by', $this->userId)->get();
    } elseif ($id == 3) {
      return $this->model->where('user_group_id', 4)->where('reseller_id', $this->resellerId)->get();
    } else {
      return $this->model->where('id', 0)->get();
    }
  }
}
