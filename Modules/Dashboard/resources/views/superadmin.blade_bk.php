@extends('layouts/layoutMaster')

@section('title', 'Apex - Charts')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
  <script>

    document.addEventListener('DOMContentLoaded', function() {

      fetch('/dashboard/get-status-wise-message-data?range=today')
        .then(response => response.json())
        .then(data => {

          updateChartData(data);
        })
        .catch(error => console.error('Error fetching today\'s data:', error));

      document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
          const range = this.getAttribute('data-range');

          fetch(`/dashboard/get-status-wise-message-data?range=${range}`)
            .then(response => response.json())
            .then(data => {
              console.log(`Data for range "${range}":`, data);
              updateChartData(data);
            })
            .catch(error => console.error('Error fetching data for range:', error));
        });
      });
    });

    function updateChartData(data) {

      statusWiseMessages = data;
      updateDonutChart(statusWiseMessages);
    }

    function updateChartData(data) {
      statusWiseMessages = data;
      updateDonutChart(statusWiseMessages);
    }

    let donutChart = null;

    function updateDonutChart(data) {

      if (donutChart) {
        donutChart.destroy();
      }

      if (!data || data.length === 0) {
        console.error('No data provided for the chart.');
        return;
      }

      let cardColor, headingColor, labelColor, borderColor, legendColor;

      if (isDarkStyle) {
        cardColor = config.colors_dark.cardColor;
        headingColor = config.colors_dark.headingColor;
        labelColor = config.colors_dark.textMuted;
        legendColor = config.colors_dark.bodyColor;
        borderColor = config.colors_dark.borderColor;
      } else {
        cardColor = config.colors.cardColor;
        headingColor = config.colors.headingColor;
        labelColor = config.colors.textMuted;
        legendColor = config.colors.bodyColor;
        borderColor = config.colors.borderColor;
      }

      // Color constant
      const chartColors = {
        column: {
          series1: '#826af9',
          series2: '#d2b0ff',
          bg: '#f8d3ff'
        },
        donut: {
          series1: '#fdd835',
          series2: '#32baff',
          series3: '#ffa1a1',
          series4: '#7367f0',
          series5: '#29dac7'
        },
        area: {
          series1: '#ab7efd',
          series2: '#b992fe',
          series3: '#e0cffe'
        }
      };

      const labels = data.map(item => item.status);
      const series = data.map(item => item.percentage);

      const donutChartEl = document.querySelector('#donutChart');
      if (!donutChartEl) {
        console.error('Donut chart element not found!');
        return;
      }

      const donutChartConfig = {
        chart: {
          height: 390,
          fontFamily: 'Inter',
          type: 'donut'
        },
        labels: labels,
        series: series,
        colors: [
          chartColors.donut.series1,
          chartColors.donut.series3,
          chartColors.donut.series4,
          chartColors.donut.series5
        ],
        stroke: {
          show: false,
          curve: 'straight'
        },
        dataLabels: {
          enabled: true,
          formatter: function(val) {
            return parseInt(val, 10) + '%';
          },
          style: {
            fontSize: '15px',
            fontWeight: 'normal'
          }
        },
        legend: {
          show: true,
          position: 'bottom',
          fontSize: '13px',
          markers: { offsetX: -3, width: 10, height: 10 },
          itemMargin: {
            vertical: 3,
            horizontal: 10
          },
          labels: {
            colors: legendColor,
            useSeriesColors: false
          }
        },
        plotOptions: {
          pie: {
            donut: {
              labels: {
                show: true,
                name: {
                  fontSize: '2rem'
                },
                value: {
                  fontSize: '0.9375rem',
                  fontWeight: 500,
                  color: headingColor,
                  formatter: function(val) {
                    return parseInt(val, 10) + '%';
                  }
                },
                total: {
                  show: true,
                  fontSize: '0.9375rem',
                  fontWeight: 500,
                  color: headingColor,
                  label: 'Total',
                  formatter: function() {
                    return series.reduce((a, b) => a + b, 0) + '%';
                  }
                }
              }
            }
          }
        }
      };

      donutChart = new ApexCharts(donutChartEl, donutChartConfig);
      donutChart.render();
    }

  </script>


  <script>
    var monthlyStatusCounts = @json($monthlyStatusCounts);
    var last7DaysTransections = @json($last7DaysTransections);
    var last7DaysMessages = @json($last7DaysMessages);
    console.log(last7DaysTransections);
  </script>
  @vite(['resources/assets/js/charts-apex.js'])
@endsection

@section('content')
  <div class="row">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row g-6 mb-5">
        <div class="col-sm-6 col-lg-12 text-center">
          <div class="d-flex justify-content-center align-items-center gap-2">
            <h4 class="mb-0">Current Balance: {{ $userInfo->available_balance }}</h4>
            
            @if (env('APP_TYPE') == "Aggregator")
              <a href="#"
                class="btn btn-primary mt-2 d-flex align-items-center"
                style="gap: 0.5rem; display: inline-flex;"
                data-bs-toggle="modal"
                data-bs-target="#payModal">
                <span class="text-500" style="font-size: 1.25rem;">Add Balance</span>
                <img src="{{ asset('assets/img/sslcommerz.png') }}"
                    alt="SSLCommerz"
                    style="height: 35px; margin-left: 6px; vertical-align: middle;">
              </a>
            @endif

            <!-- Modal -->
            <div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST" action="#">
                  @csrf
                  <div class="modal-content">
                    
                    <div class="modal-header">
                      <h5 class="modal-title">Pay Now</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" value="10" required min="10" step="any">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary" id="payNowBtn">Pay</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <p class="mt-2">
            <span class="me-1 fw-medium">Daily Number Of Request Sent SMS: {{ $totalSentMessages }}</span>
          </p>
        </div>
      </div>
    </div>
    @if($totalFailedMessages && $userInfo->user_group_id !==4)
      <div class="row g-6 mb-5">
        <div class="col-sm-6 col-lg-12 text-center">
          <div class="card card-border-shadow-danger h-100">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                  <tr>
                    <th>Numbers</th>
                    <th style="text-align: left;">Reason</th>

                  </tr>
                  </thead>
                  <tbody>
                  @foreach($totalFailedMessages as $vv)
                    <tr>
                      <td>{{ $vv['count'] }}</td>
                      <td style="text-align: left;">{{ $vv['reason'] }}</td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif
    <!-- Card Border Shadow -->
    <div class="row g-6 mb-5">
      <div class="col-sm-6 col-lg-3">
        <div class="card card-border-shadow-primary h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">

              <!-- <h4 class="mb-0">{{ $userInfo->user_group_id ==1 ? $totalUsers : "Welcome" }}</h4> -->
              <p>Remaining Non Masking Message<span
                  class="badge bg-label-primary">{{ $remainingNonmaskingmessageCount ?? 0 }}</span></p>
              <p>Remaining Masking Message<span
                  class="badge bg-label-primary">{{ $remainingMaskingMessageCount ?? 0 }}</span></p>

            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-border-shadow-warning h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                          <span class="avatar-initial rounded-3 bg-label-warning"
                          ><i class="ri-message-line ri-24px"></i
                            ></span>
              </div>
              <h4 class="mb-0">{{ $totalMessages }}</h4>
            </div>
            <h6 class="mb-0 fw-normal">Total Message</h6>
            <p class="mb-0">
              <span class="me-1 fw-medium">{{ $messageStatusCalculate['percentage_change'] }}</span>
              <small class="text-muted">than last month</small>
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-border-shadow-danger h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                          <span class="avatar-initial rounded-3 bg-label-danger"
                          ><i class="ri-money-dollar-circle-line ri-24px"></i
                            ></span>
              </div>
              <h4 class="mb-0">{{ $totalTransections }}</h4>
            </div>
            <h6 class="mb-0 fw-normal">Total Transaction</h6>
            <p class="mb-0">
              <span class="me-1 fw-medium">{{ $transectionStatusCalculate['percentage_change'] }}</span>
              <small class="text-muted">than last week</small>
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-border-shadow-info h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                          <span class="avatar-initial rounded-3 bg-label-info"
                          ><i class="ri-time-line ri-24px"></i
                            ></span>
              </div>
              <h4 class="mb-0">1</h4>
            </div>
            <h6 class="mb-0 fw-normal">Total Sender ID</h6>
            <p class="mb-0">
              <span class="me-1 fw-medium">{{ $senderIdStatusCalculate['percentage_change'] }}</span>
              <small class="text-muted">than last month</small>
            </p>
          </div>
        </div>
      </div>
      <!--/ Card Border Shadow -->
    </div>
  </div>

  <!-- Donut Chart -->
  <div class="col-md-6 col-12 mb-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h5 class="card-title mb-0">Message</h5>
          <span class="text-muted">Status wise Statistics</span>
        </div>
        <div class="dropdown d-none d-sm-flex">
          <button type="button" class="btn dropdown-toggle px-0" data-bs-toggle="dropdown" aria-expanded="false"><i
              class="ri-calendar-2-line"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                   data-range="today">Today</a></li>
            <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-range="yesterday">Yesterday</a>
            </li>
            <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-range="last_7_days">Last
                7 Days</a></li>
            <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-range="last_30_days">Last
                30 Days</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                   data-range="current_month">Current Month</a></li>
            <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-range="last_month">Last
                Month</a></li>
          </ul>

        </div>
      </div>
      <div class="card-body">
        <div id="donutChart"></div>
      </div>
    </div>
  </div>
  <!-- /Donut Chart -->

  <!-- Bar Chart -->
  <div class="col-12 col-md-6 mb-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-md-center align-items-start">
        <h5 class="card-title mb-0">Monthly Message</h5>
      </div>
      <div class="card-body">
        <div id="barChart"></div>
      </div>
    </div>
  </div>
  <!-- /Bar Chart -->

  <!-- Line Chart -->
  <div class="col-12 col-md-6 mb-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h5 class="card-title mb-0">Transactions</h5>
          <small class="text-muted">Last 7 days Transactions</small>
        </div>
        <div class="d-sm-flex d-none align-items-center">
          <h5 class="mb-0 me-4">৳ {{ $last7DaysTransectionsAmount['current_total_amount'] }}</h5>
          <span class="badge bg-label-secondary rounded-pill">
            <i class='ri-arrow-down-line ri-14px text-danger'></i>
            <span class="align-middle">{{ $last7DaysTransectionsAmount['percentage_change'] }}</span>
          </span>
        </div>
      </div>
      <div class="card-body">
        <div id="lineChart"></div>
      </div>
    </div>
  </div>
  <!-- /Line Chart -->

  <!-- Line Chart -->
  <div class="col-12 col-md-6 mb-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h5 class="card-title mb-0">Message</h5>
          <small class="text-muted">Last 7 days message</small>
        </div>
        <div class="d-sm-flex d-none align-items-center">
          <h5 class="mb-0 me-4">{{ $last7DaysMessagesTotal['current_total_count'] }}</h5>
          <span class="badge bg-label-secondary rounded-pill">
            <i class='ri-arrow-down-line ri-14px text-danger'></i>
            <span class="align-middle">{{ $last7DaysMessagesTotal['percentage_change'] }}</span>
          </span>
        </div>
      </div>
      <div class="card-body">
        <div id="lineChart2"></div>
      </div>
    </div>
  </div>
  <!-- /Line Chart -->

  </div>


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#payNowBtn').click(function(e) {
        e.preventDefault();
        $('#paymentMsg').text('Processing...');
        var data = {
          api_key: "{{$api_key}}", // Pass from controller: return view('payment', ['api_key'=>$user->api_key]);
          amount: $('#amount').val(),
          callback_url: "metronet"
        };

        $.ajax({
          url: "{{ url('/api/initiate-payment') }}", // Blade will render the full URL
          type: "POST",
          contentType: "application/json",
          headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }, // Laravel CSRF protection
          data: JSON.stringify(data),
          success: function(res) {
            if (res.payment_url) {
              window.location.href = res.payment_url;
            } else {
              $('#paymentMsg').text(res.message || 'Failed to initiate payment.');
            }
          },
          error: function(xhr) {
            let msg = 'An error occurred. Please try again.';
            if(xhr.responseJSON && xhr.responseJSON.message) {
              msg = xhr.responseJSON.message;
            }
            $('#paymentMsg').text(msg);
          }
        });
      });
    });

  </script>

@endsection
