<div class="form-section box-container">
    <!-- Services Section -->
    @foreach($services as $service)
    <h4 class="mb-3 basic-details-header ">{{ ucfirst($service['name']) }}</h4>
    <div class="form-section box-container">
     

        <!-- Display the fields for each service -->
        @php
            $fields = json_decode($service['fields'], true);
        @endphp

        {{-- @if($fields)
            @foreach($fields as $index => $field)
            <div class="mb-3">
                <label for="service_{{ $service['id'] }}_field_{{ $index }}" class="form-label">{{ ucfirst($field) }}</label>
                <input type="text" 
                       class="form-control" 
                       id="service_{{ $service['id'] }}_field_{{ $index }}" 
                       name="services[{{ $service['id'] }}][fields][{{ $index }}]"
                       value="{{ isset($merchant_details['services'][$index]['field_value']) ? $merchant_details['services'][$index]['field_value'] : '' }}"
                       placeholder="{{ ucfirst($field) }}"
                       disabled> <!-- Added disabled here -->
            </div>
            @endforeach
        @endif --}}

         @if($fields)
            @foreach($fields as $index => $field)
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>{{ ucfirst($field) }}:</strong> 
                    {{ $merchant_details['services'][$index]['field_value'] ?? 'N/A' }}</p>
                </div>
            </div>
            @endforeach
        @else
            <p class="text-muted">No fields available for this service.</p>
        @endif

    </div>
    @endforeach
</div>


