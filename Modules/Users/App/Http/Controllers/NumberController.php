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

class NumberController extends Controller
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
    $title = 'Number List';
    $datas = $this->getClients();
    $ajaxUrl = route('number-list');


    if ($this->ajaxDatatable()) {
      return DataTables::of($datas)
        ->addIndexColumn()
        ->addColumn('action', fn($row) => $this->editButton('number-edit', $row->id) . ' ' . $this->deleteButton('number-delete', $row->id))
        ->rawColumns(['status', 'action'])
        ->make();
    }

    $tableHeaders = $this->getTableHeader('number-list');
    $userGroups = $this->userGroupRepository->all();
    $users = DB::table('user')->select('id', 'name')->get();
    $longCodes = DB::table('number')->select('no')->get();

    return view('users::number.index', compact('title', 'tableHeaders', 'ajaxUrl', 'userGroups', 'longCodes', 'users'));
  }

  private function getClients(array $filters = []): Collection
  {
      $query = DB::table('number');

      if (!empty($filters['search_info'])) {
          $search = $filters['search_info'];
          $query->where(function ($q) use ($search) {
              $q->where('no', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('is_booked', 'like', "%{$search}%")
                ->orWhere('channel', 'like', "%{$search}%")
                ->orWhere('did_balance', 'like', "%{$search}%")
                ->orWhere('amount', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
          });
      }

      return $query->orderBy('id', 'desc')->get();
  }

private function getAllLongCodes(): array
{
    // Fetch only the 'no' column
    $numbers = DB::table('number')->pluck('no')->toArray();
    return $numbers; // This will be an array of strings
}

  public function create()
  {
    $title = 'Create User';
    $userTypes = $this->userGroupRepository->getUserTypes();
    $rates = $this->rateRepository->getRates();
    $senderIds = $this->senderIdRepository->getAvailableSenderId();
    return view('users::create', compact('title', 'userTypes', 'rates', 'senderIds'));
  }



  public function store(Request $request)
  {
    //dd($request->toArray());

    //new
    $request->validate([
    'assign_to' => 'required',
    ]);

    // Insert into number table (assign_to + other fields)
    DB::table('number')->insert([
        'assign_to' => $request->assign_to,
        'type' => $request->type ?? null,
        'is_booked' => $request->is_booking ?? 0,
        'no' => $request->number ?? null,
        'channel' => $request->channel ?? null,
        'did_balance' => $request->did_balance ?? 0,
        'amount' => $request->amount ?? 0,
        'status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['status' => 'added', 'message' => 'Number added successfully']);
  }





  public function show($id)
  {
    return view('users::show');
  }

  public function edit($id)
  {

    $data = $this->userRepository->find($id);
    if (isset($data->senderIds[0])) {
      $data['senderId'] = $data->senderIds[0]['senderid'];
    }
    echo $data;
  }

  public function update(UpdateUserRequest $request, $id)
  {
    $validatedData = $request->validated();

    $user = User::find($id);

    if (!$user) {
      return response()->json(['status' => 'error', 'message' => 'User not found']);
    }

    if (isset($request->password)) {
      $validatedData['password'] = Hash::make($request->password);
    }

    $rate = Rate::where('id', $request->sms_rate_id)->first();

    $validatedData['masking_rate'] = $rate->masking_rate ?? 0;
    $validatedData['nonmasking_rate'] = $rate->nonmasking_rate ?? 0;

    // Update the operator with validated data
    $user->update($validatedData);

    //update the senderId with user id
    if ($request->sms_senderId) {
      $senderId = $this->senderIdRepository->find($request->sms_senderId);
      $senderId->user_id = $user->id;
      $senderId->save();
    }

    if ($request->sms_mask) {
      $mask = $this->maskRepository->find($request->sms_mask);
      $mask->user_id = $user->id;
      $mask->save();
    }

    return response()->json(['status' => 'updated', 'message' => 'User deleted successfully']);
  }

  public function destroy($id)
  {
    $this->userRepository->delete($id);
    return response()->json(['status' => 'deleted', 'message' => 'User deleted successfully']);
  }

}
