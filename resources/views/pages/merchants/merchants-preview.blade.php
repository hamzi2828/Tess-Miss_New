{{-- merchant-preview --}}
@extends('master.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Alert Messages --}}
    @if(session('error'))
    <br>
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    @if(session('success'))
    <br>
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    {{-- Merchant Approval Status --}}
    @php
        $allApproved = $merchant_details->approved_by !== null &&
                    $merchant_details->documents->isNotEmpty() &&
                    $merchant_details->documents->every(fn($document) => $document->approved_by !== null) &&
                    $merchant_details->sales->isNotEmpty() &&
                    $merchant_details->sales->every(fn($sale) => $sale->approved_by !== null) &&
                    $merchant_details->services->isNotEmpty() &&
                    $merchant_details->services->every(fn($service) => $service->approved_by !== null);
    @endphp

    @if($allApproved)
        <div class="alert alert-success d-flex align-items-center justify-content-center w-100" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>All Stages has been successfully approved!</strong>
        </div>
    @endif


    {{-- Merchant Details --}}
    <div class="row">
        <div class="col-md-3">
            @include('pages.merchants.preview.leftHalf')
        </div>

        <div class="col-md-9">
            {{-- Basic Details Section --}}
            @if(!is_null($merchant_details))
                @include('pages.merchants.preview.basicDetailsSection')
            @endif

            {{-- Sales Data Section --}}
            @if($merchant_details->sales->isNotEmpty())
                @include('pages.merchants.preview.salesDetailsSection')
            @endif

            {{-- Services Section --}}
            @if($merchant_details->services->isNotEmpty())
                @include('pages.merchants.preview.servicesDeatilsSection')
            @endif

            {{-- Approval Section --}}
            <div class="form-section box-container">
                <h5 class="basic-details-header">Approval</h5>
                <div class="mt-4 box-container">
                    {{-- Section Ownership Details --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>KYC Added By:</strong> {{ $merchant[0]['added_by']['name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>KYC Approved By:</strong> {{ $merchant[0]['approved_by']['name'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Documents Added By:</strong> {{ $merchant[0]['documents'][0]['added_by']['name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Documents Approved By:</strong> {{ $merchant[0]['documents'][0]['approved_by']['name'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Sales Added By:</strong> {{ $merchant[0]['sales'][0]['added_by']['name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Sales Approved By:</strong> {{ $merchant[0]['sales'][0]['approved_by']['name'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Services Added By:</strong> {{ $merchant[0]['services'][0]['added_by']['name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Services Approved By:</strong> {{ $merchant[0]['services'][0]['approved_by']['name'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Decline Modal --}}
    <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('merchants.decline', $merchant_details->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="declineModalLabel">Decline Merchant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="declineNotes" class="form-label"><strong>Reason for Declining:</strong></label>
                            <textarea id="declineNotes" name="decline_notes" class="form-control" rows="4" placeholder="Enter the reason for declining..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Decline</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Back and Approve Buttons --}}
    <div class="d-flex justify-content-end mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>

        @if(auth()->user()->role === 'supervisor' && !$allApproved)
            {{-- Approve Button --}}
            <form action="{{ route('merchants.approve', $merchant_details->id) }}" method="POST" class="ms-2">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Approve
                </button>
            </form>

            {{-- Decline Button --}}
            <form class="ms-2">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#declineModal">
                    <i class="fas fa-times me-1"></i> Decline
                </button>
            </form>
        @endif
    </div>

</div>
@endsection
