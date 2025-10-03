<?php
namespace Modules\Dashboard\App\Repositories;
use Illuminate\Database\Eloquent\Collection;
use Modules\Dashboard\App\Models\Dashboard;
interface DashboardRepositoryInterface
{
  public function all(array $filters = []): Collection;
  public function create(array $data): Dashboard;
  public function find(int $id): Dashboard;
  public function update(array $data, int $id): Dashboard;
  public function delete(int $id): bool;
}
