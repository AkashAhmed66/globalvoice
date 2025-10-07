<?php

namespace Modules\Users\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Trait\ActionButtonTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Users\App\Http\Requests\CreateUserGroupRequest;
use Modules\Users\App\Http\Requests\UpdateUserGroupRequest;
use Modules\Users\App\Repositories\UserGroupRepositoryInterface;
use Modules\Users\App\Trait\DataTableTrait;
use Yajra\DataTables\DataTables;
use Modules\Users\App\Models\UserGroup;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class UserGroupController extends Controller
{
  use DataTableTrait;
  use ActionButtonTrait;
  protected $userGroupRepository;

    public function __construct(UserGroupRepositoryInterface $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    public function index(Request $request)
    {
        $title = 'User Group List';
        $datas = $this->userGroupRepository->all($request->all());
        $ajaxUrl = route('user-group-list');
        $userGroup = Auth::user()->user_group_id;

        if ($this->ajaxDatatable()) {
            return DataTables::of($datas)
                ->addIndexColumn()
                ->addColumn('status', fn($row) => $this->statusButton($row->status, $row->id))
              ->addColumn('action', function ($row) use ($userGroup) {
                return $this->editButton('user-group-edit', $row->id) .
                  $this->deleteButton('user-group-delete', $row->id);
                // if ($userGroup == 1) {
                // }
                // return ''; 
              })                ->rawColumns(['status', 'action'])
                ->make();
        }

        $tableHeaders = $this->getTableHeader('user-group-list');
        $roles = DB::table('role')->where('is_visible', 1)->orderBy('serial')->pluck('name', 'id')->toArray();

        return view('users::group.index', compact('title', 'tableHeaders', 'ajaxUrl', 'roles'));
    }

    public function create()
    {
      $title = 'Create User Group';
      return view('users::group.create', compact('title'));
    }

    public function store(CreateUserGroupRequest $request)
    {
        // check if name already exsited
        $existingGroup = DB::table('user_group')->where('name', $request->name)->first();
        if ($existingGroup) {
            return response()->json(['status' => 'error', 'message' => 'User Group` name already exists']);
        }

        $newGroupId = DB::table('user_group')->insertGetId([
            'name' => $request->name,
            'saved_by' => Auth::id(),
            'date' => now(),
        ]);

        // Fetch the newly created row
        $newGroup = DB::table('user_group')->where('id', $newGroupId)->first();

        $insertData = [];
        foreach ($request->permissions as $roleId) {
            $insertData[] = [
                'role_id' => $roleId,
                'user_group_id' => $newGroup->id,
            ];
        }
        // Insert all at once
        DB::table('permission')->insert($insertData);

        return response()->json(['status' => 'added', 'message' => 'User Group added successfully']);
    }

    public function edit($id)
    {

        $data = $this->userGroupRepository->find($id);
        echo $data;
    }

    public function update(UpdateUserGroupRequest $request, $id)
    {
        $userGroup = UserGroup::find($id);

        if (!$userGroup) {
            return response()->json(['status' => 'error', 'message' => 'User Group not found']);
        }

        // Check if name already exists (excluding current record)
        $existingGroup = DB::table('user_group')
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->first();
        
        if ($existingGroup) {
            return response()->json(['status' => 'error', 'message' => 'User Group name already exists']);
        }

        // Update user group name
        $userGroup->update([
            'name' => $request->name,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        // Delete existing permissions for this user group
        DB::table('permission')->where('user_group_id', $id)->delete();

        // Insert new permissions if provided
        if ($request->has('permissions') && is_array($request->permissions)) {
            $insertData = [];
            foreach ($request->permissions as $roleId) {
                $insertData[] = [
                    'role_id' => $roleId,
                    'user_group_id' => $id,
                ];
            }
            // Insert all at once
            DB::table('permission')->insert($insertData);
        }

        return response()->json(['status' => 'updated', 'message' => 'User Group updated successfully']);
    }

    public function destroy($id)
    {
        try {
            // Check if user group exists
            $userGroup = UserGroup::find($id);
            if (!$userGroup) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User Group not found'
                ], 404);
            }

            // Start database transaction
            DB::beginTransaction();

            // Delete associated permissions first
            DB::table('permission')->where('user_group_id', $id)->delete();

            // Delete the user group
            $deleted = $this->userGroupRepository->delete($id);

            if ($deleted) {
                DB::commit();
                return response()->json([
                    'status' => 'deleted',
                    'message' => 'User Group and associated permissions deleted successfully'
                ]);
            }

            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete User Group'
            ], 500);

        } catch (QueryException $e) {
            DB::rollback();
            
            // SQLSTATE[23000] = Integrity constraint violation
            if ($e->getCode() == "23000") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete this User Group because it is referenced by other records.'
                ], 409); // 409 Conflict
            }

            // Any other DB error
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected database error occurred.'
            ], 500);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while deleting the User Group.'
            ], 500);
        }
    }
}
