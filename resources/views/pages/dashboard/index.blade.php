@extends('master.master')

@section('title', 'index')



@section('content')


<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row g-6">
        <!-- View sales -->
        <div class="col-xl-4 ">
          <div class="card h-100">
            <div class="d-flex align-items-end row">
              <div class="col-7">
                <div class="card-body ">
                  <h5 class="card-title mb-10" style="">   Welcome - {{ auth()->user()->name }}  <br><br><span class="badge rounded-pill bg-label-primary me-1">{{ auth()->user()->role }}</span></h5>


                  <a href="{{ route('merchants.index') }}" class="btn btn-primary">View Merchants</a>
                </div>
              </div>
              <div class="col-5 text-center text-sm-left">
                <div class="card-body pb-0 px-0 px-md-4">
                  <img
                    src="../../assets/img/illustrations/card-advance-sale.png"
                    height="140"
                    alt="view sales" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- View sales -->

        <!-- Statistics -->
        <div class="col-xl-8 col-md-12">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
              <h5 class="card-title mb-0">Statistics</h5>

            </div>
            <div class="card-body d-flex align-items-end">
              <div class="w-100">
                <div class="row gy-3">
                  <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center">
                      <div class="badge rounded bg-label-primary me-4 p-2">
                        <i class="ti ti-users ti-md"></i>
                      </div>

                      <div class="card-info">
                        <h5 class="mb-0">{{ $data['totalMerchants'] ?? 0 }}</h5>
                        <small>Total Merchants</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center">
                      <div class="badge rounded bg-label-danger me-4 p-2">
                        <i class="ti ti-users ti-26px"></i></i>
                      </div>
                      <div class="card-info">
                        <h5 class="mb-0">{{ $data['newMerchantsLast24Hours'] ?? 0 }}</h5>
                        <small>New Merchants (24 H)</small>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center">
                      <div class="badge rounded bg-label-info me-4 p-2"><i class="ti ti-link ti-md"></i></div>
                      <div class="card-info">
                        <h5 class="mb-0">{{ $data['pendingMerchants'] ?? 0 }}</h5>
                        <small>Pending Merchants</small>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center">
                      <div class=" badge rounded bg-label-danger me-4 p-2">
                        <i class="ti ti-currency-dollar ti-lg"></i>
                      </div>
                      <div class="card-info">
                        <h5 class="mb-0"> {{  $data['totalDeclinedMerchants'] ?? 0 }}</h5>
                        <small>Declined Merchants</small>
                      </div>
                    </div>
                  </div>


                  <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center">
                      {{-- <div class="badge rounded bg-label-danger me-4 p-2">
                        <i class="ti ti-shopping-cart ti-lg"></i>
                      </div> --}}
                      <div class="badge rounded bg-label-info me-4 p-2"><i class="ti ti-users ti-lg"></i></div>
                      <div class="card-info">
                        <h5 class="mb-0">{{ $data['totalApprovedMerchants'] ?? 0 }}</h5>
                        <small>Approved Merchants</small>
                      </div>
                    </div>
                  </div>


                </div>
              </div>
            </div>
          </div>
        </div>
        <!--/ Statistics -->

          <!-- Latest  Merchants -->
          <div class="col-xxl-4 col-md-6">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between">
                <div class="card-title m-0 me-2">
                  <h5 class="mb-1">Latest Merchants</h5>
                  <p class="card-subtitle">Latest  {{ $data['latestFiveMerchants']->count() }}</p>
                </div>
              </div>
              <div class="card-body">
                <ul class="p-0 m-0">
                  @foreach ($data['latestFiveMerchants'] as $merchant)
                  <li class="d-flex mb-3 pb-1 align-items-center justify-content-between">
                    <div class="me-3" style="margin-right: 5px;">{{ $loop->iteration }}</div>
                    <div class="ml-3">
                      <strong>{{ $merchant['merchant_name'] }}</strong><br>
                      <small>{{ $merchant['merchant_email'] }}</small><br>
                      <small>Registration Date: {{ \Carbon\Carbon::parse($merchant['created_at'])->format('Y-m-d') }}</small>
                    </div>
                    <div class="ms-auto">
                      <form action="{{ route('merchants.preview') }}" method="GET" style="display: inline-block;">
                        @csrf
                        <input type="hidden" name="merchant_id" value="{{ $merchant['id'] }}">
                        <button type="submit" class="btn btn-icon btn-text-secondary rounded-pill waves-effect waves-light mx-1">
                            <i class="ti ti-eye"></i>
                        </button>
                    </form>
                    </div>
                  </li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>

          <!-- Lates Merchants -->

          {{-- Activity Logs --}}
          <div class="col-xxl-4 col-md-6">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between">
                <div class="card-title m-0 me-2">
                  <h5 class="mb-1">Activity Logs</h5>
                  <p class="card-subtitle">Total {{ $data['activityLogs']->count() }} </p>
                </div>
              </div>
              <div class="card-body">
                <ul class="p-0 m-0">
                  @foreach ($data['activityLogs'] as $log)
                  <li class="d-flex mb-3 pb-1 align-items-start">
                    <div class="me-3">
                      <div class="badge bg-label-primary rounded-circle p-2">
                        <i class="ti ti-activity ti-md"></i>
                      </div>
                    </div>
                    <div class="flex-grow-1">

                      <small class="text-body">URL: <a href="#" class="text-primary">{{ $log->url }}</a></small><br>
                      <small class="text-muted">IP: {{ $log->ip_address }} </small> <br>
                      <small class="text-muted">Date: {{ $log->created_at->format('Y-m-d H:i:s') }}</small>
                    </div>
                  </li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>

          <!--/ Activity Logs -->


      </div>
    </div>
    <!-- / Content -->
</div>

 @endsection

