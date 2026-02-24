<?php

namespace App\Http\Controllers;

use App\Models\OtherInvestment;
use App\Models\OtherInvestmentRenewal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OtherInvestmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $otherInvestments = OtherInvestment::with('renewals', 'withdrawals', 'balances')->get();
        return Inertia::render('Treasury/Other Investment/Index', [
            'otherInvestments' => $otherInvestments,
        ]);
    }

    /**
     * Add collection to other investment
     */
    public function addCollection(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $otherInvestment = OtherInvestment::findOrFail($id);

            // Add to collection fields (not replace)
            $otherInvestment->collection += $validated['amount'];
            $otherInvestment->collection_date = $validated['date'];

            // Calculate ending balance
            $otherInvestment->ending_balance = $otherInvestment->beginning_balance + $otherInvestment->collection - $otherInvestment->disbursement;

            $otherInvestment->save();

            return response()->json([
                'message' => 'Collection added successfully',
                'otherInvestment' => $otherInvestment
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Add disbursement to other investment
     */
    public function addDisbursement(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $otherInvestment = OtherInvestment::findOrFail($id);

            // Add to disbursement fields (not replace)
            $otherInvestment->disbursement += $validated['amount'];
            $otherInvestment->disbursement_date = $validated['date'];

            // Calculate ending balance
            $otherInvestment->ending_balance = $otherInvestment->beginning_balance + $otherInvestment->collection - $otherInvestment->disbursement;

            $otherInvestment->save();

            return response()->json([
                'message' => 'Disbursement added successfully',
                'otherInvestment' => $otherInvestment
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'other_investment_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:other_investments,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
            'acquisition_date' => 'required|date_format:m/d/Y',
            'explanation' => 'required|string|max:1000',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        $validated['acquisition_date'] = $this->convertDateFormat($validated['acquisition_date']);

        OtherInvestment::create($validated);

        return redirect('/treasury/other-investment')->with('success', 'Other Investment created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $otherInvestment = OtherInvestment::findOrFail($id);

        $validated = $request->validate([
            'other_investment_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:other_investments,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
            'acquisition_date' => 'required|date_format:m/d/Y',
            'explanation' => 'required|string|max:1000',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        $validated['acquisition_date'] = $this->convertDateFormat($validated['acquisition_date']);

        $otherInvestment->update($validated);

        return redirect('/treasury/other-investment')->with('success', 'Other Investment updated successfully.');
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
        $otherInvestment = OtherInvestment::findOrFail($id);
        $otherInvestment->delete();

        return redirect('/treasury/other-investment')->with('success', 'Other Investment deleted successfully.');
    }

    /**
     * Renew an other investment
     */
    public function renew(Request $request, $id)
    {
        $otherInvestment = OtherInvestment::findOrFail($id);

        $validated = $request->validate([
            'new_maturity_date' => 'required|date|after:today',
            'explanation' => 'required|string|max:1000',
        ]);

        // Create renewal record with previous maturity date
        OtherInvestmentRenewal::create([
            'other_investment_id' => $id,
            'previous_maturity_date' => $otherInvestment->maturity_date,
            'new_maturity_date' => $validated['new_maturity_date'],
            'explanation' => $validated['explanation'],
        ]);

        // Update maturity date
        $otherInvestment->update([
            'maturity_date' => $validated['new_maturity_date'],
        ]);

        return redirect('/treasury/other-investment')->with('success', 'Other Investment renewed successfully.');
    }

    /**
     * Withdraw an other investment
     */
    public function withdraw(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|gt:0',
                'explanation' => 'required|string|min:3'
            ]);

            $otherInvestment = OtherInvestment::findOrFail($id);

            $currentBalance = $otherInvestment->beginning_balance;
            $withdrawAmount = (float)$validated['amount'];
            $isFullWithdrawal = abs($withdrawAmount - $currentBalance) < 0.01; // Account for floating point precision

            // Create withdrawal record
            $otherInvestment->withdrawals()->create([
                'amount' => $validated['amount'],
                'explanation' => $validated['explanation']
            ]);

            // Update beginning balance
            $otherInvestment->beginning_balance -= $validated['amount'];

            // Only set maturity date to null if withdrawing all
            if ($isFullWithdrawal) {
                $otherInvestment->maturity_date = null;
            }

            $otherInvestment->save();

            return redirect()->back()->with('success', 'Investment withdrawn successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Add balance to an other investment
     */
    public function addBalance(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|gt:0',
                'reason' => 'required|string|min:3'
            ]);

            $otherInvestment = OtherInvestment::findOrFail($id);

            // Create balance record
            $otherInvestment->balances()->create([
                'amount' => $validated['amount'],
                'explanation' => $validated['reason']
            ]);

            // Update beginning balance
            $otherInvestment->update([
                'beginning_balance' => $otherInvestment->beginning_balance + $validated['amount']
            ]);

            return redirect()->back()->with('success', 'Balance added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Update collection amount
     */
    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $otherInvestment = OtherInvestment::findOrFail($id);
        $otherInvestment->collection = $validated['amount'];
        $otherInvestment->ending_balance = $otherInvestment->beginning_balance + $otherInvestment->collection - $otherInvestment->disbursement;
        $otherInvestment->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'otherInvestment' => $otherInvestment
        ]);
    }

    /**
     * Update disbursement amount
     */
    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $otherInvestment = OtherInvestment::findOrFail($id);
        $otherInvestment->disbursement = $validated['amount'];
        $otherInvestment->ending_balance = $otherInvestment->beginning_balance + $otherInvestment->collection - $otherInvestment->disbursement;
        $otherInvestment->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'otherInvestment' => $otherInvestment
        ]);
    }
}
