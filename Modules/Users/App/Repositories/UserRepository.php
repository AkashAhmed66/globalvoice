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

    $data['password'] = Hash::make($data['password']);
    $data['created_by'] = Auth::user()->id;
    $data['billing_type'] = 'prepaid';
    $data['api_key'] = Hash::make($data['password'].$data['name']);
    $data['status'] = "ACTIVE"; // Default status active

    // dd($data);
    $user = $this->model->create($data);

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
