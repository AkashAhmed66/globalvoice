<?php
namespace Modules\Users\App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Users\App\Models\Reseller;

class ResellerRepository implements ResellerRepositoryInterface
{
  protected $model;
  private $reseller_id;
  private $user_group;
  private $user_id;

  public function __construct(Reseller $model)
  {
    $this->model = $model;
    $this->reseller_id = Auth::user()->reseller_id ?? null;
    $this->user_group = Auth::user()->user_group_id ?? null;
    $this->user_id = Auth::user()->id ?? null;
  }

  public function all(array $filters = []): Collection
  {
    // $query = $this->model->query();

    // if (isset($filters['name'])) {
    //   $query->where('name', 'like', '%' . $filters['name'] . '%');
    // }

    // if (isset($filters['email'])) {
    //   $query->where('email', 'like', '%' . $filters['email'] . '%');
    // }

    // $result = $query->get();
    return collect();
  }

  public function create(array $data): Reseller
  {
    return $this->model->create($data);
  }

  public function update(array $data, int $id): Reseller
  {
    $reseller = $this->model->find($id);
    $reseller->update($data);

    return $reseller;
  }

  public function find(int $id): Reseller
  {
    return $this->model->find($id);
  }

  public function delete(int $id): bool
  {
    return $this->model->destroy($id);
  }
}
