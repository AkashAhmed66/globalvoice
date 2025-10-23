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

class TarifController extends Controller
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
    $title = 'Tarif List';
    $datas = $this->getClients();
    $ajaxUrl = route('tarif-list');

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


   return view('users::tarif.index', compact('title', 'tableHeaders', 'ajaxUrl', 'userGroups', 'opPrefixes'));
  }

  private function getClients(array $filters = []): Collection
{
    // Get all tariffs
    $tariffs = DB::table('tariff')
        ->orderBy('id', 'desc')
        ->get();

    // Attach details to each tariff
    $tariffs->transform(function ($tariff) {
        $details = DB::table('tariff_details')
            ->where('tariff_id', $tariff->id)
            ->select('ref_prefix', 'rate', 'is_active')
            ->orderBy('id')
            ->get();

        $tariff->details = $details;
        return $tariff;
    });

    // Apply search filter if needed
    if (!empty($filters['search_info'])) {
        $search = strtolower($filters['search_info']);
        $tariffs = $tariffs->filter(function ($tariff) use ($search) {
            $nameMatch = str_contains(strtolower($tariff->name), $search);
            $pulseMatch = str_contains(strtolower($tariff->pulse_local), $search);
            $detailsMatch = $tariff->details->contains(function ($detail) use ($search) {
                return str_contains(strtolower($detail->ref_prefix), $search);
            });
            return $nameMatch || $pulseMatch || $detailsMatch;
        })->values();
    }

    return $tariffs;
}



  public function create()
  {
    $title = 'Create Tarif';
    $pulses = DB::table('pulse')->where('is_visible', 1)->orderBy('id')->get();
    return view('users::tarif.create', compact('title', 'pulses'));
  }

  public function store(Request $request)
  {
      // Validate required fields
      $data = $request->validate([
          'name' => 'required|string|max:255',
          'pulse_local' => 'required|string|max:255',
        ]);

        // Insert into tariff table
        $response = DB::table('tariff')->insert([
            'name' => $data['name'],
            'pulse_local' => $data['pulse_local'],
            'created_by' => auth()->id(), // fixed
            'created_date' => now(),       // Laravel convention
        ]);


        return redirect()->route('tarif-list')->with('success', 'Tarif added successfully!');

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
        // Validate main fields and each detail
        $request->validate([
            'name' => 'required|string|max:255',
            'pulse' => 'required|integer|exists:pulse,id',
            'details' => 'required|array|min:1',
            'details.*.operator_prefix' => 'required|string',
            'details.*.rate' => 'required|numeric',
            'details.*.status' => 'nullable|in:Active,Inactive',
        ]);

        $tariff = DB::table('tariff')->find($id);
        if (!$tariff) {
            return response()->json(['status' => 'error', 'message' => 'Tariff not found'], 404);
        }

        // Check for duplicate name (exclude current record)
        if (DB::table('tariff')->where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Tariff name already exists']);
        }

        DB::beginTransaction();

        $pulse = DB::table('pulse')->find($request->pulse);

        // Update main tariff
        DB::table('tariff')->where('id', $id)->update([
            'name' => $request->name,
            'pulse_local' => $pulse->name,
            'pulse_local_id' => $pulse->id,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        // Remove old details and insert new ones
        DB::table('tariff_details')->where('tariff_id', $id)->delete();

        foreach ($request->details as $detail) {
            DB::table('tariff_details')->insert([
                'tariff_id' => $id,
                'ref_prefix' => $detail['operator_prefix'],
                'rate' => $detail['rate'],
                'is_active' => ($detail['status'] ?? 'Active') === 'Active' ? 1 : 0,
            ]);
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
