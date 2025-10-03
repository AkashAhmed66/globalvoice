<?php

namespace Modules\Users\App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Users\App\Models\Reseller;

interface ResellerRepositoryInterface extends BaseRepositoryInterface
{
  public function all(array $filters = []): Collection;

  public function create(array $data): Reseller;

  public function update(array $data, int $id): Reseller;

  public function find(int $id): Reseller;

  public function delete(int $id): bool;
}
