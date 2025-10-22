<?php

namespace Modules\Users\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Trait\ActionButtonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Users\App\Repositories\UserGroupRepositoryInterface;
use Modules\Users\App\Repositories\UserRepositoryInterface;
use Modules\Users\App\Trait\DataTableTrait;
use Yajra\DataTables\DataTables;

class PeerController extends Controller
{
  use DataTableTrait;
  use ActionButtonTrait;

  protected UserRepositoryInterface $userRepository;
  protected UserGroupRepositoryInterface $userGroupRepository;

  public function __construct(UserRepositoryInterface $userRepository, UserGroupRepositoryInterface $userGroupRepository)
  {
    $this->userRepository = $userRepository;
    $this->userGroupRepository = $userGroupRepository;
  }

  public function index()
  {
    $title = 'Peer List';
    $datas = $this->getClients();
    $ajaxUrl = route('peer-list');

    // dd($datas);

    if ($this->ajaxDatatable()) {
      return DataTables::of($datas)
        ->addIndexColumn()
        ->addColumn('action', fn($row) => $this->editButton('tarif-edit', $row->id) . ' ' . $this->deleteButton('tarif-delete', $row->id))
        ->rawColumns(['status', 'action'])
        ->make();
    }

    $tableHeaders = $this->getTableHeader('tarif-list');
    $userGroups = $this->userGroupRepository->all();
    $opPrefixes = DB::table('op_prefix')->get();

    return view('users::peer.index', compact('title', 'tableHeaders', 'ajaxUrl', 'userGroups', 'opPrefixes'));
  }

  private function getClients(array $filters = []): Collection
  {
      $query = DB::table('peer');

      if (!empty($filters['search_info'])) {
          $search = $filters['search_info'];

          $query->where(function ($q) use ($search) {
              $q->where('name', 'like', "%{$search}%")
                ->orWhere('pulse_local', 'like', "%{$search}%");
          });
      }

      return $query->orderBy('id', 'desc')->get();
  }


  public function create()
  {
    $title = 'Create Tarif';
    $pulses = DB::table('pulse')->where('is_visible', 1)->orderBy('id')->get();
    return view('users::tarif.create', compact('title', 'pulses'));
  }

  public function store(Request $request)
  {
    try {
      // Validate required fields
      $request->validate([
        'name' => 'required|string|max:255',
        'pulse' => 'required|integer',
        'details' => 'required|array',
      ]);

      // Check if name already exists
      $existingTariff = DB::table('tariff')->where('name', $request->name)->first();
      if ($existingTariff) {
        return response()->json(['status' => 'error', 'message' => 'Tariff name already exists']);
      }

      // Start transaction
      DB::beginTransaction();

      // Get pulse name
      $pulse = DB::table('pulse')->where('id', $request->pulse)->first();

      // Insert tariff
      $tariffId = DB::table('tariff')->insertGetId([
        'name' => $request->name,
        'pulse_local' => $pulse ? $pulse->name : '',
        'pulse_local_id' => $request->pulse,
        'saved_by' => Auth::id(),
        'date' => now(),
      ]);

      // Insert tariff details
      if ($request->has('details') && is_array($request->details)) {
        foreach ($request->details as $detail) {
          if (isset($detail['operator_prefix']) && isset($detail['rate'])) {
            DB::table('tariff_details')->insert([
              'tariff_id' => $tariffId,
              'ref_prefix' => $detail['operator_prefix'],
              'rate' => $detail['rate'] ?? 0,
              'is_active' => ($detail['status'] ?? 'Active') === 'Active' ? 1 : 0,
            ]);
          }
        }
      }

      DB::commit();

      return response()->json(['status' => 'added', 'message' => 'Tariff added successfully', 'id' => $tariffId]);

    } catch (\Exception $e) {
      DB::rollback();
      Log::error('Tariff store error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Failed to save tariff: ' . $e->getMessage()], 500);
    }
  }

  public function show($id)
  {
    return view('users::tarif.show');
  }

  public function edit($id)
  {
    try {
      // Get tariff data
      $tariff = DB::table('tariff')->where('id', $id)->first();

      if (!$tariff) {
        return response()->json(['status' => 'error', 'message' => 'Tariff not found'], 404);
      }

      // Get tariff details with operator prefix info
      $details = DB::table('tariff_details as d')
        ->leftJoin('op_prefix as p', 'd.ref_prefix', '=', 'p.prefix')
        ->where('d.tariff_id', $id)
        ->select('d.*', 'p.prefix', 'p.detail_name')
        ->orderBy('d.id')
        ->get();

      $data = [
        'tariff' => $tariff,
        'details' => $details
      ];

      return response()->json($data);

    } catch (\Exception $e) {
      Log::error('Tariff edit error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Failed to load tariff'], 500);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      // Validate required fields
      $request->validate([
        'name' => 'required|string|max:255',
        'pulse' => 'required|integer',
        'details' => 'required|array',
      ]);

      // Check if tariff exists
      $tariff = DB::table('tariff')->where('id', $id)->first();
      if (!$tariff) {
        return response()->json(['status' => 'error', 'message' => 'Tariff not found'], 404);
      }

      // Check if name already exists (excluding current record)
      $existingTariff = DB::table('tariff')
        ->where('name', $request->name)
        ->where('id', '!=', $id)
        ->first();

      if ($existingTariff) {
        return response()->json(['status' => 'error', 'message' => 'Tariff name already exists']);
      }

      // Start transaction
      DB::beginTransaction();

      // Get pulse name
      $pulse = DB::table('pulse')->where('id', $request->pulse)->first();

      // Update tariff
      DB::table('tariff')->where('id', $id)->update([
        'name' => $request->name,
        'pulse_local' => $pulse ? $pulse->name : '',
        'pulse_local_id' => $request->pulse,
        'updated_by' => Auth::id(),
        'updated_at' => now(),
      ]);

      // Delete existing tariff details
      DB::table('tariff_details')->where('tariff_id', $id)->delete();

      // Insert new tariff details
      if ($request->has('details') && is_array($request->details)) {
        foreach ($request->details as $detail) {
          if (isset($detail['operator_prefix']) && isset($detail['rate'])) {
            DB::table('tariff_details')->insert([
              'tariff_id' => $id,
              'ref_prefix' => $detail['operator_prefix'],
              'rate' => $detail['rate'] ?? 0,
              'is_active' => ($detail['status'] ?? 'Active') === 'Active' ? 1 : 0,
            ]);
          }
        }
      }

      DB::commit();

      return response()->json(['status' => 'updated', 'message' => 'Tariff updated successfully', 'id' => $id]);

    } catch (\Exception $e) {
      DB::rollback();
      Log::error('Tariff update error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Failed to update tariff: ' . $e->getMessage()], 500);
    }
  }

  public function destroy($id)
  {
    try {
      // Check if tariff exists
      $tariff = DB::table('tariff')->where('id', $id)->first();
      if (!$tariff) {
        return response()->json(['status' => 'error', 'message' => 'Tariff not found'], 404);
      }

      // Start transaction
      DB::beginTransaction();

      // Delete tariff details first
      DB::table('tariff_details')->where('tariff_id', $id)->delete();

      // Delete tariff
      DB::table('tariff')->where('id', $id)->delete();

      DB::commit();

      return response()->json(['status' => 'deleted', 'message' => 'Tariff deleted successfully']);

    } catch (\Exception $e) {
      DB::rollback();
      Log::error('Tariff delete error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Failed to delete tariff: ' . $e->getMessage()], 500);
    }
  }

}
