<?php

namespace Modules\Users\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Trait\ActionButtonTrait;
use Illuminate\Http\RedirectResponse;
use Modules\Users\App\Http\Requests\CreateResellerRequest;
use Modules\Users\App\Http\Requests\UpdateResellerRequest;
use Modules\Users\App\Repositories\ResellerRepositoryInterface;
use Modules\Smsconfig\App\Repositories\RateRepositoryInterface;
use Modules\Users\App\Trait\DataTableTrait;
use Yajra\DataTables\DataTables;
use Modules\Users\App\Models\Reseller;
use Modules\Smsconfig\App\Models\Rate;

class ResellerController extends Controller
{
  use DataTableTrait;
  use ActionButtonTrait;

  protected ResellerRepositoryInterface $resellerRepository;

  public function __construct(ResellerRepositoryInterface $resellerRepository, RateRepositoryInterface $rateRepository)
  {
    $this->resellerRepository = $resellerRepository;
    $this->rateRepository = $rateRepository;
  }

  public function index()
  {
    $title = 'Reseller List';
    $datas = '';// $this->resellerRepository->all();
    $ajaxUrl = route('reseller-list');

    if ($this->ajaxDatatable()) {
      return DataTables::of($datas)
        ->addIndexColumn()
        ->addColumn('status', fn($row) => $this->statusButton($row->status, $row->id))
        ->addColumn('available_balance', fn($row) => number_format($row->available_balance, 2))
        ->addColumn('sms_rate', fn($row) => ($row->sms_rate)?$row->sms_rate->rate_name:'-')
        ->addColumn('action', fn($row) => $this->editButton('reseller-edit', $row->id) . $this->deleteButton('reseller-delete', $row->id))
        ->rawColumns(['status', 'action'])
        ->make();
    }

    $tableHeaders = $this->getTableHeader('reseller-list');
    $smsRates = $this->rateRepository->all();
    return view('users::reseller.index', compact('title', 'tableHeaders', 'ajaxUrl', 'smsRates'));
  }

  public function create()
  {
    $title = 'Create Reseller';
    return view('users::create', compact('title'));
  }

  public function store(CreateResellerRequest $request)
  {
    $this->resellerRepository->create($request->all());
    return response()->json(['status' => 'added', 'message' => 'Reseller added successfully']);
  }

  public function show($id)
  {
    return view('users::show');
  }

  public function edit($id)
  {
    
    $data = $this->resellerRepository->find($id);
    echo $data;
  }

  public function update(UpdateResellerRequest $request, $id)
  {
    
      $validatedData = $request->validated();

      $reseller = Reseller::find($id);

      if (!$reseller) {
        return response()->json(['status' => 'error', 'message' => 'Reseller not found']);
      }

      // Update the operator with validated data
      $reseller->update($validatedData);
      return response()->json(['status' => 'updated', 'message' => 'Reseller updated successfully']);
  }

  public function destroy($id)
  {
    $this->resellerRepository->delete($id);
    return response()->json(['status' => 'deleted', 'message' => 'Reseller deleted successfully']);
  }
}
