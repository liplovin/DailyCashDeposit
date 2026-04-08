<?php

namespace App\Http\Controllers;

use App\Models\OperatingAccount;
use App\Models\OperatingAccountDisbursement;
use App\Models\DisbursementPayment;
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
            // Allow multiple DEBIT payments on same date, but prevent duplicate CHECK numbers
            $checkNumbersByDate = [];
            foreach ($disbursements as $disbursement) {
                // Handle both array and object formats
                $paymentType = is_array($disbursement) ? $disbursement['payment_type'] ?? 'CHECK' : $disbursement->payment_type ?? 'CHECK';
                $checkNumber = is_array($disbursement) ? $disbursement['check_number'] ?? null : $disbursement->check_number ?? null;
                $date = is_array($disbursement) ? $disbursement['date'] ?? null : $disbursement->date ?? null;
                
                if (!$checkNumber || !$date) {
                    continue; // Skip invalid entries
                }
                
                // Skip duplicate check validation for DEBIT payments - allow multiple DEBIT on same date
                if ($paymentType === 'DEBIT') {
                    // Still validate that DEBIT payments have required fields
                    $payments = is_array($disbursement) ? $disbursement['payments'] ?? [] : $disbursement->payments ?? [];
                    if (empty($payments)) {
                        return response()->json([
                            'valid' => false,
                            'message' => 'Each disbursement must have at least one payment entry.'
                        ]);
                    }
                    foreach ($payments as $payment) {
                        $paymentFor = is_array($payment) ? $payment['payment_for'] ?? '' : $payment->payment_for ?? '';
                        $payableTo = is_array($payment) ? $payment['payable_to'] ?? '' : $payment->payable_to ?? '';
                        if (!$paymentFor || !$payableTo) {
                            return response()->json([
                                'valid' => false,
                                'message' => 'All payment entries must have both "Payment For" and "Payable To" filled in.'
                            ]);
                        }
                    }
                    continue; // Skip to next disbursement - DEBIT doesn't need unique check number validation
                }
                
                // Only validate duplicate check numbers for CHECK type payments
                $key = (string)$checkNumber . '_' . (string)$date;
                
                if (isset($checkNumbersByDate[$key])) {
                    return response()->json([
                        'valid' => false,
                        'message' => 'Duplicate check number "' . htmlspecialchars($checkNumber) . '" for the same date. Each check number must be unique per date.'
                    ]);
                }
                
                $checkNumbersByDate[$key] = true;
                
                // Validate nested payments structure
                $payments = is_array($disbursement) ? $disbursement['payments'] ?? [] : $disbursement->payments ?? [];
                if (empty($payments)) {
                    return response()->json([
                        'valid' => false,
                        'message' => 'Each disbursement must have at least one payment entry.'
                    ]);
                }
                
                foreach ($payments as $payment) {
                    $paymentFor = is_array($payment) ? $payment['payment_for'] ?? '' : $payment->payment_for ?? '';
                    $payableTo = is_array($payment) ? $payment['payable_to'] ?? '' : $payment->payable_to ?? '';
                    
                    if (!$paymentFor || !$payableTo) {
                        return response()->json([
                            'valid' => false,
                            'message' => 'All payment entries must have both "Payment For" and "Payable To" filled in.'
                        ]);
                    }
                }
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
     * Store multiple disbursements with multiple payment entries each.
     * Creates 1 disbursement record + multiple payment records per disbursement.
     */
    public function store(Request $request)
    {
        // Validate structure with nested payments
        $this->validateDisbursements($request->input('disbursements', []));
        
        $disbursements = $request->input('disbursements', []);
        $operatingAccountId = $request->input('operating_account_id');

        if (!$operatingAccountId) {
            return response()->json(['message' => 'Operating account is required'], 400);
        }

        try {
            DB::beginTransaction();

            foreach ($disbursements as $disbursement) {
                $paymentType = $disbursement['payment_type'] ?? 'CHECK';
                $checkNumber = $disbursement['check_number'] ?? null;
                $date = $disbursement['date'] ?? null;
                $amount = $disbursement['amount'] ?? 0;
                $payments = $disbursement['payments'] ?? [];

                if (!$checkNumber || !$date || empty($payments)) {
                    continue; // Skip invalid entries
                }

                // Create ONE disbursement record
                $disbursementRecord = OperatingAccountDisbursement::create([
                    'operating_account_id' => $operatingAccountId,
                    'payment_type' => $paymentType,
                    'check_number' => $checkNumber,
                    'date' => $date,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);

                // Create multiple payment records for this disbursement
                foreach ($payments as $payment) {
                    $paymentFor = $payment['payment_for'] ?? '';
                    $payableTo = $payment['payable_to'] ?? '';

                    if (!$paymentFor || !$payableTo) {
                        continue; // Skip incomplete payment entries
                    }

                    DisbursementPayment::create([
                        'operating_account_disbursement_id' => $disbursementRecord->id,
                        'payment_for' => $paymentFor,
                        'payable_to' => $payableTo,
                        'amount' => $payment['amount'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Disbursements added successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing disbursements: ' . $e->getMessage());
            return response()->json(['message' => 'Error adding disbursements: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Validate the disbursement structure.
     */
    private function validateDisbursements($disbursements)
    {
        if (!is_array($disbursements)) {
            throw new \Exception('Disbursements must be an array');
        }

        foreach ($disbursements as $index => $disbursement) {
            if (!isset($disbursement['check_number'])) {
                throw new \Exception("Disbursement $index: Check number is required");
            }
            if (!isset($disbursement['date'])) {
                throw new \Exception("Disbursement $index: Date is required");
            }
            if (!isset($disbursement['amount'])) {
                throw new \Exception("Disbursement $index: Amount is required");
            }
            if (!isset($disbursement['payments']) || !is_array($disbursement['payments'])) {
                throw new \Exception("Disbursement $index: Payments array is required");
            }
            if (empty($disbursement['payments'])) {
                throw new \Exception("Disbursement $index: At least one payment entry is required");
            }

            foreach ($disbursement['payments'] as $pIndex => $payment) {
                if (empty($payment['payment_for'])) {
                    throw new \Exception("Disbursement $index, Payment $pIndex: 'Payment For' is required");
                }
                if (empty($payment['payable_to'])) {
                    throw new \Exception("Disbursement $index, Payment $pIndex: 'Payable To' is required");
                }
            }
        }
    }

    /**
     * Update a disbursement and its payment entries.
     */
    public function update(Request $request, OperatingAccountDisbursement $operatingAccountDisbursement)
    {
        $validated = $request->validate([
            'check_number' => 'required|string',
            'payment_type' => 'required|in:DEBIT,CHECK',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payments' => 'required|array',
            'payments.*.payment_for' => 'required|string|max:255',
            'payments.*.payable_to' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Only check for duplicate check numbers if payment_type is CHECK
            if ($validated['payment_type'] === 'CHECK') {
                $duplicate = OperatingAccountDisbursement::where('check_number', $validated['check_number'])
                    ->where('id', '!=', $operatingAccountDisbursement->id)
                    ->where('date', $validated['date'])
                    ->where('payment_type', 'CHECK')
                    ->first();

                if ($duplicate) {
                    return response()->json(['message' => 'Duplicate check number found for the same date'], 409);
                }
            }

            // Update disbursement record
            $operatingAccountDisbursement->update([
                'payment_type' => $validated['payment_type'],
                'check_number' => $validated['check_number'],
                'date' => $validated['date'],
                'amount' => $validated['amount'],
            ]);

            // Delete existing payments
            $operatingAccountDisbursement->payments()->delete();

            // Create new payment records
            foreach ($validated['payments'] as $payment) {
                DisbursementPayment::create([
                    'operating_account_disbursement_id' => $operatingAccountDisbursement->id,
                    'payment_for' => $payment['payment_for'],
                    'payable_to' => $payment['payable_to'],
                    'amount' => $payment['amount'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Disbursement updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
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
