<?php

namespace Modules\Users\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Trait\ActionButtonTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Modules\Smsconfig\App\Models\SenderId;
use Modules\Smsconfig\App\Repositories\MaskRepositoryInterface;
use Modules\Smsconfig\App\Repositories\RateRepositoryInterface;
use Modules\Smsconfig\App\Repositories\SenderIdRepositoryInterface;
use Modules\Users\App\Http\Requests\CreateUserRequest;
use Modules\Users\App\Http\Requests\UpdateUserProfileRequest;
use Modules\Users\App\Http\Requests\UpdateUserRequest;
use Modules\Users\App\Repositories\UserGroupRepositoryInterface;
use Modules\Users\App\Repositories\UserRepositoryInterface;
use Modules\Users\App\Trait\DataTableTrait;
use Yajra\DataTables\DataTables;
use Modules\Users\App\Models\User;
use Modules\Users\App\Models\UserGroup;
use Modules\Smsconfig\App\Models\Rate;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
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
    $title = 'Client List';
    $datas = $this->getClients();
    $ajaxUrl = route('client-list');

    // dd($datas);

    if ($this->ajaxDatatable()) {
      return DataTables::of($datas)
        ->addIndexColumn()
        ->editColumn('is_active', fn($row) => $row->is_active ? 'Active' : 'Inactive')
        ->addColumn('action', fn($row) => $this->editButton('client-edit', $row->id) . ' ' . $this->deleteButton('client-delete', $row->id))
        ->rawColumns(['is_active', 'action'])
        ->make();
    }

    $tableHeaders = $this->getTableHeader('client-list');
    $userGroups = $this->userGroupRepository->all();
    $tariffs = DB::table("tariff")->pluck("name", "id")->toArray(); 
    $service_type = DB::table("service_type")->pluck("name", "code")->toArray();
    $districts = DB::table("area_info")
    ->distinct()
    ->pluck("district")
    ->toArray();

    return view('users::client.index', compact('title', 'tariffs', 'tableHeaders', 'ajaxUrl', 'userGroups', 'districts', 'service_type'));
  }

  private function getClients(array $filters = []): Collection
  {
      $query = DB::table('client');

      if (!empty($filters['search_info'])) {
          $search = $filters['search_info'];

          $query->where(function ($q) use ($search) {
              $q->where('name', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhere('contact_name', 'like', "%{$search}%")
                ->orWhere('mail', 'like', "%{$search}%")
                ->orWhere('contact_no', 'like', "%{$search}%");
          });
      }

      return $query->orderBy('id', 'desc')->get();
  }


  public function create()
  {
    $title = 'Create User';
    $userTypes = $this->userGroupRepository->getUserTypes();
    $rates = $this->rateRepository->getRates();
    $senderIds = $this->senderIdRepository->getAvailableSenderId();
    return view('users::create', compact('title', 'userTypes', 'rates', 'senderIds'));
  }

  private function createOrUpdate(Request $request, $id = null)
  {
    $data = $request->toArray();

    if ($id) {
      // Update existing client
      return $this->userRepository->update($id, $data);
    }

    // Create new client
    return $this->userRepository->create($data);
  }

  public function store(Request $request)
  {
    // return $request->toArray();
      // Validate required fields for client
      $validated = $request->validate([
          'name'           => 'required|string|max:255',
          'contact_name'   => 'required|string|max:255',
          'contact_no'     => 'required|string|max:20',
          'credit_limit'   => 'required|numeric',
          'district'       => 'required|string|max:255',
          'mail'           => 'required|email',
          'password'       => 'required|string|min:6',
          'tariff'         => 'required|numeric',
          'web_name'       => 'required|string|max:255',
          'zone'           => 'required|string|max:255',
          'address'        => 'nullable|string|max:255',
      ]);

      // Insert client and get ID
      $clientId = DB::table('client')->insertGetId([
          'name'            => $validated['name'],
          'address'         => $validated['address'] ?? '',
          'contact_name'    => $validated['contact_name'],
          'contact_no'      => $validated['contact_no'],
          'credit_limit'    => $validated['credit_limit'],
          'district'        => $validated['district'],
          'mail'            => $validated['mail'],
          'password'        => bcrypt($validated['password']),
          'tariff_id'       => $validated['tariff'],
          'web_name'        => $validated['web_name'],
          'zone'            => $validated['zone'],
          'is_isd_enabled'  => $validated['is_isd_enabled'] ?? 0,
          'created_by'      => auth()->user()->id,
          'created_date'    => now(),
      ]);

      // Process services if available
      if ($request->has('services')) {
          foreach ($request->services as $index => $service) {
              // Check if all required service fields are not null
              if (
                  !empty($service['service_type']) &&
                  !empty($service['service_name']) &&
                  !empty($service['otc']) &&
                  !empty($service['mrc']) &&
                  !empty($service['launch_date']) &&
                  !empty($service['bill_start_date'])
              ) {
                  DB::table('service')->insert([
                      'type'            => $service['service_type'],
                      'name'            => $service['service_name'],
                      'description'     => $service['description'] ?? null,
                      'launch_date'     => $service['launch_date'],
                      'bill_start_date' => $service['bill_start_date'],
                      'otc'             => $service['otc'],
                      'mrc'             => $service['mrc'],
                      'client_id'       => $clientId,
                      'created_by'      => auth()->user()->id,
                      'created_date'    => now(),
                      'serial'          => $service['serial'] ?? 1,
                  ]);
              }
          }
      }

      return response()->json([
          'status'  => 'added',
          'message' => 'User and services added successfully',
      ]);
  }

  public function show($id)
  {
    return view('users::show');
  }

  public function edit($id)
  {

      // Get the client record
      $client = DB::table('client')->where('id', $id)->first();

      if (!$client) {
          return response()->json(['error' => 'Client not found'], 404);
      }

      // Convert stdClass to array for modification
      $client = (array) $client;


      // Get all related services
      $services = DB::table('service')
          ->where('client_id', $id)
          ->select(
              'id',
              'type as service_type',
              'name as service_name',
              'description',
              'launch_date',
              'bill_start_date',
              'otc',
              'mrc',
              'serial'
          )
          ->get();

      // Attach services as array
      $client['services'] = $services;

      // Return as JSON
      return response()->json($client);
  }


  public function update(Request $request, $id)
  {
      // Validate required fields (same as store)
      $validated = $request->validate([
          'name'           => 'required|string|max:255',
          'contact_name'   => 'required|string|max:255',
          'contact_no'     => 'required|string|max:20',
          'credit_limit'   => 'required|numeric',
          'district'       => 'required|string|max:255',
          'mail'           => 'required|email',
          'password'       => 'nullable|string|min:6',
          'tariff'         => 'required|numeric',
          'web_name'       => 'required|string|max:255',
          'zone'           => 'required|string|max:255',
          'address'        => 'nullable|string|max:255',
      ]);

      $now = now();

      // Update client
      DB::table('client')->where('id', $id)->update([
          'name'            => $validated['name'],
          'address'         => $validated['address'] ?? '',
          'contact_name'    => $validated['contact_name'],
          'contact_no'      => $validated['contact_no'],
          'credit_limit'    => $validated['credit_limit'],
          'district'        => $validated['district'],
          'mail'            => $validated['mail'],
          'password'        => $validated['password'] ? bcrypt($validated['password']) : DB::raw('password'),
          'tariff_id'       => $validated['tariff'],
          'web_name'        => $validated['web_name'],
          'zone'            => $validated['zone'],
          'is_isd_enabled'  => $validated['is_isd_enabled'] ?? 0,
          'created_by'      => auth()->user()->id,
          'created_date'    => $now,
      ]);

      // Handle services (insert/update)
      if ($request->has('services')) {
          foreach ($request->services as $service) {
              if (
                  !empty($service['service_type']) &&
                  !empty($service['service_name']) &&
                  !empty($service['otc']) &&
                  !empty($service['mrc']) &&
                  !empty($service['launch_date']) &&
                  !empty($service['bill_start_date'])
              ) {
                  if (!empty($service['id']) && $service['id'] != 0) {
                      // Update existing service
                      DB::table('service')->where('id', $service['id'])->update([
                          'type'            => $service['service_type'],
                          'name'            => $service['service_name'],
                          'description'     => $service['description'] ?? null,
                          'launch_date'     => $service['launch_date'],
                          'bill_start_date' => $service['bill_start_date'],
                          'otc'             => $service['otc'],
                          'mrc'             => $service['mrc'],
                          'serial'          => $service['serial'] ?? 1,
                          'created_by'      => auth()->user()->id,
                          'created_date'    => $now,
                      ]);
                  } else {
                      // Insert new service
                      DB::table('service')->insert([
                          'type'            => $service['service_type'],
                          'name'            => $service['service_name'],
                          'description'     => $service['description'] ?? null,
                          'launch_date'     => $service['launch_date'],
                          'bill_start_date' => $service['bill_start_date'],
                          'otc'             => $service['otc'],
                          'mrc'             => $service['mrc'],
                          'serial'          => $service['serial'] ?? 1,
                          'client_id'       => $id,
                          'created_by'      => auth()->user()->id,
                          'created_date'    => $now,
                      ]);
                  }
              }
          }
      }

      return response()->json([
          'status'  => 'updated',
          'message' => 'Client and services updated successfully',
      ]);
  }


  public function destroy($id)
  {
    DB::table('service')->where('client_id', $id)->delete();
    DB::table('client')->where('id', $id)->delete();
    return response()->json(['status' => 'deleted', 'message' => 'User deleted successfully']);
  }

}
