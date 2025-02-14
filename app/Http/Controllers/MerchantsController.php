<?php

namespace App\Http\Controllers;
use App\Models\Merchant;
use App\Models\MerchantCategory;
use App\Models\MerchantDocument;
use App\Models\MerchantSale;
use App\Models\MerchantShareholder;
use App\Models\MerchantService;
use App\Services\DocumentsService;
use App\Models\Document;
use App\Models\Service;
use App\Models\Country;
use App\Models\User;
use App\Services\MerchantsServiceService;
use App\Notifications\MerchantActivityNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\GraphMailersSender;
use Illuminate\Support\Facades\Mail;

use Exception;

use Illuminate\Http\Request;

class MerchantsController extends Controller
{

    protected $merchantsService;
    protected $notificationService;
    protected $documentsService;
    protected $graphMailersSender;

    public function __construct(MerchantsServiceService $merchantsService,
     NotificationService $notificationService, DocumentsService $documentsService,
     GraphMailersSender $graphMailersSender)
    {
        $this->merchantsService = $merchantsService;
        $this->notificationService = $notificationService;
        $this->documentsService = $documentsService;
        $this->graphMailersSender = $graphMailersSender;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all merchants using service layer
        $merchants = $this->merchantsService->getAllMerchants();

        return view('pages.merchants.merchants-list', compact('merchants'));
    }


    // Method to preview merchant details
    public function preview(Request $request)
    {
          $title  = 'Preview Merchants Details';
        $merchantId = $request->input('merchant_id');
        $merchant_details = Merchant::with(['sales', 'services', 'shareholders', 'documents', 'operating_countries'])->where('id', $merchantId)->first();
        $merchant = $this->merchantsService->getAllMerchants($merchantId);
        $MerchantCategory = MerchantCategory::all();
        $Country = Country::all();
        $all_documents  = Document::all();
        $services = Service::all();

        return view('pages.merchants.merchants-preview', compact('merchant_details','title','MerchantCategory','Country','all_documents','services','merchant'));
    }




       /**
     * Show the form for creating a new resource.
     */
    public function create_merchants_kfc(Request $request)
    {
        $title = 'Create Merchants KYC';
        $MerchantCategory = MerchantCategory::all();
        $Country = Country::all();


        if ($request->has('merchant_id')) {
            $merchant_id = $request->input('merchant_id');
            $merchant_details = Merchant::with(['sales', 'services', 'shareholders', 'documents'])
                ->where('id', $merchant_id)
                ->first();

            if ($merchant_details) {

                return redirect()->route('edit.merchants.kyc', ['merchant_id' => $merchant_details->id])
                    ->with('info', 'Merchant already exists. Redirecting to edit page.');
            }
        }

        if (!auth()->user()->can('addKYC', auth()->user())) {
            return redirect()->back()->with('error', 'You are not authorized.');
        }

        return view('pages.merchants.create.create-merchants', compact('title', 'MerchantCategory', 'Country'));
    }



    public function create_merchants_documents(Request $request)
    {
        $title = 'Create Merchants Documents';
        $merchant_documents = Document::all();
        $merchant_details = null;

        if ($request->has('merchant_id')) {
            $merchant_id = $request->input('merchant_id');
            $merchant_details = Merchant::with(['sales', 'services', 'shareholders', 'documents'])
                ->where('id', $merchant_id)
                ->first();
             $merchant_shareholders = MerchantShareholder::where('merchant_id', $merchant_id)->get();

             if (is_null($merchant_details->approved_by)) {
                return redirect()->back()->with('error', 'kyc not approved yet.');
             }

            if ($merchant_details && !$merchant_details->documents->isEmpty()) {
                return redirect()->route('edit.merchants.documents', ['merchant_id' => $merchant_id])
                    ->with('info', 'Documents already exists. Redirecting to edit page.');
            }
        }

        if (auth()->user()->can('addDocuments', auth()->user())) {
            return view('pages.merchants.create.create-merchants-documents', compact('merchant_documents', 'title', 'merchant_shareholders'));
        } else {
            return redirect()->back()->with('error', 'You are not authorized.');
        }
    }








    public  function create_merchants_sales(Request $request){

        $title = 'Create Merchants Sales';
        $merchant_details = null;

        if ($request->has('merchant_id')) {
            $merchant_id = $request->input('merchant_id');
            $merchant_details = Merchant::with(['sales', 'services', 'shareholders', 'documents'])
                ->where('id', $merchant_id)
                ->first();

            if ($merchant_details && $merchant_details->approved_by === null) {
                return redirect()->back()->with('error', 'kyc not approved yet.');
            }

            if ($merchant_details && $merchant_details->documents->every(fn($doc) => $doc->approved_by === null)) {
                return redirect()->back()->with('error', 'Documents not approved yet.');
            }

            if ($merchant_details && $merchant_details->sales->isNotEmpty()) {
                return redirect()->route('edit.merchants.sales', ['merchant_id' => $merchant_id])
                    ->with('info', 'Sales data already exists. Redirecting to edit page.');
            }
        }


        if (!auth()->user()->can('addSales', auth()->user()))
            {
               return redirect()->back()->with('error', 'You are not authorized.');
            }
        return view('pages.merchants.create.create-merchants-sales', compact('title'));
    }

    public  function create_merchants_services(Request $request){


        $services = Service::all();
        $title = 'Create Merchants Services';

            if ($request->has('merchant_id')) {
            $merchant_id = $request->input('merchant_id');
            $merchant_details = Merchant::with(['sales', 'services', 'shareholders', 'documents'])
                ->where('id', $merchant_id)
                ->first();

                if ($merchant_details && $merchant_details->approved_by === null) {
                    return redirect()->back()->with('error', 'kyc not approved yet.');
                }

                if ($merchant_details && $merchant_details->documents->every(fn($doc) => $doc->approved_by === null)) {
                    return redirect()->back()->with('error', 'Documents not approved yet.');
                }

                if ($merchant_details && $merchant_details->sales->every(fn($sale) => $sale->approved_by === null) ) {
                    return redirect()->back()->with('error', 'Sales not approved yet.');
                }
            if ( $merchant_details->services->isNotEmpty()) {
                return redirect()->route('edit.merchants.services', ['merchant_id' => $merchant_id])
                    ->with('info', 'Services data already exists. Redirecting to edit page.');
            }
        }


        if (!auth()->user()->can('addServices', auth()->user()))
        {
           return redirect()->back()->with('error', 'You are not authorized.');
        }
        return view('pages.merchants.create.create-merchants-services', compact('services', 'title'));
    }
    /**
     * Store a newly created resource in storage.
     */


     public function store_merchants_kyc(Request $request)
     {
         // Validate the request
         $validatedData = $request->validate([
             'merchant_name' => 'required|string|max:255',
             'date_of_incorporation' => 'required|date',
             'merchant_arabic_name' => 'required|string|max:255',
             'company_registration' => 'required|string|max:255',
             'company_address' => 'required|string',
             'mobile_number' => 'required|string|max:15',
             'company_activities' => 'required|integer',
             'landline_number' => 'required|string|max:15',
             'website' => 'nullable|url',
             'email' => 'required|email|unique:merchants,merchant_email',
             'monthly_website_visitors' => 'nullable|integer',
             'key_point_of_contact' => 'required|string|max:255',
             'monthly_active_users' => 'nullable|integer',
             'key_point_mobile' => 'required|string|max:15',
             'monthly_avg_volume' => 'nullable|integer',
             'existing_banking_partner' => 'nullable|string|max:255',
             'monthly_avg_transactions' => 'required|integer',
             'shareholderFirstName.*' => 'required|string|max:255',
             'shareholderMiddleName.*' => 'nullable|string|max:255',
             'shareholderLastName.*' => 'required|string|max:255',
             'shareholderDOB.*' => 'required|date',
             'shareholderNationality.*' => 'required|integer|exists:countries,id',
             'shareholderID.*' => 'nullable|string|max:255',
             'operating_countries' => 'required|array|min:1',
             'operating_countries.*' => 'integer|exists:countries,id',
         ]);

         try {
             // Create the merchant using the service
             $merchant = $this->merchantsService->createMerchants($validatedData);

             // Notify about KYC creation
                //  $this->notificationService->storeMerchantsKYC($merchant);

                $this->graphMailersSender->sendcreationMail($merchant->id,'Your Merchant Have Been Created', 1);

             return redirect() ->route('edit.merchants.kyc', ['merchant_id' => $merchant->id])->with('success', 'Merchant and Shareholders successfully added.');
         } catch (\Exception $e) {

             // Redirect back with an error message
             return redirect()->back()->with('error', 'An error occurred while adding the merchant. Please try again.');
         }
     }


     public function store_merchants_documents(Request $request)
     {


         $validatedData = $request->validate([
             'document_*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
             'expiry_*' => 'nullable|date',
         ]);




         $merchant = $request->input('merchant_id');

         $merchant_id = $merchant['id'] ?? $request->input('merchant_id');

         foreach ($request->all() as $key => $value) {
            if (strpos($key, 'document_') === 0 && $request->hasFile($key)) {
                $keyParts = explode('_', $key);

                if (count($keyParts) === 2) {
                    $document_id = $keyParts[1];
                    $shareholder_id = null;
                    $shareholder_name = null;
                    $expiryDate = null;
                } elseif (count($keyParts) >= 4) {
                    $document_id = $keyParts[1];
                    $shareholder_id = $keyParts[2];
                    $shareholder_name = implode('_', array_slice($keyParts, 3));
                    $expiryDateKey = 'expiry_' . $document_id . '_' . $shareholder_id . '_' . $shareholder_name;
                    $expiryDate = $request->input($expiryDateKey, null);
                } else {
                    continue;
                }

                $file = $request->file($key);

                // Use Laravel's store method to save the file in the 'public/documents' directory
                $filePath = $file->storeAs('documents', $document_id . '_' . ($shareholder_name ? $shareholder_name . '_' : '') . $file->getClientOriginalName(), 'public');
                 File::copy(storage_path('app/public/' . $filePath), public_path('storage/' . $filePath));

                // Save the document information to the database
                MerchantDocument::create([
                    'title' => basename($filePath),
                    'document' => 'storage/' . $filePath, // Store the relative path for easier retrieval
                    'date_expiry' => $expiryDate,
                    'merchant_id' => $merchant_id,
                    'added_by' => auth()->user()->id,
                    'document_type' => $file->getClientMimeType(),
                    'emailed' => false,
                    'status' => true,
                    'shareholders_id' => $shareholder_id,
                ]);
            }
        }


         $this->notificationService->storeMerchantsDocuments($merchant_id);
         $this->graphMailersSender->sendcreationMail($merchant_id,'Your Merchant Documents Have Been Created', 2);

         return redirect()->route('edit.merchants.documents', ['merchant_id' => $merchant_id])
             ->with('success', 'Documents uploaded and saved successfully.')
             ->withInput($request->all());
     }



     public function store_merchants_sales(Request $request)
     {
         // Step 1: Validate the form input
         $validatedData = $request->validate([
             'minTransactionAmount' => 'required|numeric',
             'monthlyLimitAmount' => 'required|numeric',
             'maxTransactionAmount' => 'required|numeric',
             'maxTransactionCount' => 'required|integer',
             'dailyLimitAmount' => 'required|numeric',
         ]);

         $merchant = $request->input('merchant_id');
         $merchant_id = $merchant['id'] ?? $request->input('merchant_id');


         $this->merchantsService->storeMerchantsSales($validatedData, $merchant_id);

         $this->notificationService->storeMerchantsSales($merchant_id);
         $this->graphMailersSender->sendcreationMail($merchant_id,'Your Merchant Sales Have Been Created', 3);

        return redirect()->route('edit.merchants.sales', ['merchant_id' => $merchant_id])
        ->with('success', 'Merchant sales data saved successfully.')->withInput($request->all());
     }



     public function store_merchants_services(Request $request)
     {


         // Step 1: Validate the incoming data
         $validatedData = $request->validate([
            'services.*.fields.*' => 'nullable|string',
        ]);



         $merchant = $request->input('merchant_id');
         $merchant_id = $merchant['id'] ?? $request->input('merchant_id');
         // Step 2: Use the service to save the merchant services data
         $this->merchantsService->storeMerchantsServices($validatedData, $merchant_id);
         $this->notificationService->storeMerchantsServices($merchant_id);
         $this->graphMailersSender->sendcreationMail($merchant_id,'Your Merchant Services Have Been Created', 4);



        return redirect()->route('edit.merchants.services', ['merchant_id' => $merchant_id])
        ->with('success', 'Services data saved successfully.')->withInput($request->all());
     }





    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit_merchants_kyc(Request $request)
    {
        $merchant_id = $request->input('merchant_id');

        $title = 'Edit Merchants Details';
        $merchant_details = Merchant::with(['sales', 'services', 'shareholders', 'documents', 'operating_countries'])->where('id', $merchant_id)->first();
        $MerchantCategory = MerchantCategory::all();
        $Country = Country::all();

        if (!$merchant_details) {
            return redirect()->route('create.merchants.kfc', ['merchant_id' => $merchant_id]);
        }

        // Convert operating_countries to an array of IDs
        $merchant_details->operating_countries = $merchant_details->operating_countries->pluck('id')->toArray();

        if (auth()->user()->can('changeKYC', auth()->user())) {

                $result = $this->merchantsService->checkMerchantShareholdersSanctionDetails($merchant_id);
                // $this->merchantsService->checkAndUpdateSanctionList($merchant_id);

            return view('pages.merchants.edit.edit-merchants', compact('merchant_details',
                'title', 'MerchantCategory', 'Country', 'result'));
        } else {
            return redirect()->back()->with('error', 'You are not authorized.');
        }
    }


    public function edit_merchants_documents(Request $request)
    {

        $title = 'Edit Merchants Details';

        $merchant_id = $request->input('merchant_id');
        $merchant_details = Merchant::with(['documents', 'sales', 'services', 'shareholders'])->where('id', $merchant_id)->first();
        $all_documents  = Document::all();


        if ($merchant_details->documents->isEmpty()) {
            return redirect()->route('create.merchants.documents', ['merchant_id' => $merchant_id]);
        }
        if (auth()->user()->can('changeDocuments', auth()->user()))
        {
            if (is_null($merchant_details->approved_by)) {
                return redirect()->back()->with('error', 'kyc not approved yet.');
                }

            return view('pages.merchants.edit.edit-merchants-documents', compact('merchant_details', 'title', 'all_documents'));
        }else{
            return redirect()->back()->with('error', 'You are not authorized.');
        }
    }



    public function edit_merchants_sales (Request $request)
    {
        $merchant_id = $request->input('merchant_id');

        $title = 'Edit Merchants Sales';
        $merchant_details = Merchant::with(['sales', 'services', 'shareholders', 'documents'])->where('id', $merchant_id)->first();

        if ($merchant_details->sales->isEmpty()) {
            return redirect()->route('create.merchants.sales', ['merchant_id' => $merchant_id]);
        }
        if (auth()->user()->can('changeSales', auth()->user()))
        {

        if ($merchant_details && !$merchant_details->documents->every(fn($doc) => $doc->approved_by !== null)) {
            return redirect()->back()->with('error', 'Documents not approved yet.');
        }
        return view('pages.merchants.edit.edit-merchants-sales', compact('merchant_details', 'title'));
        }else{
            return redirect()->back()->with('error', 'You are not authorized.');
        }
    }

    public function edit_merchants_services(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        $title = 'Edit Merchants Services';
        $merchant_details = Merchant::with(['services', 'shareholders', 'documents', 'sales'])->where('id', $merchant_id)->first();
        $services = Service::all();
        $userStage = auth()->user()->getDepartmentStage(auth()->user()->department);


        if ($merchant_details->services->isEmpty()) {
            return redirect()->route('create.merchants.services', ['merchant_id' => $merchant_id]);
        }
        if (auth()->user()->can('changeServices', auth()->user()))
        {
            if ($merchant_details && !$merchant_details->sales->every(fn($sale) => $sale->approved_by !== null) ) {
                return redirect()->back()->with('error', 'Sales not approved yet.');
            }
            return view('pages.merchants.edit.edit-merchants-services', compact('merchant_details', 'title', 'services'));
        }else{
            return redirect()->back()->with('error', 'You are not authorized.');
        }

    }
    /**
     * Update the specified resource in storage.
     */
    public function update_merchants_kyc(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'merchant_name' => 'required|string|max:255',
            'date_of_incorporation' => 'required|date',
            'merchant_arabic_name' => 'required|string|max:255',
            'company_registration' => 'required|string|max:255',
            'company_address' => 'required|string',
            'mobile_number' => 'required|string|max:15',
            'company_activities' => 'required|integer',
            'landline_number' => 'required|string|max:15',
            'website' => 'nullable|url',
            'email' => 'required|email',
            'monthly_website_visitors' => 'nullable|integer',
            'key_point_of_contact' => 'required|string',
            'monthly_active_users' => 'nullable|integer',
            'key_point_mobile' => 'required|string|max:15',
            'monthly_avg_volume' => 'nullable|integer',
            'existing_banking_partner' => 'nullable|string',
            'monthly_avg_transactions' => 'required|integer',
            'shareholderFirstName.*' => 'required|string|max:255',
            'shareholderMiddleName.*' => 'nullable|string|max:255',
            'shareholderLastName.*' => 'required|string|max:255',
            'shareholderDOB.*' => 'required|date',
            'shareholderNationality.*' => 'required|integer',
            'shareholderID.*' => 'nullable|string|max:255',
            'operating_countries' => 'required|array|min:1',
            'operating_countries.*' => 'integer|exists:countries,id',
        ]);

        // Retrieve the merchant ID
        $merchant_id = $request->input('merchant_id');
        $merchant = Merchant::findOrFail($merchant_id);

        // Authorization check
        if (auth()->user()->role === 'user' && $merchant->approved_by !== null) {
            return redirect()->back()->with('error', 'You are not authorized to edit this KYC as it has already been approved.');
        }

        // Save operating countries
        if (isset($validatedData['operating_countries'])) {
            $merchant->operating_countries()->sync($validatedData['operating_countries']);
        }

        // Use the service to update merchant
        $this->merchantsService->updateMerchants($validatedData, $merchant_id);
        $this->notificationService->storeMerchantsKYC($merchant);


        // Reset approvals for the merchant
        $merchant->update(['approved_by' => null]);

        // Reset approvals for documents
        $merchant->documents->each(function ($document) {
            $document->update(['approved_by' => null]);
        });

        // Reset approvals for sales and services
        MerchantSale::where('merchant_id', $merchant_id)->update(['approved_by' => null]);
        MerchantService::where('merchant_id', $merchant_id)->update(['approved_by' => null]);

        // Reset decline notes
        session()->forget('print_decline_notes');
        $this->graphMailersSender->sendcreationMail($merchant_id,'Your Merchant Have Been updated', 1);
        // Redirect back with a success message
        return redirect()->back()->with('success', 'Merchant and Shareholders successfully updated.');
    }





    public function update_merchants_documents(Request $request)
    {

        try {
            $validatedData = $request->validate([
                'document_*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
                'expiry_*' => 'nullable|date',
                'replace_document_*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
                'replace_expiry_*' => 'nullable|date',
            ]);
            $merchant_id = $request->input('merchant_id');
            // Use the correctly cased property
            $this->documentsService->updateDocuments($validatedData, $request);
            $this->notificationService->storeMerchantsDocuments($merchant_id);
            $this->graphMailersSender->sendcreationMail($merchant_id,'Your Merchant Documents Have Been updated', 2);
            return redirect()->back()->with('success', 'Documents successfully updated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }




    public function update_merchants_sales(Request $request)
    {
        // Step 1: Validate the request data
        $validatedData = $request->validate([
            'sales.*.minTransactionAmount' => 'required|numeric',
            'sales.*.monthlyLimitAmount' => 'required|numeric',
            'sales.*.maxTransactionAmount' => 'required|numeric',
            'sales.*.maxTransactionCount' => 'required|integer',
            'sales.*.dailyLimitAmount' => 'required|numeric',
        ]);

        // Step 2: Retrieve the merchant details
        $merchant = $request->input('merchant_id');

        $merchant_id = $merchant['id'] ?? $request->input('merchant_id');

        $merchant = Merchant::with(['sales'])->find($merchant_id);



        // Step 3: Authorization Check for 'user' role
        if (
            auth()->user()->role === 'user' &&
            $merchant &&
            $merchant->sales->every(fn($sales) => $sales->approved_by !== null)
        ) {
            return redirect()->back()->with('error', 'You are not authorized to edit these sales as they have already been approved.');
        }

        // Step 4: Update Merchant Sales
        $this->merchantsService->updateMerchantsSales($validatedData['sales'], $merchant_id);
        $this->notificationService->storeMerchantsSales($merchant_id);
        session()->forget('print_decline_notes');
        $this->graphMailersSender->sendcreationMail($merchant_id,'Your Merchant Sales Have Been updated', 3);
        return redirect()->back()->with('success', 'Merchant sales data successfully updated.');
    }


    public function update_merchants_services(Request $request)
    {
        // Step 1: Validate the request data
        $validatedData = $request->validate([
            'services.*.fields.*' => 'nullable|string',
        ]);


        // Step 2: Retrieve the merchant details
        $merchant_id = $request->input('merchant_id');
        $merchant = Merchant::with(['services'])->find($merchant_id);

        // Step 3: Check authorization for 'user' role
        if (
            auth()->user()->role === 'user' &&
            $merchant &&
            $merchant->services->every(fn($service) => $service->approved_by !== null)
        ) {
            return redirect()->back()->with('error', 'You are not authorized to edit these services as they have already been approved.');
        }

        // Step 4: Update merchant services
        $this->merchantsService->updateMerchantsServices($validatedData['services'], $merchant_id);
        $this->notificationService->storeMerchantsServices($merchant_id);
        session()->forget('print_decline_notes');
        $this->graphMailersSender->sendcreationMail($merchant_id,'Your Merchant Services Have Been updated', 4);
        return redirect()->back()->with('success', 'Merchant services data successfully updated.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Use the service to delete the merchant
        $this->merchantsService->deleteMerchants($id);

        // Redirect with a success message
        return redirect()->route('merchants.index')->with('success', 'Merchant deleted successfully.');

    }



    public function approve_merchants($id)
    {
        $merchant_details = Merchant::with(['documents', 'sales', 'services', 'shareholders'])->where('id', $id)->first();


        // Step 1: Approve Merchant KYC
        if ($merchant_details && is_null($merchant_details->approved_by)) {
            return redirect()->route('approve.merchants.kyc', ['merchant_id' => $id]);
        }
        if ($merchant_details && !is_null($merchant_details->approved_by)
            && $merchant_details->documents->isEmpty()
            && $merchant_details->sales->isEmpty()
            && $merchant_details->services->isEmpty()) {
            return redirect()->back()->with('success', 'KYC already approved, but no documents, sales, or services are associated with this merchant.');
        }


        // Step 2: Approve Documents
        if (
            $merchant_details &&
            $merchant_details->documents->isNotEmpty() &&
            !is_null($merchant_details->approved_by) &&
            $merchant_details->documents->every(fn($doc) => is_null($doc->approved_by))
        ) {
            return redirect()->route('approve.merchants.documents', ['merchant_id' => $id]);
        }

        if ($merchant_details && !is_null($merchant_details->approved_by)
            && $merchant_details->documents->isNotEmpty()
            && $merchant_details->documents->every(fn($doc) => !is_null($doc->approved_by))
            && $merchant_details->sales->isEmpty()
            && $merchant_details->services->isEmpty()) {
        return redirect()->back()->with('success', 'Merchant documents already approved, but no sales, or services are associated with this merchant.');
         }


        // Step 3: Approve Sales
        if (
            $merchant_details &&
            $merchant_details->sales->isNotEmpty() &&
            !is_null($merchant_details->approved_by) &&
            $merchant_details->documents->every(fn($doc) => !is_null($doc->approved_by)) &&
            $merchant_details->sales->every(fn($sale) => is_null($sale->approved_by))
        ) {
            return redirect()->route('approve.merchants.sales', ['merchant_id' => $id]);
        }

        if ($merchant_details && !is_null($merchant_details->approved_by)
            && $merchant_details->documents->isNotEmpty()
            && $merchant_details->documents->every(fn($doc) => !is_null($doc->approved_by))
            && $merchant_details->sales->isNotEmpty()
            && $merchant_details->sales->every(fn($sale) => !is_null($sale->approved_by))
            && $merchant_details->services->isEmpty()) {

        return redirect()->back()->with('success', 'Merchant Sales already approved, but no services are associated with this merchant.');
        }


        // Step 4: Approve Services
        if (
            $merchant_details &&
            $merchant_details->services->isNotEmpty() &&
            !is_null($merchant_details->approved_by) &&
            $merchant_details->documents->every(fn($doc) => !is_null($doc->approved_by)) &&
            $merchant_details->sales->every(fn($sale) => !is_null($sale->approved_by)) &&
            $merchant_details->services->every(fn($service) => is_null($service->approved_by))
        ) {
            return redirect()->route('approve.merchants.services', ['merchant_id' => $id]);
        }



        return redirect()->back()->with('success', 'Merchant Completed, documents, sales, and services approved successfully.');
    }


    // public function approveKYC(Request $request){
    //     $merchant_id = $request->input('merchant_id');
    //     $merchant = Merchant::with('documents')->find($merchant_id);
    //     $addedByUser = User::find($merchant->added_by);
    //     if($addedByUser->role == 'frontendUser'){
    //         $this->notificationService->approveKYCFrontendUser($merchant_id);
    //         }else{
    //          $this->notificationService->approveKYC($merchant_id);
    //         }
    //     return redirect()->back()->with('success', 'KYC approved successfully.');
    // }


    public function approveKYC(Request $request)
    {
            $merchant_id = $request->input('merchant_id');

            // Find the merchant along with its related documents
            $merchant = Merchant::with('documents')->find($merchant_id);

            if (!$merchant) {
                return redirect()->back()->with('error', 'Merchant not found.');
            }

            // Find the user who added the merchant
            $addedByUser = User::find($merchant->added_by);

            if (!$addedByUser) {
                return redirect()->back()->with('error', 'User who added the merchant not found.');
            }

            // Notify based on the user's role
            if ($addedByUser->role === 'frontendUser') {
                $this->notificationService->approveKYCFrontendUser($merchant_id);
            } else {
                $this->notificationService->approveKYC($merchant_id);
            }

        $this->graphMailersSender->sendapprovalMail($merchant_id, 'Your Merchant Have Been Approved', 2);

        return redirect()->back()->with('success', 'KYC approved successfully and email sent.');

    }



    public  function approve_merchants_documents(Request $request){
        $merchant_id = $request->input('merchant_id');
        $merchant = Merchant::with('documents')->find($merchant_id);
        $addedByUser = User::find($merchant->added_by);
        if($addedByUser->role == 'frontendUser'){
            $this->notificationService->approveMerchantsDocumentsFrontendUser($merchant_id);
            }
        $this->notificationService->approveMerchantsDocuments($merchant_id);



        $this->graphMailersSender->sendapprovalMail($merchant_id, 'Your Merchant Documents Have Been Approved', 3);

        return redirect()->back()->with('success', 'Merchant documents approved successfully.');
    }

    public  function approve_merchants_sales(Request $request){
        $merchant_id = $request->input('merchant_id');
        $this->notificationService->approveMerchantsSales($merchant_id);

        $this->graphMailersSender->sendapprovalMail($merchant_id, 'Your Merchant Sales Have Been Approved', 4);

        return redirect()->back()->with('success', 'Merchant sales approved successfully.');
    }

    public function approve_merchants_services(Request $request){
        $merchant_id = $request->input('merchant_id');

        $this->notificationService->approveMerchantsServices($merchant_id);
        return redirect()->back()->with('success', 'Merchant services approved successfully.');
    }




    public function decline_merchants(Request $request, $id)
    {

        $request->validate([
            'decline_notes' => 'required|string|max:500',
        ]);

        $declineNotes = $request->input('decline_notes');
        session()->put('decline_notes', $declineNotes);
        session()->put('print_decline_notes', $declineNotes);


        $merchantDetails = Merchant::with(['documents', 'sales', 'services', 'shareholders'])->findOrFail($id);


        // Step 1: Decline Merchant KYC
        $userStage = auth()->user()->getDepartmentStage(auth()->user()->department);

        if ($merchantDetails && is_null($merchantDetails->declined_by) && $userStage == 1) {

            return redirect()->route('decline.merchants.kyc', ['merchant_id' => $id]);
        }



        // Step 2: Decline Documents
        if (
            $merchantDetails &&
            !is_null($merchantDetails->approved_by) &&
            $merchantDetails->documents->isNotEmpty() &&
            $merchantDetails->documents->some(fn($doc) => is_null($doc->declined_by) &&
            $userStage == 2)
        ) {
            return redirect()->route('decline.merchants.documents', ['merchant_id' => $id]);
        }


        // Step 3: Decline Sales
        if (
            $merchantDetails &&
            !is_null($merchantDetails->approved_by) &&
            $merchantDetails->documents->isNotEmpty() &&
            $merchantDetails->documents->every(fn($doc) => !is_null($doc->approved_by)) &&
            $merchantDetails->sales->isNotEmpty() &&
            $merchantDetails->sales->some(fn($sale) => is_null($sale->declined_by) &&
            $userStage == 3)
        ) {

            return redirect()->route('decline.merchants.sales', ['merchant_id' => $id]);
        }


        // Step 4: Decline Services
        if (
            $merchantDetails &&
            !is_null($merchantDetails->approved_by) &&
            $merchantDetails->documents->isNotEmpty() &&
            $merchantDetails->documents->every(fn($doc) => !is_null($doc->approved_by)) &&
            $merchantDetails->sales->isNotEmpty() &&
            $merchantDetails->services->some(fn($service) => is_null($service->declined_by) &&
            $userStage == 4)
        ) {

            return redirect()->route('decline.merchants.services', ['merchant_id' => $id]);
        }

        return redirect()->back()->with('success', 'Merchant completed: documents, sales, and services declined successfully.');
    }



    public function declineKYC(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        $declineNotes = session()->pull('decline_notes', 'No notes provided');


        $merchant = Merchant::with(['documents', 'sales', 'services', 'shareholders'])->findOrFail($merchant_id);
        $merchant->declined_by = auth()->user()->id;
        $merchant->approved_by = null;
        $merchant->decline_notes = $declineNotes;
        $merchant->save();

        $addedByUser = User::find($merchant->added_by);


        $merchant = Merchant::with('documents')->find($merchant_id);

        if ($merchant) {
            $merchant->documents->each(function ($document) {
                $document->update(['approved_by' => null]);
            });
        }

        MerchantSale::where('merchant_id', $merchant_id)
            ->update(['approved_by' => null]);

        MerchantService::where('merchant_id', $merchant_id)
            ->update(['approved_by' => null]);
        if($addedByUser->role == 'frontendUser'){
        $this->notificationService->declineKYCFrontendUser($merchant_id, $declineNotes);
        }else{
        $this->notificationService->declineKYC($merchant_id, $declineNotes);
        }
        $this->graphMailersSender->senddeclinedMail($merchant_id, 'Your Merchant kyc Have Been Declined', 1);
        return redirect()->back()->with('success', 'KYC declined successfully.');
    }

    public function decline_merchants_documents(Request $request)
    {


        $merchant_id = $request->input('merchant_id');
        $declineNotes = session()->pull('decline_notes', 'No notes provided');
        $merchant = Merchant::with(['documents', 'sales', 'services', 'shareholders'])->findOrFail($merchant_id);
        foreach ($merchant->documents as $document) {
            $document->declined_by = auth()->user()->id;
            $document->decline_notes = $declineNotes;
            $document->approved_by = null;
            $document->save();
        }

        $addedByUser = User::find($merchant->added_by);

        MerchantSale::where('merchant_id', $merchant_id)
        ->update(['approved_by' => null]);

        MerchantService::where('merchant_id', $merchant_id)
            ->update(['approved_by' => null]);
            if($addedByUser->role == 'frontendUser'){
        $this->notificationService->declineMerchantsDocumentsFrontendUser($merchant_id, $declineNotes);
        }else{
        $this->notificationService->declineMerchantsDocuments($merchant_id, $declineNotes);
        }
        $this->graphMailersSender->senddeclinedMail($merchant_id, 'Your Merchant documents Have Been Declined', 2);
        return redirect()->back()->with('success', 'Merchant documents declined successfully.');
    }

    public function decline_merchants_sales(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        $declineNotes = session()->pull('decline_notes', 'No notes provided');
        $merchant = Merchant::with(['documents', 'sales', 'services', 'shareholders'])->findOrFail($merchant_id);
        foreach ($merchant->sales as $sale) {
            $sale->declined_by = auth()->user()->id;
            $sale->decline_notes = $declineNotes;
            $sale->approved_by = null;
            $sale->save();
        }
        MerchantService::where('merchant_id', $merchant_id)
        ->update(['approved_by' => null]);
        $this->notificationService->declineMerchantsSales($merchant_id, $declineNotes);
        $this->graphMailersSender->senddeclinedMail($merchant_id, 'Your Merchant sales Have Been Declined', 3);
        return redirect()->back()->with('success', 'Merchant sales declined successfully.');
    }

    public function decline_merchants_services(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        $declineNotes = session()->pull('decline_notes', 'No notes provided');
        $merchant = Merchant::with(['documents', 'sales', 'services', 'shareholders'])->findOrFail($merchant_id);
        foreach ($merchant->services as $service) {
            $service->declined_by = auth()->user()->id;
            $service->decline_notes = $declineNotes;
            $service->approved_by = null;
            $service->save();
        }
        $this->notificationService->declineMerchantsServices($merchant_id, $declineNotes);
        $this->graphMailersSender->senddeclinedMail($merchant_id, 'Your Merchant services Have Been Declined', 4);
        return redirect()->back()->with('success', 'Merchant services declined successfully.');
    }


}
