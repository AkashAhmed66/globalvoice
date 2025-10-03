<?php

namespace Modules\Users\App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Users\App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
  public function all(array $filters = []): Collection;
  
  public function create(array $data): User;

  public function update(array $data, int $id): User;

  public function find(int $id): User;

  public function delete(int $id): bool;
}
