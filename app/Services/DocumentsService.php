<?php
namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Merchant;
use App\Models\MerchantSale;
use App\Models\MerchantService;
use App\Models\MerchantShareholder;
use App\Models\MerchantDocument;
use Illuminate\Support\Facades\File;

class DocumentsService
{
    /**
     * Create a new document.
     *
     * @param array $data
     * @return Document
     */
    public function createDocument(array $data)
    {
        // Convert allowed_types array to a comma-separated string
        $allowedTypesString = isset($data['allowed_types']) ? implode(',', $data['allowed_types']) : 'None';

        // Create the document
        return Document::create([
            'title' => $data['documentTitle'],
            'is_required' => $data['is_required'],
            'require_expiry' => $data['require_expiry'],
            'allowed_types' => $allowedTypesString,
            'added_by' => auth()->user()->id,
            'status' => 'active',
        ]);
    }

    /**
     * Update an existing document.
     *
     * @param Document $document
     * @param array $data
     * @return bool
     */

public function updateDocument(Document $document, array $data)
{
    // Convert allowed_types array to a comma-separated string
    $allowedTypesString = isset($data['allowed_types']) ? implode(',', $data['allowed_types']) : 'None';

    // Update the document
    $document->update([
        'title' => $data['documentTitle'],
        'is_required' => $data['is_required'],
        'require_expiry' => $data['require_expiry'],
        'allowed_types' => $allowedTypesString,
        'updated_by' => auth()->user()->id, // Assuming you want to track who updated the document
    ]);

    return $document;
}

    public function updateDocuments($validatedData, $request)
    {

        $merchant_id = $request->input('merchant_id');
        $merchant = Merchant::with(['documents', 'sales', 'services', 'shareholders'])->find($merchant_id);

        // Authorization check
        if (
            auth()->user()->role === 'user' &&
            $merchant &&
            $merchant->documents->every(fn($document) => $document->approved_by !== null)
        ) {
            throw new \Exception('You are not authorized to edit these documents as they have already been approved.');
        }

        // Process each document
        foreach ($request->all() as $key => $value) {

            if (strpos($key, 'document_') === 0 && $request->hasFile($key)) {
                $this->handleNewDocument($key, $request, $merchant_id);
            }

            if (strpos($key, 'replace_document_') === 0 && $request->hasFile($key)) {
                $this->handleReplaceDocument($key, $request, $merchant_id);
            }

            if (strpos($key, 'update_replace_document_') === 0 && $request->hasFile($key)) {
                $this->handleUpdateReplacementDocument($key, $request, $merchant_id);
            }

            if (strpos($key, 'replace_expiry_') === 0) {
                $this->updateReplacementExpiryDate($key, $value);
            }
        }

        $this->updateExistingDocumentExpiryDates($request, $merchant_id);
        $this->resetApprovals($merchant);

        return true;
    }


    private function handleNewDocument($key, $request, $merchant_id)
    {
        try {
            $keyParts = explode('_', $key);
            $shareholder_name = null;

            if (count($keyParts) === 3) {
                // Format: document_{document_id}_{previous_document_id}
                $document_id = $keyParts[1];
                $previous_document_id = $keyParts[2];
                $expiryDateKey = 'expiry_' . $previous_document_id;
                $expiryDate = $request->input($expiryDateKey, null);
            } elseif (count($keyParts) >= 4) {
                // Format: document_{document_id}_{shareholder_name}_{previous_document_id}
                $document_id = $keyParts[1];
                $shareholder_name = $keyParts[2];
                $previous_document_id = $keyParts[3];
                $expiryDateKey = 'expiry_' . $previous_document_id;
                $expiryDate = $request->input($expiryDateKey, null);
            } else {
                \Log::warning('Invalid document key format', ['key' => $key]);
                return;
            }

            // Retrieve the file
            $file = $request->file($key);
            if (!$file) {
                \Log::error('No file uploaded', ['key' => $key]);
                return;
            }

            // Create the directory if it doesn't exist
            $documentsPath = public_path('storage/documents');
            if (!File::exists($documentsPath)) {
                File::makeDirectory($documentsPath, 0755, true);
            }

            // Generate file name and store the file
            $fileName = $document_id . '_' . ($shareholder_name ? $shareholder_name . '_' : '') . $file->getClientOriginalName();

            // Store file directly in public directory
            $file->move($documentsPath, $fileName);
            $filePath = 'documents/' . $fileName;

            // Check if the document exists
            $existingDocument = MerchantDocument::where('id', $previous_document_id)
                                              ->where('merchant_id', $merchant_id)
                                              ->first();

            if ($existingDocument) {
                // Delete old file if it exists
                $oldFilePath = public_path(str_replace('storage/', 'storage/documents/', $existingDocument->document));
                if (File::exists($oldFilePath)) {
                    File::delete($oldFilePath);
                }

                // Update the existing document
                $existingDocument->update([
                    'title' => $fileName,
                    'document' => 'storage/' . $filePath,
                    'date_expiry' => $expiryDate,
                    'added_by' => auth()->user()->id,
                    'document_type' => $file->getClientMimeType(),
                    'status' => 1
                ]);

                \Log::info('Document updated successfully', [
                    'document_id' => $existingDocument->id,
                    'file_path' => $filePath
                ]);
            } else {
                \Log::warning('Document not found', [
                    'previous_document_id' => $previous_document_id,
                    'merchant_id' => $merchant_id
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error handling new document: ' . $e->getMessage(), [
                'exception' => $e,
                'key' => $key,
                'merchant_id' => $merchant_id
            ]);
            throw $e;
        }
    }


    private function handleReplaceDocument($key, $request, $merchant_id)
    {
        $replaceDocumentId = str_replace('replace_document_', '', $key);
        $file = $request->file($key);

        $fileName = 'replace_' . $replaceDocumentId . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $fileName, 'public');
        File::copy(storage_path('app/public/' . $filePath), public_path('storage/' . $filePath));

        MerchantDocument::create([
            'title' => $fileName,
            'document' => 'storage/' . $filePath,
            'date_expiry' => $request->input('replace_expiry_' . $replaceDocumentId),
            'merchant_id' => $merchant_id,
            'previous_doc_id' => $replaceDocumentId,
            'added_by' => auth()->user()->id,
            'document_type' => $file->getClientMimeType(),
            'status' => 1,
        ]);

        MerchantDocument::where('id', $replaceDocumentId)->update(['status' => 0]);
    }

    private function handleUpdateReplacementDocument($key, $request, $merchant_id)
    {
        preg_match('/update_replace_document_(\d+)_of_(\d+)/', $key, $matches);

        if ($matches) {
            $replaceDocumentId = $matches[1];
            $originalDocumentId = $matches[2];
            $file = $request->file($key);

            $fileName = 'replace_' . $originalDocumentId . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'public');
            File::copy(storage_path('app/public/' . $filePath), public_path('storage/' . $filePath));

            $existingReplacement = MerchantDocument::where('id', $replaceDocumentId)->first();

            if ($existingReplacement) {
                $existingReplacement->update([
                    'title' => $fileName,
                    'document' => 'storage/' . $filePath,
                    'date_expiry' => $request->input('replace_expiry_' . $replaceDocumentId),
                    'added_by' => auth()->user()->id,
                    'document_type' => $file->getClientMimeType(),
                    'status' => 1,
                ]);
            }
        }
    }

    private function updateReplacementExpiryDate($key, $value)
    {
        preg_match('/replace_expiry_(\d+)/', $key, $matches);

        if ($matches) {
            $replaceDocumentId = $matches[1];
            $existingReplacement = MerchantDocument::where('id', $replaceDocumentId)->first();

            if ($existingReplacement) {
                $existingReplacement->update(['date_expiry' => $value]);
            }
        }
    }

    private function updateExistingDocumentExpiryDates($request, $merchant_id)
    {
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'existing_document_') === 0) {
                $existing_document_id = str_replace('existing_document_', '', $key);
                $expiryDateKey = 'expiry_' . $existing_document_id;
                $expiryDate = $request->input($expiryDateKey, null);

                MerchantDocument::where('id', $existing_document_id)
                    ->where('merchant_id', $merchant_id)
                    ->update(['date_expiry' => $expiryDate]);
            }
        }
    }

    private function resetApprovals($merchant)
    {
        if ($merchant) {
            $merchant->documents->each(function ($document) {
                $document->update(['approved_by' => null]);
            });
        }

        MerchantSale::where('merchant_id', $merchant->id)
            ->update(['approved_by' => null]);

        MerchantService::where('merchant_id', $merchant->id)
            ->update(['approved_by' => null]);
    }

    /**
     * Delete a document.
     *
     * @param Document $document
     * @return bool|null
     * @throws \Exception
     */
    public function deleteDocument(Document $document)
    {
        // You could add additional logic here, like deleting files related to the document if needed.
        return $document->delete();
    }

    /**
     * Retrieve a document by ID.
     *
     * @param int $id
     * @return Document|null
     */
    public function getDocumentById($id)
    {
        return Document::find($id);
    }
}
