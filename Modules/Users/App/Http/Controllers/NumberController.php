<?php

namespace Modules\Users\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Trait\ActionButtonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Modules\Smsconfig\App\Models\Rate;
use Modules\Smsconfig\App\Repositories\MaskRepositoryInterface;
use Modules\Smsconfig\App\Repositories\RateRepositoryInterface;
use Modules\Smsconfig\App\Repositories\SenderIdRepositoryInterface;
use Modules\Users\App\Http\Requests\UpdateUserRequest;
use Modules\Users\App\Repositories\UserGroupRepositoryInterface;
use Modules\Users\App\Repositories\UserRepositoryInterface;
use Modules\Users\App\Trait\DataTableTrait;
use Yajra\DataTables\DataTables;
use Modules\Users\App\Models\User;
use Carbon\Carbon;

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
                    ->orWhere('is_active', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    // ✅ FIXED STORE METHOD
    public function store(Request $request)
    {
        try {
            Log::info('NumberController@store called', ['data' => $request->all()]);

            // Validate required field
            $request->validate([
                'no' => 'required|string|max:255',
            ]);

            // Prepare insert data
            $data = [
                'is_booked'    => $request->input('is_booked', 0),
                'no'           => $request->input('no'),
                'type'         => $request->input('type'),
                'channel'      => $request->input('channel'),
                'client_id'    => $request->input('client_id'),
                'credit_limit' => $request->input('credit_limit'),
                'long_code'    => $request->input('long_code'),
                'created_by'   => Auth::id() ?? $request->input('created_by'),
                'created_date' => Carbon::now(),
                'action_date'  => Carbon::now(),
                'did_balance'  => $request->input('did_balance', 0),
                'is_active'    => $request->input('is_active', 1),
            ];

            // ✅ Force insert into number table
            $inserted = DB::table('number')->insert($data);

            if ($inserted) {
                Log::info('Number inserted successfully', ['data' => $data]);
                return response()->json([
                    'status' => 'added',
                    'message' => 'Number added successfully!',
                ]);
            } else {
                Log::warning('Insert failed', ['data' => $data]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insert failed, no rows affected.',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error inserting number: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        return view('users::show');
    }

    public function edit($id)
    {
        $data = DB::table('number')->where('id', $id)->first();
        return response()->json($data);
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

        $user->update($validatedData);

        return response()->json(['status' => 'updated', 'message' => 'User updated successfully']);
    }

    public function destroy($id)
    {
        DB::table('number')->where('id', $id)->delete();
        return response()->json(['status' => 'deleted', 'message' => 'Number deleted successfully']);
    }
}
