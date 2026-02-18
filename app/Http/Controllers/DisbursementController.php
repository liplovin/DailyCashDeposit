<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use Illuminate\Http\Request;

class DisbursementController extends Controller
{
    public function validate(Request $request)
    {
        $validated = $request->validate([
            'collateral_id' => 'required|exists:collaterals,id',
            'disbursements' => 'required|array|min:1',
            'disbursements.*.check_number' => 'required|string|max:50',
            'disbursements.*.date' => 'required|date_format:Y-m-d',
            'disbursements.*.amount' => 'required|numeric|min:0.01',
        ]);

        // Check each disbursement for duplicate check numbers (across all dates)
        foreach ($validated['disbursements'] as $disbursement) {
            $existing = Disbursement::where('check_number', $disbursement['check_number'])
                ->first();

            if ($existing) {
                return response()->json([
                    'valid' => false,
                    'message' => "Check number {$disbursement['check_number']} already exists. Check numbers must be unique."
                ], 200);
            }
        }

        return response()->json(['valid' => true]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'collateral_id' => 'required|exists:collaterals,id',
            'disbursements' => 'required|array|min:1',
            'disbursements.*.check_number' => 'required|string|max:50',
            'disbursements.*.date' => 'required|date_format:Y-m-d',
            'disbursements.*.amount' => 'required|numeric|min:0.01',
        ]);

        foreach ($validated['disbursements'] as $disbursement) {
            Disbursement::create([
                'collateral_id' => $validated['collateral_id'],
                'check_number' => $disbursement['check_number'],
                'date' => $disbursement['date'],
                'amount' => $disbursement['amount'],
                'status' => 'pending',
            ]);
        }

        return back()->with('success', 'Disbursements saved successfully');
    }

    public function update(Request $request, $id)
    {
        $disbursement = Disbursement::findOrFail($id);

        $validated = $request->validate([
            'check_number' => 'required|string|max:50',
            'date' => 'required|date_format:Y-m-d',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'nullable|in:pending,processed',
        ]);

        // Check if the new check number already exists (but allow the same disbursement)
        $exists = Disbursement::where('check_number', $validated['check_number'])
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Check number {$validated['check_number']} already exists. Check numbers must be unique."
            ], 422);
        }

        $disbursement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Disbursement updated successfully.',
            'disbursement' => $disbursement
        ]);
    }

    public function destroy($id)
    {
        $disbursement = Disbursement::findOrFail($id);
        $disbursement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disbursement deleted successfully.'
        ]);
    }

    /**
     * Mark disbursements as processed
     */
    public function processDisbursements(Request $request)
    {
        try {
            $validated = $request->validate([
                'disbursement_ids' => 'required|array',
                'disbursement_ids.*' => 'integer|exists:disbursements,id',
            ]);

            $disbursementIds = $validated['disbursement_ids'];

            // Update all disbursements to processed status
            Disbursement::whereIn('id', $disbursementIds)->update(['status' => 'processed']);

            return response()->json([
                'success' => true,
                'message' => 'Disbursements marked as processed successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing disbursements: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Auto-process all pending disbursements (called by scheduled task at 12:00 AM)
     */
    public function autoProcessPendingDisbursements()
    {
        try {
            // Update all pending disbursements to processed status
            $processed = Disbursement::where('status', 'pending')->update(['status' => 'processed']);

            \Log::info("Auto-process disbursements: {$processed} disbursements marked as processed at " . now());

            return [
                'success' => true,
                'message' => "Auto-processed {$processed} pending disbursements at " . now(),
                'count' => $processed,
            ];

        } catch (\Exception $e) {
            \Log::error("Error auto-processing disbursements: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error auto-processing disbursements: ' . $e->getMessage(),
            ];
        }
    }
}
