@extends('layouts/layoutMaster')

@section('title', 'Dashboard - eCommerce')

{{--@section('vendor-style')
  @vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss'
  ])
@endsection--}}

{{--@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/cards-statistics.scss'])
@endsection--}}

{{--@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/apex-charts/apexcharts.js',
    'resources/assets/vendor/libs/swiper/swiper.js'
    ])
@endsection--}}

{{--@section('page-script')
  @vite('resources/assets/js/app-ecommerce-dashboard.js')
@endsection--}}

@section('content')
  <div class="row g-6 mb-6">

    <div class="col-lg-12">
      <div class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <h5 class="mb-1">Total Amount</h5>
          </div>
        </div>
        <div class="card-body d-flex justify-content-between flex-wrap gap-4">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class="ri-user-star-line ri-24px"></i>
              </div>
            </div>
            <div class="card-info">
              <h5 class="mb-0">{{ $balance_info->available_balance }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Sales Overview-->
    <!-- Sales Overview-->
    <div class="col-lg-6">
          <div class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <h5 class="mb-1">SMS REQUEST SUMMARY (LAST 90 DAYS)</h5>
          </div>
        </div>
        <div class="card-body justify-content-between flex-wrap gap-4">
            <div class="table-responsive">
                <table class="table table-sm table-border-bottom-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>User</th>
                      <th>API</th>
                      <th>WEB</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                  @if($message_request && $message_request->isNotEmpty())
                  @foreach($message_request as $index => $vv)
                    <tr>
                    <td>{{ $index + 1 }}</td>
                      <td>{{ $vv->name }}</td>
                      <td>{{ $vv->api_count }}</td>
                      <td>{{ $vv->web_count }}</td>
                      <td>{{ $vv->web_count + $vv->api_count }}</td>
                    </tr>
                    @endforeach
                    @else
                        <p>No messages available.</p>
                    @endif
                  </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>
    <!--/ Sales Overview-->
    <!-- Sales Overview-->
    <div class="col-lg-6">
          <div class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <h5 class="mb-1">SMS STATUS REPORT (LAST 90 DAYS)</h5>
          </div>
        </div>
        <div class="card-body justify-content-between flex-wrap gap-4">
        <div class="table-responsive">
                <table class="datatables-ecommerce table table-sm table-border-bottom-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>User</th>
                      <th>Queued</th>
                      <th>Processing</th>
                      <th>Sent</th>
                      <th>Delivered</th>
                      <th>Undelivered</th>
                      <th>Failed</th>
                    </tr>
                  </thead>
                  <tbody>
                  @if($message_status && $message_status->isNotEmpty())
                  @foreach($message_status as $index => $vv)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td>{{ $vv->name }}</td>
                      <td>{{ $vv->queue_count }}</td>
                      <td>{{ $vv->processing_count }}</td>
                      <td>{{ $vv->sent_count }}</td>
                      <td>{{ $vv->delivered_count }}</td>
                      <td>{{ $vv->hold_count }}</td>
                      <td>{{ $vv->failed_count }}</td>
                    </tr>
                    @endforeach
                    @else
                        <p>No messages available.</p>
                    @endif
                  </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>
    <!--/ Sales Overview-->
  </div>

@endsection
