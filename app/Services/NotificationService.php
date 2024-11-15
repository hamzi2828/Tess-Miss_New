<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\MerchantDocument;
use App\Models\MerchantSale;
use App\Models\MerchantService;
use App\Models\User;
use App\Notifications\MerchantActivityNotification;

class NotificationService
{

    public function storeMerchantsKYC($merchant)
    {
        // Retrieve the user who added the merchant
        $addedByUser = User::find($merchant->added_by);
        $addedByUserName = $addedByUser ? $addedByUser->name : auth()->user()->name;
        $notificationMessage = "A new KYC has been saved";
        $role = 'supervisor';
        $stage = 1;
        $this->approveEntity($merchant, 'store', $stage, $notificationMessage, $role, $addedByUserName);
    }

    public function storeMerchantsDocuments($merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $addedByUser = User::find($merchant->added_by);
        $addedByUserName = $addedByUser ? $addedByUser->name : auth()->user()->name;
        $notificationMessage = 'New documents have been uploaded.';
        $role = 'supervisor';
        $stage = 2;
        $this->approveEntity($merchant, 'store', $stage, $notificationMessage, $role, $addedByUserName);
    }

    public function storeMerchantsSales($merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $addedByUser = User::find($merchant->added_by);
        $addedByUserName = $addedByUser ? $addedByUser->name : auth()->user()->name;
        $notificationMessage = 'New sales have been saved.';
        $role = 'supervisor';
        $stage = 3;

        $this->approveEntity($merchant, 'store', $stage, $notificationMessage, $role, $addedByUserName);
    }

    public function storeMerchantsServices($merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $addedByUser = User::find($merchant->added_by);
        $addedByUserName = $addedByUser ? $addedByUser->name : auth()->user()->name;
        $notificationMessage = 'New services have been saved.';
        $role = 'supervisor';
        $stage = 4;
        $this->approveEntity($merchant, 'store', $stage, $notificationMessage, $role, $addedByUserName);
    }





    // Approve KYC

    public function approveKYC($merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $merchant->approved_by = auth()->user()->id;
        $merchant->save();
        $activityType = 'approve';
        $notificationMessage = "A new KYC has been approved";
        $role = 'user';
        $stage = 2;
        $approvedByUserName = auth()->user()->name;
        $this->approveEntity($merchant, $activityType, $stage, $notificationMessage, $role, $approvedByUserName);
    }



    // Approve Documents
    public function approveMerchantsDocuments($merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $documents = MerchantDocument::where('merchant_id', $merchantId)->get();

        // Loop through each document to update the approval
        foreach ($documents as $document) {
            $document->approved_by = auth()->user()->id;
            $document->save();
        }
        $activityType = 'approve';
        $notificationMessage = "Merchant documents have been approved";
        $role = 'user';
        $stage = 3;
        $approvedByUserName = auth()->user()->name;

            $this->approveEntity($merchant, $activityType, $stage, $notificationMessage, $role, $approvedByUserName);

    }


    // Approve Sales
    public function approveMerchantsSales($merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $sales = MerchantSale::where('merchant_id', $merchantId)->get();

        foreach ($sales as $sale) {
            $sale->approved_by = auth()->user()->id;
            $sale->save();
        }
        $activityType = 'approve';
        $notificationMessage = "Merchant Sales have been approved";
        $role = 'user';
        $stage = 4;
        $approvedByUserName = auth()->user()->name;
        $this->approveEntity($merchant, $activityType, $stage, $notificationMessage, $role, $approvedByUserName);
    }

    // Approve Services
    public function approveMerchantsServices($merchantId)
    {
        $services = MerchantService::where('merchant_id', $merchantId)->get();
        foreach ($services as $service) {
            $service->approved_by = auth()->user()->id;
            $service->save();
        }
        $activityType = 'approve';
        $notificationMessage = "Merchant services have been approved, completing the process";
        $role = 'user';
        $stage = 4;
        $approvedByUserName = auth()->user()->name;
        $this->approveEntity($services, $activityType, $stage, $notificationMessage, $role, $approvedByUserName);

    }

    // Common Approval Logic
    private function approveEntity($entity, $type, $stage, $notificationMessage, $role, $UserName = null)
    {

        $user = User::where('role', $role)
            ->whereHas('department', function ($query) use ($stage) {
                $query->where('stage', $stage);
            })->get();


        foreach ($user as $user) {

            $user->notify(new MerchantActivityNotification($type, $entity, $UserName, $notificationMessage));
        }
    }


}
