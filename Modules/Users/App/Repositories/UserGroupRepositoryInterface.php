<?php

namespace Modules\Users\App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Users\App\Models\UserGroup;

interface UserGroupRepositoryInterface extends BaseRepositoryInterface
{
  public function all(array $filters = []): Collection;

  public function create(array $data): UserGroup;

  public function update(array $data, int $id): UserGroup;

  public function find(int $id): UserGroup;

  public function delete(int $id): bool;
}
