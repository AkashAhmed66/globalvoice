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

class BalanceController extends Controller
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
    $title = 'Balance List';
    $datas = $this->getClients();
    $ajaxUrl = route('balance-list');

    // dd($datas);

    if ($this->ajaxDatatable()) {
      return DataTables::of($datas)
        ->addIndexColumn()
        ->make();
    }

    $tableHeaders = $this->getTableHeader('balance-list');
    $userGroups = $this->userGroupRepository->all();
    $clients = DB::table('client')->where('is_Active', 1)->pluck('name', 'id')->toArray();

    return view('users::balance.index', compact('title', 'clients', 'tableHeaders', 'ajaxUrl', 'userGroups'));
  }

  private function getClients(array $filters = []): Collection
  {
      $query = DB::table('client')
          ->join('balance', 'client.id', '=', 'balance.client_id') // Adjust FK if needed
          ->select(
              'client.*',
              'balance.amount as balance_amount', // choose what to select from balance
              'balance.updated_at as balance_updated_at'
          );

      if (!empty($filters['search_info'])) {
          $search = $filters['search_info'];

          $query->where(function ($q) use ($search) {
              $q->where('client.name', 'like', "%{$search}%")
                ->orWhere('client.status', 'like', "%{$search}%")
                ->orWhere('client.contact_name', 'like', "%{$search}%")
                ->orWhere('client.mail', 'like', "%{$search}%")
                ->orWhere('client.contact_no', 'like', "%{$search}%")
                ->orWhere('balance.amount', 'like', "%{$search}%");
          });
      }

      return new Collection($query->orderBy('client.id', 'desc')->get());
  }


  public function create()
  {
    $title = 'Create User';
    $userTypes = $this->userGroupRepository->getUserTypes();
    $rates = $this->rateRepository->getRates();
    $senderIds = $this->senderIdRepository->getAvailableSenderId();
    return view('users::create', compact('title', 'userTypes', 'rates', 'senderIds'));
  }

  public function store(CreateUserRequest $request)
  {
    $userInfo = $this->userRepository->create($request->except('sms_senderId', 'sms_mask'));

    //update the senderId with user id
    if ($request->sms_senderId) {
      $senderId = $this->senderIdRepository->find($request->sms_senderId);
      $senderId->user_id = $userInfo->id;
      $senderId->save();
    }

    if ($request->sms_mask) {
      $mask = $this->maskRepository->find($request->sms_mask);
      $mask->user_id = $userInfo->id;
      $mask->save();
    }

    return response()->json(['status' => 'added', 'message' => 'User added successfully']);
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
