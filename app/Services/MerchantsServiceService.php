<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\MerchantCategory;
use App\Models\MerchantDocument;
use App\Models\MerchantSale;
use App\Models\MerchantShareholder;
use App\Models\MerchantService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // Make sure this line is here
use App\Notifications\MerchantActivityNotification;


class MerchantsServiceService
{

    // public function getAllMerchants(): array
    // {
    //     $merchants = Merchant::with([
    //         'sales.addedBy', 'sales.approvedBy', 
    //         'services.addedBy', 'services.approvedBy', 
    //         'shareholders', 
    //         'documents.addedBy', 'documents.approvedBy',
    //         'addedBy', 'approvedBy'])->get()->toArray();
    //     return $merchants;
    // }
    
    public function getAllMerchants($merchantId = null): array 
        {
            // Build the query with eager loading for related data
            $query = Merchant::with([
                'sales.addedBy', 
                'sales.approvedBy', 
                'sales.declinedBy',
                'services.addedBy', 
                'services.approvedBy', 
                'services.declinedBy',
                'shareholders', 
                'documents.addedBy', 
                'documents.approvedBy',
                'documents.declinedBy',
                'addedBy', 
                'approvedBy',
                'declinedBy'
            ]);

            // Apply filtering if merchantId is provided
            if ($merchantId) {
                $query->where('id', $merchantId);
            }

            // dd($query->get()->toArray());
            // Fetch the data and convert to array
            return $query->get()->toArray();
        }

 
        public function createMerchants(array $data): Merchant
        {
            $merchant = new Merchant();
            $merchant->merchant_name = $data['merchant_name'];
            $merchant->merchant_name_ar = $data['merchant_arabic_name'];
            $merchant->comm_reg_no = $data['company_registration'];
            $merchant->address = $data['company_address'];
            $merchant->merchant_mobile = $data['mobile_number'];
            $merchant->merchant_category = $data['company_activities'];
            $merchant->merchant_landline = $data['landline_number'];
            $merchant->merchant_url = $data['website'];
            $merchant->merchant_email = $data['email'];
            $merchant->website_month_visit = $data['monthly_website_visitors'];
            $merchant->contact_person_name = $data['key_point_of_contact'];
            $merchant->website_month_active = $data['monthly_active_users'];
            $merchant->contact_person_mobile = $data['key_point_mobile'];
            $merchant->website_month_volume = $data['monthly_avg_volume'];
            $merchant->merchant_previous_bank = $data['existing_banking_partner'];
            $merchant->website_month_transaction = $data['monthly_avg_transactions'];
            $merchant->merchant_date_incorp = $data['date_of_incorporation'];
            $merchant->added_by = Auth::user()->id;
            $merchant->status = 'screening';
            $merchant->save();
        
            // Save operational countries
            if (isset($data['operating_countries'])) {
                $merchant->operating_countries()->sync($data['operating_countries']);
            }
        
            // Handle Shareholders
            $this->createShareholders($merchant, $data);
        
            return $merchant;
        }
        
        

  
        protected function createShareholders(Merchant $merchant, array $data): void
        {
            $firstNames = $data['shareholderFirstName'];
            $middleNames = $data['shareholderMiddleName'] ?? [];
            $lastNames = $data['shareholderLastName'];
            $dobs = $data['shareholderDOB'];
            $nationalities = $data['shareholderNationality'];
            $qids = $data['shareholderID'];

            foreach ($firstNames as $index => $firstName) {
                $shareholder = new MerchantShareholder();
                $shareholder->merchant_id = $merchant->id;
                $shareholder->first_name = $firstName;
                $shareholder->middle_name = $middleNames[$index] ?? null;
                $shareholder->last_name = $lastNames[$index];
                $shareholder->dob = $dobs[$index];
                $shareholder->country_id = $nationalities[$index];
                $shareholder->qid = $qids[$index] ?? null;
                $shareholder->added_by = Auth::user()->id ?? 1;
                $shareholder->status = 'active';

                // Combine first_name and last_name for the title
                $shareholder->title = $firstName . ' ' . $lastNames[$index];

                $shareholder->save();
            }
        }



    public function storeMerchantsSales(array $data, int $merchant_id): MerchantSale
    {
        $merchant_id = $merchant_id;  // Example merchant ID, replace with dynamic value if needed
     
         // Step 2: Create a new MerchantSale record using validated data
         $merchantSale = new MerchantSale();
         $merchantSale->merchant_id = $merchant_id;
         $merchantSale->min_transaction_amount = $data['minTransactionAmount'];
         $merchantSale->max_transaction_amount = $data['maxTransactionAmount'];
         $merchantSale->daily_limit_amount = $data['dailyLimitAmount'];
         $merchantSale->monthly_limit_amount = $data['monthlyLimitAmount'];
         $merchantSale->max_transaction_count = $data['maxTransactionCount'];
         $merchantSale->added_by = auth()->user()->id ?? 1;  // Use the authenticated user, default to 1 if not available
     
         // Save the merchant sale record
         $merchantSale->save();
         return $merchantSale;

    }


    public function storeMerchantsServices(array $data, int $merchant_id)
    {
        
        // Step 1: Iterate over the services and save each field in the merchant_services table
        foreach ($data['services'] as $service_id => $serviceData) {
            // Get the fields for this service
            $fields = $serviceData['fields'];
            
            // Save each field
            foreach ($fields as $index => $fieldValue) {
                MerchantService::create([
                    'merchant_id' => $merchant_id,
                    'service_id' => $service_id,
                    'field_name' => 'Field ' . $index, 
                    'field_value' => $fieldValue ?? '',
                    'added_by' => Auth::user()->id ?? 1, 
                    'status' => true, 
                ]);
            }
        }
    }


    public function updateMerchants(array $data, int $merchant_id): Merchant
    {
        // Find the existing merchant
        $merchant = Merchant::findOrFail($merchant_id);
    
        // Update merchant fields
        $merchant->update([
            'merchant_name' => $data['merchant_name'],
            'merchant_name_ar' => $data['merchant_arabic_name'],
            'comm_reg_no' => $data['company_registration'],
            'address' => $data['company_address'],
            'merchant_mobile' => $data['mobile_number'],
            'merchant_category' => $data['company_activities'],
            'merchant_landline' => $data['landline_number'],
            'merchant_url' => $data['website'],
            'merchant_email' => $data['email'],
            'website_month_visit' => $data['monthly_website_visitors'],
            'contact_person_name' => $data['key_point_of_contact'],
            'website_month_active' => $data['monthly_active_users'],
            'contact_person_mobile' => $data['key_point_mobile'],
            'website_month_volume' => $data['monthly_avg_volume'],
            'merchant_previous_bank' => $data['existing_banking_partner'],
            'website_month_transaction' => $data['monthly_avg_transactions'],
            'merchant_date_incorp' => $data['date_of_incorporation'],
            'added_by' => Auth::user()->id ?? 1,
            'declined_by' => null,
        ]);
    
        // Update the associated shareholders
        $this->updateShareholders($merchant, $data);
    
        // Update operating countries
        if (isset($data['operating_countries']) && is_array($data['operating_countries'])) {
            $merchant->operating_countries()->sync($data['operating_countries']);
        }
    
        return $merchant;
    }
    
    protected function updateShareholders(Merchant $merchant, array $data): void
    {
        // Use transaction to ensure data consistency
        DB::transaction(function () use ($merchant, $data) {
            // Delete existing shareholders
            $merchant->shareholders()->delete();
    
            // Re-create the shareholders with the updated data
            $this->createShareholders($merchant, $data);
        });
    }
    

    public function updateMerchantsSales(array $salesData, int $merchant_id)
    {
        // Delete all existing sales data for the merchant
        MerchantSale::where('merchant_id', $merchant_id)->delete();
    
        // Insert new sales data
        foreach ($salesData as $sale) {
            MerchantSale::create([
                'merchant_id' => $merchant_id,
                'min_transaction_amount' => $sale['minTransactionAmount'],
                'max_transaction_amount' => $sale['maxTransactionAmount'],
                'monthly_limit_amount' => $sale['monthlyLimitAmount'],
                'max_transaction_count' => $sale['maxTransactionCount'],
                'daily_limit_amount' => $sale['dailyLimitAmount'],
                'added_by' => Auth::user()->id ?? 1,
            ]);
        }

        MerchantService::where('merchant_id', $merchant_id)
        ->update(['approved_by' => null]);
    }
    


    public function updateMerchantsServices(array $servicesData, int $merchant_id)
    {
        foreach ($servicesData as $service_id => $serviceData) {
            // Validate if 'fields' key exists and is an array
            if (!isset($serviceData['fields']) || !is_array($serviceData['fields'])) {
                continue; // Skip invalid service data
            }
    
            // Delete existing data for the merchant_id and service_id
            MerchantService::where('merchant_id', $merchant_id)
                ->where('service_id', $service_id)
                ->delete();
    
            // Get the fields for the service
            $fields = $serviceData['fields'];
    
            // Iterate over each field and create new records
            foreach ($fields as $index => $fieldValue) {
                // Skip null or empty field values
                if (is_null($fieldValue) || $fieldValue === '') {
                    continue;
                }
    
                MerchantService::create([
                    'merchant_id' => $merchant_id,
                    'service_id' => $service_id,
                    'field_name' => 'Field ' . $index, // Name the fields dynamically
                    'field_value' => $fieldValue,
                    'added_by' => Auth::user()->id ?? 1, // Use authenticated user ID or fallback
                    'status' => true,
                ]);
            }
        }
    }
    

    public function deleteMerchants(int $merchant_id): void
    {
        Merchant::destroy($merchant_id);
    }



    public function checkMerchantShareholdersSanctionDetails(int $merchantId): array
    {
        // Fetch the merchant with its shareholders
        $merchant = Merchant::with('shareholders.country')->find($merchantId);

        if (!$merchant) {
            return [
                'success' => false,
                'message' => 'Merchant not found',
            ];
        }

        // Prepare API URL base
        $apiBaseUrl = 'https://portal.moi.gov.qa/wps/portal/NCTC/sanctionlist/unifiedsanctionlist/!ut/p/a1/hc29DsIgAATgZ_EJOIG2dqSkASKINSRWlobJkGh1MD6_-LOqt13yXY5EMpI4p3s-plu-zOn07LGeTEs51ZxaL7nAwDoTHHNQqirgUEClba_4GhvVhA6DpzrUO02B5b_9nsQ3Ec6Acljfy0JaHbRkwGrbfMCvixfAlwiQ63lENmLxAKkSZVg!/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_I9242H42LOC4A0Q3BITM3M0G85/res/id=getSanctionList/c=cacheLevelPage/=/?lang=en';

        $matchedResults = [];

       
        foreach ($merchant->shareholders as $shareholder) {
            // Extract shareholder details
            $firstName = $shareholder->first_name;
            $middleName = $shareholder->middle_name ?? '';
            $lastName = $shareholder->last_name;
            $qid = $shareholder->qid;
            $nationality = $shareholder->country->country_name ?? 'Unknown';

            try {
                // Fetch data from the API
                $response = Http::timeout(10)->get($apiBaseUrl);
                if ($response->successful()) {
                    $data = $response->json()['content'] ?? [];
            
                    // Normalize shareholder names into arrays for comparison
                    $inputFirstNames = array_map('strtolower', array_map('trim', explode(' ', $firstName)));
                    $inputMiddleNames = array_map('strtolower', array_map('trim', explode(' ', $middleName)));
                    $inputLastNames = array_map('strtolower', array_map('trim', explode(' ', $lastName)));
            
                    // Check for matching records
                    foreach ($data as $record) {
                        // Normalize API names into arrays
                        $apiFirstNames = array_map('strtolower', array_map('trim', explode(' ', $record['firstNameEN'] ?? '')));
                        $apiMiddleNames = array_map('strtolower', array_map('trim', explode(' ', $record['secondNameEN'] ?? '')));
                        $apiLastNames = array_map('strtolower', array_map('trim', explode(' ', $record['thirdNameEN'] ?? '')));
                        $apiNationality = $record['nationality'] ?? 'Unknown';
            
                      
                        // Match if any part of the shareholder name exists in the API response
                        $firstNameMatch = !empty(array_intersect($inputFirstNames, $apiFirstNames));
                        $middleNameMatch = empty($inputMiddleNames) || !empty(array_intersect($inputMiddleNames, $apiMiddleNames));
                        $lastNameMatch = !empty(array_intersect($inputLastNames, $apiLastNames));
                        $nationalityMatch = $nationality === $apiNationality;
                        
                        // Check if all conditions are met
                        if ($firstNameMatch && $lastNameMatch && $middleNameMatch && $nationalityMatch) {
                            $matchedResults[] = [
                                'shareholder' => [
                                    'first_name' => $firstName,
                                    'middle_name' => $middleName,
                                    'last_name' => $lastName,
                                    'qid' => $qid,
                                    'country_name' => $nationality,
                             
                                ],
                                'matched_record' => $record,
                            ];
                            $shareholder->moi = 1;
                            $shareholder->save();
                        }
                    }
                } else {
                    \Log::error('Failed to fetch sanction list data. Status: ' . $response->status());
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching sanction details for shareholder: ' . $firstName . ', Error: ' . $e->getMessage());
            }
            
            
        }

        return $matchedResults;
    }



    public function checkAndUpdateSanctionList(int $merchantId): array
    {
        $merchant = Merchant::with('shareholders')->find($merchantId);
    
        if (!$merchant) {
            return [
                'success' => false,
                'message' => 'Merchant not found',
            ];
        }
    
        $results = [];
    
        foreach ($merchant->shareholders as $shareholder) {
            $firstName = $shareholder->first_name;
            $middleName = $shareholder->middle_name;
            $lastName = $shareholder->last_name;
            $dob = $shareholder->dob;
    
            $fullName = "{$firstName} {$middleName} {$lastName}";

            $sanctionedShareholders = \DB::table('data_table')
            ->where(function($query) use ($firstName, $middleName, $lastName, $fullName) {
                // First condition: Match Name 6 and check against full name
                $query->where('key_name', 'like', '%Name 6%')
                      ->where('key_value', 'like', "%{$fullName}%")
                
                // Second condition: Match '1:' and check against first name
                      ->orWhere(function($query) use ($firstName) {
                          $query->where('key_name', 'like', '%1:%')
                                ->where('key_value', 'like', "%{$firstName}%");
                      })
                      ->orWhere(function($query) use ($middleName) {
                        $query->where('key_name', 'like', '%2:%')
                              ->where('key_value', 'like', "%{$middleName}%");
                    })
                    ->orWhere(function($query) use ($lastName) {
                        $query->where('key_name', 'like', '%3:%')
                              ->where('key_value', 'like', "%{$lastName}%");
                    });

            })
            ->get();
        
        
            dd( $sanctionedShareholders);
    
            if ($sanctionedShareholders->isNotEmpty()) {
                $shareholder->sanctionlist = 1;
                $shareholder->save();
    
                $results[] = [
                    'shareholder' => [
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'dob' => $dob,
                    ],
                    'status' => 'Updated to Sanction List',
                ];
            } else {
                $results[] = [
                    'shareholder' => [
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'dob' => $dob,
                    ],
                    'status' => 'No Match Found',
                ];
            }
        }
    
        return [
            'success' => true,
            'message' => 'Sanction list check completed.',
            'results' => $results,
        ];
    }
    


   

// public function checkAndUpdateSanctionList(int $merchantId): array
// {
//     $merchant = Merchant::with('shareholders')->find($merchantId);

//     if (!$merchant) {
//         return [
//             'success' => false,
//             'message' => 'Merchant not found',
//         ];
//     }

//     $results = [];

//     // Fetch the HTML content from the URL
//     $url = 'https://ofsistorage.blob.core.windows.net/publishlive/2022format/ConList.html';
//     $response = Http::get($url);

//     if (!$response->successful()) {
//         return [
//             'success' => false,
//             'message' => 'Failed to fetch sanction list.',
//         ];
//     }

//     $htmlContent = $response->body();

//     // Use DOMDocument to parse the HTML content
//     $dom = new \DOMDocument;
//     @$dom->loadHTML($htmlContent);  // suppress warnings for invalid HTML structure

//     // Find all 'td' or any other element containing the DOB in the sanction list
//     $nodes = $dom->getElementsByTagName('td'); // Assuming the names are in <td> tags

//     $sanctionList = [];

//     // Loop through all nodes and extract DOB and Name
//     foreach ($nodes as $node) {
//         $keyValue = trim($node->nodeValue);

//         // Look for DOB in the content
//         if (strpos($keyValue, 'DOB:') !== false) {
//             // Extract DOB from the record (Assuming DOB is always after 'DOB:')
//             preg_match('/DOB:\s*([0-9]{2}\/[0-9]{2}\/[0-9]{4})/', $keyValue, $matches);
//             if (isset($matches[1])) {
//                 $sanctionList[] = [
//                     'dob' => $matches[1], // Store DOB from the sanction list
//                     'record' => $keyValue, // Store the entire record for matching
//                 ];
//             }
//         }
//         if (strpos($keyValue, 'Name 6:') !== false || strpos($keyValue, '1:') !== false) {
//             $sanctionList[] = [
//                 'name' => $keyValue, // Store the full Name 6 or 1: field for matching
//                 'record' => $keyValue, // Store the entire record for matching
//             ];
//         }
//     }

//     // Now match the shareholder's DOB with the records in the sanction list
//     foreach ($merchant->shareholders as $shareholder) {
//         $dob = $shareholder->dob; // Get DOB from the shareholder

//         // Look for matching DOB in the sanction list
//         $matchedRecord = null;
//         foreach ($sanctionList as $sanctionRecord) {
//             if ($dob === $sanctionRecord['dob']) {
//                 $matchedRecord = $sanctionRecord['record'];
//                 break;
//             }
//         }

//         if ($matchedRecord) {
//             // If a match is found, return the record and update shareholder
//             $results[] = [
//                 'shareholder' => [
//                     'first_name' => $shareholder->first_name,
//                     'middle_name' => $shareholder->middle_name,
//                     'last_name' => $shareholder->last_name,
//                     'dob' => $dob,
//                 ],
//                 'status' => 'Match Found',
//                 'sanction_record' => $matchedRecord,
//             ];
//         } else {
//             $results[] = [
//                 'shareholder' => [
//                     'first_name' => $shareholder->first_name,
//                     'middle_name' => $shareholder->middle_name,
//                     'last_name' => $shareholder->last_name,
//                     'dob' => $dob,
//                 ],
//                 'status' => 'No Match Found',
//             ];
//         }
//     }

//     dd($results);
//     return [
//         'success' => true,
//         'message' => 'Sanction list check completed.',
//         'results' => $results,
//     ];
// }


public function hasMoiFlag(int $merchantId): bool
{

    $merchant = Merchant::with('shareholders')->find($merchantId);
    
    if (!$merchant) {
        return false; // Merchant not found, return false
    }

    // Check if any shareholder has moi = 1
    foreach ($merchant->shareholders as $shareholder) {
        if ($shareholder->moi == 1) {
            return true;
        }
    }

    return false; 
}


}
