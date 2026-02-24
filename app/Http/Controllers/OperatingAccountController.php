<?php

namespace App\Http\Controllers;

use App\Models\OperatingAccount;
use App\Models\OperatingAccountRenewal;
use App\Models\OperatingAccountWithdrawal;
use App\Models\OperatingAccountBalance;
use App\Models\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperatingAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $operatingAccounts = OperatingAccount::with(['collections', 'renewals', 'withdrawals', 'balances'])->get();
        
        // Render based on user role
        $component = match($user->role) {
            'treasury3' => 'Treasury3/OperatingAccount/Index',
            default => 'Treasury/Operating Accounts/Index'
        };
        
        return Inertia::render($component, [
            'operatingAccounts' => $operatingAccounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'operating_account_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:operating_accounts,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yyyy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        OperatingAccount::create($validated);

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $operatingAccount = OperatingAccount::findOrFail($id);

        $validated = $request->validate([
            'operating_account_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:operating_accounts,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yyyy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        $operatingAccount->update($validated);

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account updated successfully.');
    }

    private function convertDateFormat($dateString)
    {
        // Convert mm/dd/yyyy to Y-m-d
        $parts = explode('/', $dateString);
        if (count($parts) === 3) {
            $month = $parts[0];
            $day = $parts[1];
            $year = $parts[2];

            return $year . '-' . $month . '-' . $day;
        }
        return $dateString;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $operatingAccount = OperatingAccount::findOrFail($id);
        $operatingAccount->delete();

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account deleted successfully.');
    }

    /**
     * Renew the operating account maturity date.
     */
    public function renew(Request $request, $id)
    {
        $operatingAccount = OperatingAccount::findOrFail($id);

        $validated = $request->validate([
            'new_maturity_date' => 'required|date|after:today',
            'explanation' => 'required|string|max:1000',
        ]);

        // Create renewal record
        OperatingAccountRenewal::create([
            'operating_account_id' => $id,
            'previous_maturity_date' => $operatingAccount->maturity_date,
            'new_maturity_date' => $validated['new_maturity_date'],
            'explanation' => $validated['explanation'],
        ]);

        // Update the maturity date
        $operatingAccount->update([
            'maturity_date' => $validated['new_maturity_date'],
        ]);

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account renewed successfully.');
    }

    /**
     * Withdraw the operating account.
     */
    public function withdraw(Request $request, $id)
    {
        $operatingAccount = OperatingAccount::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string|max:1000',
        ]);

        // Create withdrawal record
        OperatingAccountWithdrawal::create([
            'operating_account_id' => $id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);

        // Update the beginning balance
        $currentBalance = (float) $operatingAccount->beginning_balance;
        $newBalance = max(0, $currentBalance - (float) $validated['amount']);
        
        $operatingAccount->update([
            'beginning_balance' => $newBalance,
        ]);

        // If fully withdrawn, set maturity date to null
        if ($newBalance <= 0) {
            $operatingAccount->update([
                'maturity_date' => null,
            ]);
        }

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account withdrawal recorded successfully.');
    }

    /**
     * Add balance to the operating account.
     */
    public function addBalance(Request $request, $id)
    {
        $operatingAccount = OperatingAccount::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string|max:1000',
        ]);

        // Create balance addition record
        OperatingAccountBalance::create([
            'operating_account_id' => $id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);

        // Update the beginning balance
        $currentBalance = (float) $operatingAccount->beginning_balance;
        $newBalance = $currentBalance + (float) $validated['amount'];
        
        $operatingAccount->update([
            'beginning_balance' => $newBalance,
        ]);

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account balance added successfully.');
    }

    /**
     * Store multiple collections for an operating account.
     */
    public function storeCollections(Request $request)
    {
        try {
            // Get raw input first to debug
            $operatingAccountId = $request->input('operating_account_id');
            
            // Validate the operating account exists
            if (!$operatingAccountId || !OperatingAccount::where('id', $operatingAccountId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operating account not found.',
                ], 422);
            }

            $operatingAccount = OperatingAccount::findOrFail($operatingAccountId);
            $uploadedCollections = [];
            
            // Get all collections from FormData
            $collectionsData = [];
            $index = 0;
            while ($request->has("collections.$index.collection_amount")) {
                $collectionsData[] = [
                    'collection_amount' => $request->input("collections.$index.collection_amount"),
                    'deposit_slip' => $request->file("collections.$index.deposit_slip"),
                    'check' => $request->file("collections.$index.check"),
                ];
                $index++;
            }

            if (empty($collectionsData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No collections provided.',
                ], 422);
            }

            // Process each collection
            foreach ($collectionsData as $collectionData) {
                // Remove commas from the amount before converting to float
                $amountString = str_replace(',', '', $collectionData['collection_amount']);
                $amount = (float)$amountString;
                
                // Validate amount
                if ($amount <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Each collection amount must be greater than 0.',
                    ], 422);
                }

                $filePath = null;
                $checkPath = null;

                // Store the deposit slip file if present
                if ($collectionData['deposit_slip']) {
                    $file = $collectionData['deposit_slip'];
                    
                    // Validate file
                    if (!$file->isValid()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'One of the uploaded files is invalid.',
                        ], 422);
                    }

                    // Store file
                    $filePath = $file->store('deposit-slips/' . $operatingAccount->id, 'public');
                }

                // Store the check file if present (optional)
                if ($collectionData['check']) {
                    $file = $collectionData['check'];
                    
                    // Validate file
                    if (!$file->isValid()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'One of the uploaded check files is invalid.',
                        ], 422);
                    }

                    // Store file
                    $checkPath = $file->store('checks/' . $operatingAccount->id, 'public');
                }

                // Create collection record with status = pending
                $collection = Collection::create([
                    'operating_account_id' => $operatingAccount->id,
                    'collection_amount' => $amount,
                    'deposit_slip' => $filePath,
                    'check' => $checkPath,
                    'status' => 'pending',
                ]);

                $uploadedCollections[] = $collection;
            }

            return response()->json([
                'success' => true,
                'message' => 'Collections saved successfully.',
                'collections' => $uploadedCollections,
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving collections: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark collections as processed
     */
    public function processCollections(Request $request)
    {
        try {
            $validated = $request->validate([
                'collection_ids' => 'required|array',
                'collection_ids.*' => 'integer|exists:collections,id',
            ]);

            $collectionIds = $validated['collection_ids'];

            // Update all collections to processed status
            Collection::whereIn('id', $collectionIds)->update(['status' => 'processed']);

            return response()->json([
                'success' => true,
                'message' => 'Collections marked as processed successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing collections: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Auto-process all pending collections (called by scheduled task at 12:00 AM)
     */
    public function autoProcessPendingCollections()
    {
        try {
            // Update all pending collections to processed status
            $processed = Collection::where('status', 'pending')->update(['status' => 'processed']);

            \Log::info("Auto-process collections: {$processed} collections marked as processed at " . now());

            return [
                'success' => true,
                'message' => "Auto-processed {$processed} pending collections at " . now(),
                'count' => $processed,
            ];

        } catch (\Exception $e) {
            \Log::error("Error auto-processing collections: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error auto-processing collections: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a collection
     */
    public function deleteCollection($id)
    {
        try {
            $collection = Collection::findOrFail($id);
            
            // Delete the file if it exists
            if ($collection->deposit_slip) {
                $filePath = storage_path('app/public/' . $collection->deposit_slip);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Delete the collection record
            $collection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Collection deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting collection: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a collection
     */
    public function updateCollection(Request $request, $id)
    {
        try {
            $collection = Collection::findOrFail($id);

            // Only allow updating pending collections
            if ($collection->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending collections can be edited.',
                ], 422);
            }

            $amount = $request->input('collection_amount');
            // Remove commas from the amount before converting to float
            $amountString = str_replace(',', '', $amount);
            $amount = (float)$amountString;

            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Collection amount must be greater than 0.',
                ], 422);
            }

            $collection->collection_amount = $amount;

            // Update deposit slip file if provided
            if ($request->hasFile('deposit_slip')) {
                $file = $request->file('deposit_slip');

                if (!$file->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The uploaded file is invalid.',
                    ], 422);
                }

                // Delete old file if exists
                if ($collection->deposit_slip) {
                    $oldFilePath = storage_path('app/public/' . $collection->deposit_slip);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Store new file
                $filePath = $file->store('deposit-slips/' . $collection->operating_account_id, 'public');
                $collection->deposit_slip = $filePath;
            }

            // Update check file if provided
            if ($request->hasFile('check')) {
                $file = $request->file('check');

                if (!$file->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The uploaded check file is invalid.',
                    ], 422);
                }

                // Delete old file if exists
                if ($collection->check) {
                    $oldFilePath = storage_path('app/public/' . $collection->check);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Store new file
                $filePath = $file->store('checks/' . $collection->operating_account_id, 'public');
                $collection->check = $filePath;
            }

            $collection->save();

            return response()->json([
                'success' => true,
                'message' => 'Collection updated successfully.',
                'collection' => $collection,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating collection: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add collection for Treasury3 daily tracking
     */
    public function addCollection(Request $request, $id)
    {
        try {
            $operatingAccount = OperatingAccount::findOrFail($id);
            
            $validated = $request->validate([
                'collections' => 'required|array',
                'collections.*.collection_amount' => 'required|numeric|min:0',
                'collections.*.deposit_slip' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:5120',
                'collections.*.check' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:5120',
            ]);

            $collections = [];
            $totalAmount = 0;

            foreach ($validated['collections'] as $index => $collectionData) {
                $amount = (float) str_replace(',', '', $collectionData['collection_amount']);
                $totalAmount += $amount;

                $collection = new Collection([
                    'operating_account_id' => $id,
                    'collection_date' => now(),
                    'collection_amount' => $amount,
                    'status' => 'pending',
                ]);

                // Store deposit slip if provided
                if ($request->hasFile("collections.$index.deposit_slip")) {
                    $file = $request->file("collections.$index.deposit_slip");
                    if ($file->isValid()) {
                        $filePath = $file->store('deposit-slips/' . $id, 'public');
                        $collection->deposit_slip = $filePath;
                    }
                }

                // Store check if provided
                if ($request->hasFile("collections.$index.check")) {
                    $file = $request->file("collections.$index.check");
                    if ($file->isValid()) {
                        $filePath = $file->store('checks/' . $id, 'public');
                        $collection->check = $filePath;
                    }
                }

                $collection->save();
                $collections[] = $collection;
            }

            return response()->json([
                'success' => true,
                'message' => 'Collections added successfully.',
                'collections' => $collections,
                'total_amount' => $totalAmount,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding collections: ' . $e->getMessage(),
            ], 500);
        }
    }
}
