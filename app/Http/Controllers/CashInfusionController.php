<?php

namespace App\Http\Controllers;

use App\Models\CashInfusion;
use App\Models\CashInfusionRenewal;
use App\Models\CashInfusionWithdrawal;
use App\Models\CashInfusionBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashInfusionController extends Controller
{
    public function index()
    {
        $cashInfusions = CashInfusion::with(['renewals', 'withdrawals', 'balances'])->get();
        return Inertia::render('Treasury/Cash Infusion/Index', [
            'cashInfusions' => $cashInfusions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cash_infusion_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:cash_infusions,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
            'acquisition_date' => 'required|date_format:m/d/Y',
            'explanation' => 'required|string|max:1000',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        $validated['acquisition_date'] = $this->convertDateFormat($validated['acquisition_date']);
        CashInfusion::create($validated);

        return redirect('/treasury/cash-infusion')->with('success', 'Cash Infusion created successfully.');
    }

    public function update(Request $request, $id)
    {
        $cashInfusion = CashInfusion::findOrFail($id);

        $validated = $request->validate([
            'cash_infusion_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:cash_infusions,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
            'acquisition_date' => 'required|date_format:m/d/Y',
            'explanation' => 'required|string|max:1000',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        $validated['acquisition_date'] = $this->convertDateFormat($validated['acquisition_date']);
        $cashInfusion->update($validated);

        return redirect('/treasury/cash-infusion')->with('success', 'Cash Infusion updated successfully.');
    }

    public function destroy($id)
    {
        $cashInfusion = CashInfusion::findOrFail($id);
        $cashInfusion->delete();

        return redirect('/treasury/cash-infusion')->with('success', 'Cash Infusion deleted successfully.');
    }

    private function convertDateFormat($dateString)
    {
        $parts = explode('/', $dateString);
        if (count($parts) === 3) {
            $month = $parts[0];
            $day = $parts[1];
            $year = $parts[2];
            return $year . '-' . $month . '-' . $day;
        }
        return $dateString;
    }

    public function addCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $cashInfusion = CashInfusion::findOrFail($id);
        $cashInfusion->collection += $validated['amount'];
        $cashInfusion->collection_date = $validated['date'];
        $cashInfusion->ending_balance = $cashInfusion->beginning_balance + $cashInfusion->collection - ($cashInfusion->disbursement ?? 0);
        $cashInfusion->save();

        return response()->json([
            'message' => 'Collection added successfully',
            'cashInfusion' => $cashInfusion
        ]);
    }

    public function addDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $cashInfusion = CashInfusion::findOrFail($id);
        $cashInfusion->disbursement += $validated['amount'];
        $cashInfusion->disbursement_date = $validated['date'];
        $cashInfusion->ending_balance = $cashInfusion->beginning_balance + $cashInfusion->collection - $cashInfusion->disbursement;
        $cashInfusion->save();

        return response()->json([
            'message' => 'Disbursement added successfully',
            'cashInfusion' => $cashInfusion
        ]);
    }

    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $cashInfusion = CashInfusion::findOrFail($id);
        $cashInfusion->collection = $validated['amount'];
        $cashInfusion->ending_balance = $cashInfusion->beginning_balance + $cashInfusion->collection - $cashInfusion->disbursement;
        $cashInfusion->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'cashInfusion' => $cashInfusion
        ]);
    }

    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $cashInfusion = CashInfusion::findOrFail($id);
        $cashInfusion->disbursement = $validated['amount'];
        $cashInfusion->ending_balance = $cashInfusion->beginning_balance + $cashInfusion->collection - $cashInfusion->disbursement;
        $cashInfusion->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'cashInfusion' => $cashInfusion
        ]);
    }

    public function renew(Request $request, $id)
    {
        $validated = $request->validate([
            'new_maturity_date' => 'required|date|after:today',
            'explanation' => 'required|string|max:1000',
        ]);

        $cashInfusion = CashInfusion::findOrFail($id);
        $previousMaturityDate = $cashInfusion->maturity_date;

        // Create renewal record
        CashInfusionRenewal::create([
            'cash_infusion_id' => $cashInfusion->id,
            'previous_maturity_date' => $previousMaturityDate,
            'new_maturity_date' => $validated['new_maturity_date'],
            'explanation' => $validated['explanation'],
        ]);

        // Update maturity date
        $cashInfusion->update(['maturity_date' => $validated['new_maturity_date']]);

        return redirect()->back()->with('success', 'Cash Infusion renewed successfully.');
    }

    public function withdraw(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string|max:1000',
        ]);

        $cashInfusion = CashInfusion::findOrFail($id);
        $currentBalance = $cashInfusion->beginning_balance;

        if ($validated['amount'] > $currentBalance) {
            return redirect()->back()->withErrors(['amount' => 'Withdrawal amount exceeds current balance']);
        }

        // Create withdrawal record
        CashInfusionWithdrawal::create([
            'cash_infusion_id' => $cashInfusion->id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);

        // Update balance
        $newBalance = $currentBalance - $validated['amount'];
        $cashInfusion->update(['beginning_balance' => $newBalance]);

        // If fully withdrawn, mark as withdrawn (set maturity_date to null)
        if ($newBalance <= 0) {
            $cashInfusion->update(['maturity_date' => null]);
        }

        return redirect()->back()->with('success', 'Cash Infusion withdrawn successfully.');
    }

    public function addBalance(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string|max:1000',
        ]);

        $cashInfusion = CashInfusion::findOrFail($id);

        // Create balance addition record
        CashInfusionBalance::create([
            'cash_infusion_id' => $cashInfusion->id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);

        // Update balance
        $newBalance = $cashInfusion->beginning_balance + $validated['amount'];
        $cashInfusion->update(['beginning_balance' => $newBalance]);

        return redirect()->back()->with('success', 'Balance added successfully.');
    }
}
