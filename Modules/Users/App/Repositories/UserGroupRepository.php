<?php
namespace Modules\Users\App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Users\App\Models\UserGroup;

class UserGroupRepository implements UserGroupRepositoryInterface
{
  protected $model;
  private $reseller_id;
  private $user_group;
  private $user_id;

  public function __construct(UserGroup $model)
  {
    $this->model = $model;
    $this->reseller_id = Auth::user()->reseller_id ?? null;
    $this->user_group = Auth::user()->id_user_group ?? null;
    $this->user_id = Auth::user()->id ?? null;
  }

  public function all(array $filters = []): Collection
  {
    $query = $this->model->query();

    if ($this->user_group == 2) {
      $query->where('id', 4);
    }

    if (isset($filters['title'])) {
      $query->where('title', 'like', '%' . $filters['title'] . '%');
    }

    if (isset($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    $query->orderBy('id', 'desc');

    return $query->get();
  }

  public function create(array $data): UserGroup
  {
    return $this->model->create($data);
  }

  public function update(array $data, int $id): UserGroup
  {
    $reseller = $this->model->find($id);
    $reseller->update($data);

    return $reseller;
  }

  public function find(int $id): UserGroup
  {
    return $this->model->find($id);
  }

  public function delete(int $id): bool
  {
    return $this->model->destroy($id);
  }
}
