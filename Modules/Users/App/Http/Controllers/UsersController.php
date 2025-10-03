<?php

namespace Modules\Users\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Trait\ActionButtonTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

class UsersController extends Controller
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
//    dd(Redis::get("user:LoadTest"));
    $title = 'User List';
    $datas = $this->userRepository->all();
    $ajaxUrl = route('users-list');

    if ($this->ajaxDatatable()) {
      return DataTables::of($datas)
        ->addIndexColumn()
        ->addColumn('group', fn($row) => 
          DB::table('user_group')->where('id', $row->user_group_id)->value('name')
        )
        ->addColumn('status', fn($row) => $this->statusButton($row->status, $row->id))
        ->addColumn('action', fn($row) => $this->editButton('users-edit', $row->id) . ' ' . $this->deleteButton('users-delete', $row->id) . ' ' . ((Auth::user()->user_group_id == 1 || Auth::user()->user_group_id == 2) ? $this->loginAsButton('users-login-as', $row->id) : ''))
        ->rawColumns(['status', 'action'])
        ->make();
    }

    $tableHeaders = $this->getTableHeader('user-list');
    $userGroups = $this->userGroupRepository->all();

    return view('users::user.index', compact('title', 'tableHeaders', 'ajaxUrl', 'userGroups'));
  }

  public function loginAs($id)
  {
    if(session()->has('hasClonedUser') && session()->get('hasClonedUser') == $id){
      if(session()->has('hasClonedUser')) session()->remove('hasClonedUser');
      if(session()->has('hasClonedAnotherUser')) session()->remove('hasClonedAnotherUser');
      auth()->loginUsingId($id);
      return redirect()->route('dashboard');
    }

    if(session()->has('hasClonedAnotherUser') && session()->get('hasClonedAnotherUser') == $id){
      session()->remove('hasClonedAnotherUser');
      auth()->loginUsingId($id);
      return redirect()->route('dashboard');
    }

    if(!session()->has('hasClonedUser')){
      session()->put('hasClonedUser', auth()->user()->id);
    }else{
      session()->put('hasClonedAnotherUser', auth()->user()->id);
    }

    auth()->loginUsingId($id);
    return redirect()->route('dashboard');
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

  public function profile()
  {
    $title = 'Profile';
    $user = auth()->user();
    return view('users::user.profile', compact('title', 'user'));
  }

  public function profileUpdate(UpdateUserProfileRequest $request)
  {
    $validatedData = $request->validated();
    $user = auth()->user();
    //if request has password then update password
    if ($request->has('password') && !empty($request->password)) {
      $validatedData['password'] = bcrypt($request->password);
    }
    $user->update($validatedData);
    return redirect()->back()->with('success', 'Profile updated successfully');
  }


  public function usersRedisList()
  {
    try {
      $users = User::all();
      $passw = [
        'Business' => 'bU$!ness',
        'Reve' => 'r&V#$ysTem',
        'Techno71' => 't&cHn07!',
        'DataHost' => 'd@!@h0$t',
        'MimSMS' => 'm!msms',
        'REVESMS' => 'R$v$sms',
        'GenNet' => 'g$nn$t',
        'Banna' => '123456',
        'LoadTest' => '123456',
      ];

      foreach ($users as $user) {
        $senderId = SenderId::where('user_id', $user->id)->first();

        if (env('APP_TYPE') != 'Aggregator') {
          $data = [
            'id' => $user->id,
            'username' => $user->username,
            'password' => '123456', // default, will be overridden below if matched
            'available_balance' => $user->available_balance ?? 0,
            'cli' => $senderId->senderid ?? '',
            'rate' => $user->smsRate->nonmasking_rate ?? 0,
          ];

          // Override password if listed in $passw
          if (array_key_exists($user->username, $passw)) {
            $data['password'] = $passw[$user->username];
          }

          Redis::set("user:{$user->username}", json_encode($data));
        }
      }

      echo "Redis users list updated successfully";
    } catch (\Exception $e) {
      dd($e->getMessage());
    }
  }


  public function checkRedisUser($username)
  {
    $user = Redis::get("user:{$username}");
    if (!$user) {
      return response()->json([
        'status' => 'error',
        'message' => 'User not found in Redis',
      ]);
    }

    $user = json_decode($user, true);

    return response()->json([
      'status' => 'success',
      'data' => $user,
    ]);
  }


}
