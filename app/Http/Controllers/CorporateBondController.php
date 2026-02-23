<?php

namespace App\Http\Controllers;

use App\Models\CorporateBond;
use App\Models\CorporateBondRenewal;
use App\Models\CorporateBondWithdrawal;
use App\Models\CorporateBondBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CorporateBondController extends Controller
{
    public function index()
    {
        $corporateBonds = CorporateBond::with('renewals', 'withdrawals', 'balances')->get();
        return Inertia::render('Treasury/Corporate Bonds/Index', [
            'corporateBonds' => $corporateBonds,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'corporate_bond_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:corporate_bonds,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        CorporateBond::create($validated);

        return redirect('/treasury/corporate-bonds')->with('success', 'Corporate Bond created successfully.');
    }

    public function update(Request $request, $id)
    {
        $corporateBond = CorporateBond::findOrFail($id);

        $validated = $request->validate([
            'corporate_bond_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:corporate_bonds,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        $corporateBond->update($validated);

        return redirect('/treasury/corporate-bonds')->with('success', 'Corporate Bond updated successfully.');
    }

    public function destroy($id)
    {
        $corporateBond = CorporateBond::findOrFail($id);
        $corporateBond->delete();

        return redirect('/treasury/corporate-bonds')->with('success', 'Corporate Bond deleted successfully.');
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

        $corporateBond = CorporateBond::findOrFail($id);
        $corporateBond->collection += $validated['amount'];
        $corporateBond->collection_date = $validated['date'];
        $corporateBond->ending_balance = $corporateBond->beginning_balance + $corporateBond->collection - ($corporateBond->disbursement ?? 0);
        $corporateBond->save();

        return response()->json([
            'message' => 'Collection added successfully',
            'corporateBond' => $corporateBond
        ]);
    }

    public function addDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $corporateBond = CorporateBond::findOrFail($id);
        $corporateBond->disbursement += $validated['amount'];
        $corporateBond->disbursement_date = $validated['date'];
        $corporateBond->ending_balance = $corporateBond->beginning_balance + $corporateBond->collection - $corporateBond->disbursement;
        $corporateBond->save();

        return response()->json([
            'message' => 'Disbursement added successfully',
            'corporateBond' => $corporateBond
        ]);
    }

    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $corporateBond = CorporateBond::findOrFail($id);
        $corporateBond->collection = $validated['amount'];
        $corporateBond->ending_balance = $corporateBond->beginning_balance + $corporateBond->collection - $corporateBond->disbursement;
        $corporateBond->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'corporateBond' => $corporateBond
        ]);
    }

    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $corporateBond = CorporateBond::findOrFail($id);
        $corporateBond->disbursement = $validated['amount'];
        $corporateBond->ending_balance = $corporateBond->beginning_balance + $corporateBond->collection - $corporateBond->disbursement;
        $corporateBond->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'corporateBond' => $corporateBond
        ]);
    }

    public function renew(Request $request, $id)
    {
        $validated = $request->validate([
            'new_maturity_date' => 'required|date',
            'explanation' => 'required|string|max:1000',
        ]);

        $corporateBond = CorporateBond::findOrFail($id);
        $previousMaturityDate = $corporateBond->maturity_date;

        // Create renewal record
        CorporateBondRenewal::create([
            'corporate_bond_id' => $corporateBond->id,
            'previous_maturity_date' => $previousMaturityDate,
            'new_maturity_date' => $validated['new_maturity_date'],
            'explanation' => $validated['explanation'],
        ]);

        // Update maturity date
        $corporateBond->update(['maturity_date' => $validated['new_maturity_date']]);

        return redirect()->back()->with('success', 'Corporate bond renewed successfully!');
    }

    public function withdraw(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string|max:1000',
        ]);

        $corporateBond = CorporateBond::findOrFail($id);
        $currentBalance = $corporateBond->beginning_balance;

        if ($validated['amount'] > $currentBalance) {
            return redirect()->back()->withErrors(['amount' => 'Withdrawal amount exceeds current balance']);
        }

        // Create withdrawal record
        CorporateBondWithdrawal::create([
            'corporate_bond_id' => $corporateBond->id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);

        // Update balance
        $newBalance = $currentBalance - $validated['amount'];
        $corporateBond->update(['beginning_balance' => $newBalance]);

        // If fully withdrawn, mark as withdrawn (set maturity_date to null)
        if ($newBalance <= 0) {
            $corporateBond->update(['maturity_date' => null]);
        }

        return redirect()->back()->with('success', 'Corporate bond withdrawn successfully!');
    }

    public function addBalance(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string|max:1000',
        ]);

        $corporateBond = CorporateBond::findOrFail($id);

        // Create balance addition record
        CorporateBondBalance::create([
            'corporate_bond_id' => $corporateBond->id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);

        // Update balance
        $newBalance = $corporateBond->beginning_balance + $validated['amount'];
        $corporateBond->update(['beginning_balance' => $newBalance]);

        return redirect()->back()->with('success', 'Balance added successfully!');
    }
}
