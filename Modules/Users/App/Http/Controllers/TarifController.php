<?php

namespace Modules\Users\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Trait\ActionButtonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Users\App\Repositories\UserGroupRepositoryInterface;
use Modules\Users\App\Repositories\UserRepositoryInterface;
use Modules\Users\App\Trait\DataTableTrait;
use Yajra\DataTables\DataTables;

class TarifController extends Controller
{
  use DataTableTrait, ActionButtonTrait;

  protected $userRepository, $userGroupRepository;

  public function __construct(UserRepositoryInterface $userRepository, UserGroupRepositoryInterface $userGroupRepository)
  {
    $this->userRepository = $userRepository;
    $this->userGroupRepository = $userGroupRepository;
  }

  public function index()
  {
    $title = 'Tarif List';
    $datas = DB::table('tariff')->orderBy('id', 'desc')->get();
    $ajaxUrl = route('tarif-list');
    $tableHeaders = $this->getTableHeader('tarif-list');
    $userGroups = $this->userGroupRepository->all();
    $opPrefixes = DB::table('op_prefix')->get();

    if ($this->ajaxDatatable()) {
      return DataTables::of($datas)
        ->addIndexColumn()
        ->addColumn('action', fn($row) => $this->editButton('tarif-edit', $row->id))
        ->rawColumns(['action'])
        ->make();
    }

    return view('users::tarif.index', compact('title', 'tableHeaders', 'ajaxUrl', 'userGroups', 'opPrefixes'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name' => 'required|string|max:255',
      'pulse_local' => 'required|string'
    ]);

    $newTarifId = DB::table('tariff')->insertGetId([
      'name' => $data['name'],
      'pulse_local' => $data['pulse_local'],
      'created_by' => Auth::id(),
      'created_date' => now(),
    ]);

    foreach ($request->details as $detail) {
      DB::table('tariff_details')->insert([
        'tariff_id' => $newTarifId,
        'ref_prefix' => $detail['operator_prefix'],
        'rate' => $detail['rate'],
        'is_active' => $detail['status'] === 'Active' ? 1 : 0,
        'created_by' => Auth::id(),
        'created_date' => now()
      ]);
    }

    return response()->json([
      'status' => 'created',
      'message' => 'Tarif added successfully'
    ]);
  }

  public function edit($id)
  {
    try {
      $tariff = DB::table('tariff')->where('id', $id)->first();
      $details = DB::table('tariff_details')->where('tariff_id', $id)->get();

      return response()->json([
        'tariff' => $tariff,
        'details' => $details
      ]);

    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return response()->json(['status' => 'error'], 500);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      $request->validate([
        'name' => 'required|string|max:255',
        'pulse_local' => 'required|string',
        'details' => 'required|array',
      ]);

      DB::beginTransaction();

      DB::table('tariff')->where('id', $id)->update([
        'name' => $request->name,
        'pulse_local' => $request->pulse_local,
      ]);

      DB::table('tariff_details')->where('tariff_id', $id)->delete();

      foreach ($request->details as $detail) {
        DB::table('tariff_details')->insert([
          'tariff_id' => $id,
          'ref_prefix' => $detail['operator_prefix'],
          'rate' => $detail['rate'],
          'is_active' => $detail['status'] === 'Active' ? 1 : 0,
          'created_by' => Auth::id(),
          'created_date' => now(),
        ]);
      }

      DB::commit();

      return response()->json([
        'status' => 'updated',
        'message' => 'Tarif updated successfully'
      ]);

    } catch (\Exception $e) {
      DB::rollback();
      Log::error($e->getMessage());
      return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  public function destroy($id)
  {
    DB::table('tariff_details')->where('tariff_id', $id)->delete();
    DB::table('tariff')->where('id', $id)->delete();

    return response()->json(['status' => 'deleted']);
  }
}
