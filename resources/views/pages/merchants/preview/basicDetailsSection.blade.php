<div class="form-section box-container">

    <!-- Step-based Progress Bar -->
    @include('pages.merchants.components.preview-progressBar')


  <h4 class="basic-details-header">Basic Details</h4>


<div class="form-section box-container mb-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Merchant Name:</strong> {{ $merchant_details['merchant_name'] ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Date of Incorporation:</strong>
                {{ $merchant_details['merchant_date_incorp'] ? \Carbon\Carbon::parse($merchant_details['merchant_date_incorp'])->format('Y-m-d') : 'N/A' }}
            </p>  </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Merchant Arabic Name:</strong> {{ $merchant_details['merchant_name_ar'] ?? 'N/A' }}</p>
        </div>

        <div class="col-md-6">
            <p><strong>Company Registration:</strong> {{ $merchant_details['comm_reg_no'] ?? 'N/A' }}</p>
        </div>
    </div>


    <div class="mb-3">
         <p class="mb-0">
             <strong>Registered Company Address/Details:</strong> {{ $merchant_details['address'] ?? 'N/A' }}
        </p>
    </div>



    <div class="row mb-3">
        <div class="col-md-6">
            <p>
                <strong>Mobile Number:</strong>
                {{ $merchant_details['merchant_mobile'] ?? 'N/A' }}
            </p>
        </div>

        <div class="col-md-6">
            <p>
                <strong>Company Principal Activities:</strong>
                @php
                    $activity = $MerchantCategory->where('id', $merchant_details['merchant_category'])->first();
                @endphp
                {{ $activity ? $activity->title : 'N/A' }}
            </p>
        </div>
    </div>


    <div class="row mb-3">
        <div class="col-md-6">
            <p>
                <strong>Landline Number:</strong>
                {{ $merchant_details['merchant_landline'] ?? 'N/A' }}
            </p>
        </div>

        <div class="col-md-6">
            <p>
                <strong>Website:</strong>
                <a href="{{ $merchant_details['merchant_url'] ?? '#' }}" target="_blank">
                    {{ $merchant_details['merchant_url'] ?? 'N/A' }}
                </a>
            </p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p>
                <strong>Email:</strong>
                {{ $merchant_details['merchant_email'] ?? 'N/A' }}
            </p>
        </div>

        <div class="col-md-6">
            <p>
                <strong>Monthly Website Visitors:</strong>
                {{ $merchant_details['website_month_visit'] ?? 'N/A' }}
            </p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p>
                <strong>Key Point of Contact:</strong>
                {{ $merchant_details['contact_person_name'] ?? 'N/A' }}
            </p>
        </div>

        <div class="col-md-6">
            <p>
                <strong>Monthly Active Users:</strong>
                {{ $merchant_details['website_month_active'] ?? 'N/A' }}
            </p>
        </div>
    </div>


    <div class="row mb-3">
        <div class="col-md-6">
            <p>
                <strong>Key Point Mobile:</strong>
                {{ $merchant_details['contact_person_mobile'] ?? 'N/A' }}
            </p>
        </div>

        <div class="col-md-6">
            <p>
                <strong>Monthly Average Volume (QAR):</strong>
                {{ number_format($merchant_details['website_month_volume'] ?? 0, 2) }}
            </p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p>
                <strong>Existing Banking Partner:</strong>
                {{ $merchant_details['merchant_previous_bank'] ?? 'N/A' }}
            </p>
        </div>

        <div class="col-md-6">
            <p>
                <strong>Monthly Average No. Of Transactions:</strong>
                {{ $merchant_details['website_month_transaction'] ?? 'N/A' }}
            </p>
        </div>
    </div>

    <div class="row mb-3">

        <div class="col-md-12">
            <p><strong>Countries of Operation:</strong>
                @foreach($merchant_details['operating_countries'] as $country)
                    <span>{{ $country->country_name }}</span>@if(!$loop->last), @endif
                @endforeach
            </p>
        </div>
    </div>

</div>
</div>


<!-- Shareholders Section with Add Button -->
<div class="form-section box-container">
    <h4 class="mb-3 basic-details-header">Shareholders</h4>

    <!-- Container for all shareholders -->
    <div id="shareholders-container" class="form-section box-container mb-4">
        @if(!empty($merchant_details['shareholders']) && count($merchant_details['shareholders']) > 0)
            @foreach($merchant_details['shareholders'] as $shareholder)
            <div class="shareholder-entry row mb-3" style="border-bottom: 1px solid lightgray;">
                <div class="col-md-3">
                    <strong>First Name:</strong>
                    <p>{{ $shareholder['first_name'] ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Middle Name:</strong>
                    <p>{{ $shareholder['middle_name'] ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Last Name:</strong>
                    <p>{{ $shareholder['last_name'] ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Date of Birth:</strong>
                    <p> {{ $shareholder['dob'] ? \Carbon\Carbon::parse($shareholder['dob'])->format('Y-m-d') : 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Nationality:</strong>
                    <p>{{ $Country->firstWhere('id', $shareholder['country_id'])?->country_name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <strong>QID / National ID / Passport Name:</strong>
                    <p>{{ $shareholder['qid'] ?? 'N/A' }}</p>
                </div>


                <div class="col-md-3">
                    @if($shareholder['sanctions_check_status'] === 'success' )
                    <strong>Score in Sanction List: </strong>
                    <p>

                        <span class="badge bg-danger" style="padding: 8px 8px 9px 8px; box-shadow: 0 0.125rem 0.375rem 0 rgba(209, 0, 0, 0.3);">{{ $shareholder['sanctions_score'] * 100}} %</span>
                    <button class="btn btn-info btn-sm" onclick="openModal('{{ 'https://myprojects.multibizdev.com/fetch-data?shareholder_id=' . $shareholder['id'] }}')">View Details</button>
                    </p>
                    @endif
                </div>


            </div>
            @endforeach
        @else
        <p class="text-muted">No shareholders have been added yet.</p>
        @endif
    </div>
</div>





<!-- Modal for displaying the HTML page -->
<div class="modal fade  modal-lg" id="detailsModal" tabindex="-1"  aria-labelledby="detailsModalLabel" aria-hidden="true"><div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shareholderModalLabel">Shareholder Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Placeholder content to be updated by JavaScript -->
        <iframe id="detailsIframe" src="" width="100%" height="600px" frameborder="0"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script>
    // Function to open the modal and set the iframe source URL
    function openModal(url) {
        // Set the iframe src to the provided URL
        document.getElementById('detailsIframe').src = url;

        // Show the modal
        $('#detailsModal').modal('show');
    }
        function closeModal() {
        $('#detailsModal').modal('hide');
    }
</script>