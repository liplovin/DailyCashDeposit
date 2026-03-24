<?php

namespace App\Http\Controllers;

use App\Models\OperatingAccount;
use App\Models\OperatingAccountDisbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperatingAccountDisbursementController extends Controller
{
    /**
     * Validate disbursements for duplicate check numbers within the same submission.
     */
    public function validate(Request $request)
    {
        try {
            $disbursements = $request->input('disbursements', []);
            $operatingAccountId = $request->input('operating_account_id');
            
            if (empty($disbursements)) {
                return response()->json(['valid' => true]);
            }

            // Check for duplicates within the current submission only (same date and same account)
            $checkNumbersByDate = [];
            foreach ($disbursements as $disbursement) {
                // Handle both array and object formats
                $checkNumber = is_array($disbursement) ? $disbursement['check_number'] ?? null : $disbursement->check_number ?? null;
                $date = is_array($disbursement) ? $disbursement['date'] ?? null : $disbursement->date ?? null;
                
                if (!$checkNumber || !$date) {
                    continue; // Skip invalid entries
                }
                
                $key = (string)$checkNumber . '_' . (string)$date;
                
                if (isset($checkNumbersByDate[$key])) {
                    return response()->json([
                        'valid' => false,
                        'message' => 'Duplicate check number "' . htmlspecialchars($checkNumber) . '" for the same date. Each check number must be unique per date.'
                    ]);
                }
                
                $checkNumbersByDate[$key] = true;
            }

            return response()->json(['valid' => true]);
        } catch (\Exception $e) {
            \Log::error('Disbursement validation error: ' . $e->getMessage());
            return response()->json([
                'valid' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Store multiple disbursements.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'disbursements' => 'required|array',
            'disbursements.*.operating_account_id' => 'required|exists:operating_accounts,id',
            'disbursements.*.check_number' => 'required|string',
            'disbursements.*.date' => 'required|date',
            'disbursements.*.amount' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['disbursements'] as $disbursement) {
                OperatingAccountDisbursement::create([
                    'operating_account_id' => $disbursement['operating_account_id'],
                    'check_number' => $disbursement['check_number'],
                    'date' => $disbursement['date'],
                    'amount' => $disbursement['amount'],
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Disbursements added successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error adding disbursements: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a disbursement.
     */
    public function update(Request $request, OperatingAccountDisbursement $operatingAccountDisbursement)
    {
        $validated = $request->validate([
            'check_number' => 'required|string',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            // Check for duplicate check numbers (excluding current record)
            $duplicate = OperatingAccountDisbursement::where('check_number', $validated['check_number'])
                ->where('id', '!=', $operatingAccountDisbursement->id)
                ->first();

            if ($duplicate) {
                return response()->json(['message' => 'Duplicate check number found'], 409);
            }

            $operatingAccountDisbursement->update($validated);

            return response()->json(['message' => 'Disbursement updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating disbursement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a disbursement.
     */
    public function destroy(OperatingAccountDisbursement $operatingAccountDisbursement)
    {
        try {
            $operatingAccountDisbursement->delete();
            return response()->json(['message' => 'Disbursement deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting disbursement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process selected disbursements (mark as processed).
     */
    public function processDisbursements(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['message' => 'No disbursements selected'], 400);
        }

        try {
            $count = OperatingAccountDisbursement::whereIn('id', $ids)
                ->where('status', 'pending')
                ->update(['status' => 'processed']);

            return response()->json([
                'message' => "{$count} disbursement(s) processed successfully",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing disbursements: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Auto-process all pending disbursements (called by scheduler).
     */
    public function autoProcessPendingDisbursements()
    {
        try {
            $count = OperatingAccountDisbursement::where('status', 'pending')
                ->update(['status' => 'processed']);

            \Log::info("Operating Account Disbursements auto-processed: {$count} records at " . now());

            return $count;
        } catch (\Exception $e) {
            \Log::error("Error auto-processing operating account disbursements: " . $e->getMessage());
            return 0;
        }
    }
}
