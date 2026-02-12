<?php

namespace App\Http\Controllers;

use App\Models\OperatingAccount;
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
        $operatingAccounts = OperatingAccount::with('collections')->get();
        
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
                $amount = (float)$collectionData['collection_amount'];
                
                // Validate amount
                if ($amount <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Each collection amount must be greater than 0.',
                    ], 422);
                }

                $filePath = null;

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

                // Create collection record with status = pending
                $collection = Collection::create([
                    'operating_account_id' => $operatingAccount->id,
                    'collection_amount' => $amount,
                    'deposit_slip' => $filePath,
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
}
