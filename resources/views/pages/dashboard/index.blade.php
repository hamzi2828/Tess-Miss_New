@extends('master.master')

@section('title', 'index')

@push('css')
@endpush

@section('content')


<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row g-6">
        <!-- View sales -->
        <div class="col-xl-4">
          <div class="card">
            <div class="d-flex align-items-end row">
              <div class="col-7">
                <div class="card-body text-nowrap">
                  <h5 class="card-title mb-10" style="">   Welcome - {{ auth()->user()->name }} 🎉  <span class="badge rounded-pill bg-label-primary me-1">{{ auth()->user()->role }}</span></h5>

        
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
                        <i class="ti ti-chart-pie-2 ti-lg"></i>
                      </div>
                      <div class="card-info">
                        <h5 class="mb-0">{{ $data['totalMerchants'] ?? 0 }}</h5>
                        <small>Total Merchants</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center">
                      <div class="badge rounded bg-label-success me-4 p-2">
                        <i class="ti ti-currency-dollar ti-lg"></i>
                      </div>
                      <div class="card-info">
                        <h5 class="mb-0">{{ $data['newMerchantsLast24Hours'] ?? 0 }}</h5>
                        <small>New Merchants Last 24 Hours</small>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center">
                      <div class="badge rounded bg-label-info me-4 p-2"><i class="ti ti-users ti-lg"></i></div>
                      <div class="card-info">
                        <h5 class="mb-0">{{ $data['pendingMerchants'] ?? 0 }}</h5>
                        <small>Pending Merchants</small>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center">
                      <div class="badge rounded bg-label-success me-4 p-2">
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
                      <div class="badge rounded bg-label-danger me-4 p-2">
                        <i class="ti ti-shopping-cart ti-lg"></i>
                      </div>
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

    
   
   
      </div>
    </div>
    <!-- / Content -->
</div>

 @endsection

@push('script')

@endpush