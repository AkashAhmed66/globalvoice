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
        ->addColumn('action', fn($row) => $this->editButton('peer-edit', $row->id) . ' ' . $this->deleteButton('peer-delete', $row->id))
        ->rawColumns(['status', 'action'])
        ->make();
    }

    $tableHeaders = $this->getTableHeader('peer-list');
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
    $title = 'Create Peer';
    return view('users::peer.create', compact('title'));
  }

  public function store(Request $request)
  {
    try {
      // Validate required fields
      $request->validate([
        'name' => 'required|string|max:255',
        'channel' => 'required|string|max:255',
        'host' => 'required|string|max:255',
      ]);

      // Check if name already exists
      $existingPeer = DB::table('peer')->where('name', $request->name)->first();
      if ($existingPeer) {
        return response()->json(['status' => 'error', 'message' => 'Peer name already exists'], 422);
      }

      // Insert peer
      $peerId = DB::table('peer')->insertGetId([
        'name' => $request->name,
        'host' => $request->host,
        'channel' => $request->channel,
        'is_active' => 1,
        'created_by' => Auth::id(),
        'created_date' => now(),
        'action_date' => now(),
      ]);

      return response()->json(['status' => 'added', 'message' => 'Peer added successfully', 'id' => $peerId]);

    } catch (\Exception $e) {
      Log::error('Peer store error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Failed to save peer: ' . $e->getMessage()], 500);
    }
  }

  public function show($id)
  {
    return view('users::tarif.show');
  }

  public function edit($id)
  {
    try {
      // Get peer data
      $peer = DB::table('peer')->where('id', $id)->first();

      if (!$peer) {
        return response()->json(['status' => 'error', 'message' => 'Peer not found'], 404);
      }

      return response()->json(['peer' => $peer]);

    } catch (\Exception $e) {
      Log::error('Peer edit error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Failed to load peer'], 500);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      // Validate required fields
      $request->validate([
        'name' => 'required|string|max:255',
        'channel' => 'required|string|max:255',
      ]);

      // Check if peer exists
      $peer = DB::table('peer')->where('id', $id)->first();
      if (!$peer) {
        return response()->json(['status' => 'error', 'message' => 'Peer not found'], 404);
      }

      // Check if name already exists (excluding current record)
      $existingPeer = DB::table('peer')
        ->where('name', $request->name)
        ->where('id', '!=', $id)
        ->first();

      if ($existingPeer) {
        return response()->json(['status' => 'error', 'message' => 'Peer name already exists'], 422);
      }

      // Update peer
      DB::table('peer')->where('id', $id)->update([
        'name' => $request->name,
        'channel' => $request->channel,
        'updated_by' => Auth::id(),
        'updated_at' => now(),
      ]);

      return response()->json(['status' => 'updated', 'message' => 'Peer updated successfully', 'id' => $id]);

    } catch (\Exception $e) {
      Log::error('Peer update error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Failed to update peer: ' . $e->getMessage()], 500);
    }
  }

  public function destroy($id)
  {
    try {
      // Check if peer exists
      $peer = DB::table('peer')->where('id', $id)->first();
      if (!$peer) {
        return response()->json(['status' => 'error', 'message' => 'Peer not found'], 404);
      }

      // Delete peer
      DB::table('peer')->where('id', $id)->delete();

      return response()->json(['status' => 'deleted', 'message' => 'Peer deleted successfully']);

    } catch (\Exception $e) {
      Log::error('Peer delete error: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => 'Failed to delete peer: ' . $e->getMessage()], 500);
    }
  }

}
