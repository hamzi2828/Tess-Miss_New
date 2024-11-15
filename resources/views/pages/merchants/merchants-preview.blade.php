{{-- merchant-preview --}}
@extends('master.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

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

    <div class="row">
        <!-- Merchant Name Section (4 Columns) -->
<div class="row">
    <!-- Merchant Information Section -->
    <div class="col-md-3">
        <div class="card text-left">
            <div class="card-header">
                <h5 class="mb-0 basic-details-header">Merchant Information</h5>
            </div>
            <div class="card-body">
                <h5 class="card-title">{{ $merchant_details->merchant_name ?? 'N/A' }}</h5>
                <p class="text-muted">{{ $merchant_details->merchant_name_ar ?? 'N/A' }}</p>
                <hr>
                <p><strong>Merchant ID:</strong> {{ $merchant_details->merchant_id ?? 'N/A' }}</p>
                <p><strong>Terminal ID:</strong> {{ $merchant_details->terminal_id ?? 'N/A' }}</p>
                <p><strong>Commercial Registration #:</strong> {{ $merchant_details->comm_reg_no ?? 'N/A' }}</p>
                <p><strong>Parent Category:</strong> {{ $merchant_details->parent_category ?? 'No Category Found' }}</p>
                <p>
                    <strong>Service Category:</strong>
                    @php
                        $activity = $MerchantCategory->where('id', $merchant_details['merchant_category'])->first();
                    @endphp
                    {{ $activity ? $activity->title : 'N/A' }}
                </p>
            </div>
            <div class="card-footer">
                  <h5 class="w-100 mb-3 basic-details-header">Merchant Documents</h5>
            @if($merchant_details->documents->isNotEmpty())
                <div class="form-section box-container">
                    <!-- Section for Valid Documents -->
                    @foreach($merchant_details['documents'] as $document)
                        @php
                            $documentExpired = false;

                            if (isset($document['date_expiry'])) {
                                $expiryDate = \Carbon\Carbon::parse($document['date_expiry']);
                                $documentExpired = $expiryDate->isPast();
                            }
                        @endphp

                        @if(!$documentExpired) <!-- Only display non-expired documents -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    @php
                                        $titleParts = explode('_', $document['title']); 
                                        $documentId = $titleParts[0]; 
                                        $secondWord = $titleParts[1] ?? null; 
                                        $matchingDocument = $all_documents->firstWhere('id', (int)$documentId);
                                        $title = $matchingDocument ? $matchingDocument->title : 'Document';

                                        if ($matchingDocument && $matchingDocument->title === 'QID' && $secondWord) {
                                            $title .= " for " . $secondWord;
                                        }
                                    @endphp
                                    <strong>{{ $title }}</strong>
                                </label>
                                <div class="input-group">
                                    @if(!empty($document['document']))
                                        <!-- Display a clickable button with an icon -->
                                        <a href="{{ asset($document['document']) }}" target="_blank" class="btn btn-outline-secondary">
                                            <i class="tf-icons ti ti-file"></i> View 
                                        </a>
                                    @else
                                        <p class="text-muted">No file available</p>
                                    @endif
                                </div>
                            </div>

                            @if($matchingDocument && $matchingDocument->require_expiry)
                            <div class="col-md-6 mt-5">

                            <p><strong>Expiry Date:</strong> 
                            {{ $document['date_expiry'] ? \Carbon\Carbon::parse($document['date_expiry'])->format('Y-m-d') : 'N/A' }}
                        </p> 
                            </div>
                            @endif
                        </div>
                        @endif
                    @endforeach
                    <!-- Section for Expired Documents -->
                    <h4 class="mt-4 mb-3 ">Expired Documents</h4>
                    @foreach($merchant_details['documents'] as $document)
                        @php
                            $documentExpired = isset($document['date_expiry']) && \Carbon\Carbon::parse($document['date_expiry'])->isPast();
                        @endphp

                        @if($documentExpired) 
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <strong>{{ $matchingDocument->title ?? 'Document' }}</strong>
                                </label>
                                <div class="input-group">
                                    @if(!empty($document['document']))
                                        <a href="{{ asset($document['document']) }}" target="_blank" class="btn btn-outline-secondary">
                                            <i class="tf-icons ti ti-file"></i> View File
                                        </a>
                                    @else
                                        <p class="text-muted">No file available</p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="expiry_{{ $document['id'] }}" class="form-label">Expiry Date</label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="expiry_{{ $document['id'] }}" 
                                    name="expiry_{{ $document['id'] }}" 
                                    value="{{ $document['date_expiry'] }}" 
                                    disabled
                                >
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endif
                {{-- <button class="btn btn-secondary w-100">Merchant Previous Documents</button> --}}
            </div>
        </div>
    </div>




        <!-- Merchant Details Section (8 Columns) -->
        <div class="col-md-9">
            <!-- Basic Details Section -->
            @if(!is_null($merchant_details))
                @include('pages.merchants.preview.basicDetailsSection')
            @endif

            <!-- Documents Details Section -->
        

            <!-- Sales Data Section -->
            @if($merchant_details->sales->isNotEmpty())
                @include('pages.merchants.preview.salesDetailsSection')
            @endif

            <!-- Services Section -->
            @if($merchant_details->services->isNotEmpty())
                @include('pages.merchants.preview.servicesDeatilsSection')
            @endif

            <div class="form-section box-container">
        <div class="mt-4">
            {{-- Section Ownership Details --}}
            <h5 class="basic-details-header">Section Ownership Details</h5>
        
            <p><strong>KYC:</strong> 
                Created By: {{ $merchant[0]['added_by']['name'] ?? 'N/A' }}, 
                Approved By: {{ $merchant[0]['approved_by']['name'] ?? 'N/A' }}
            </p>
             <p><strong>Documents:</strong>
                Added By: {{ !empty($merchant_details['documents']) && !empty($merchant_details['documents'][0]['added_by']['name']) ? $merchant_details['documents'][0]['added_by']['name'] : 'N/A' }},
                Approved By: {{ !empty($merchant_details['documents']) && !empty($merchant_details['documents'][0]['approved_by']['name']) ? $merchant_details['documents'][0]['approved_by']['name'] : 'N/A' }}
            </p>
            <p><strong>Sales:</strong>
                Added By: {{ !empty($merchant_details['sales']) && !empty($merchant_details['sales'][0]['added_by']['name']) ? $merchant_details['sales'][0]['added_by']['name'] : 'N/A' }},
                Approved By: {{ !empty($merchant_details['sales']) && !empty($merchant_details['sales'][0]['approved_by']['name']) ? $merchant_details['sales'][0]['approved_by']['name'] : 'N/A' }}
            </p>
            <p><strong>Services:</strong>
                Added By: {{ !empty($merchant_details['services']) && !empty($merchant_details['services'][0]['added_by']['name']) ? $merchant_details['services'][0]['added_by']['name'] : 'N/A' }},
                Approved By: {{ !empty($merchant_details['services']) && !empty($merchant_details['services'][0]['approved_by']['name']) ? $merchant_details['services'][0]['approved_by']['name'] : 'N/A' }}
            </p>
        </div>


        </div>
        </div>
    </div>




    <!-- Back and Approve Buttons -->
    <div class="d-flex justify-content-end mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        @if(auth()->user()->role === 'supervisor')
        {{-- Approve Button --}}
        <form action="{{ route('merchants.approve', $merchant_details->id) }}" method="POST" class="ms-2">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-1"></i> Approve
            </button>
        </form>
        @endif
    </div>

</div>
@endsection
