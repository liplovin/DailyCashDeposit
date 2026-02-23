<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentRenewal;
use App\Models\InvestmentWithdrawal;
use App\Models\InvestmentBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentController extends Controller
{
    public function index()
    {
        $investments = Investment::with(['renewals', 'withdrawals', 'balances'])->get();
        return Inertia::render('Treasury/Investment/Index', [
            'investments' => $investments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'investment_name' => 'required|string|max:255',
            'reference_number' => 'required|string',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        Investment::create($validated);

        return redirect('/treasury/investment')->with('success', 'Investment created successfully.');
    }

    public function update(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $validated = $request->validate([
            'investment_name' => 'required|string|max:255',
            'reference_number' => 'required|string',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        $investment->update($validated);

        return redirect('/treasury/investment')->with('success', 'Investment updated successfully.');
    }

    public function destroy($id)
    {
        $investment = Investment::findOrFail($id);
        $investment->delete();

        return redirect('/treasury/investment')->with('success', 'Investment deleted successfully.');
    }

    private function convertDateFormat($dateString)
    {
        $parts = explode('/', $dateString);
        return $parts[2] . '-' . $parts[0] . '-' . $parts[1];
    }

    public function addCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->collection += $validated['amount'];
        $investment->collection_date = $validated['date'];
        $investment->ending_balance = $investment->beginning_balance + $investment->collection - ($investment->disbursement ?? 0);
        $investment->save();

        return response()->json([
            'message' => 'Collection added successfully',
            'investment' => $investment
        ]);
    }

    public function addDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->disbursement += $validated['amount'];
        $investment->disbursement_date = $validated['date'];
        $investment->ending_balance = $investment->beginning_balance + $investment->collection - $investment->disbursement;
        $investment->save();

        return response()->json([
            'message' => 'Disbursement added successfully',
            'investment' => $investment
        ]);
    }

    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->collection = $validated['amount'];
        $investment->ending_balance = $investment->beginning_balance + $investment->collection - $investment->disbursement;
        $investment->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'investment' => $investment
        ]);
    }

    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->disbursement = $validated['amount'];
        $investment->ending_balance = $investment->beginning_balance + $investment->collection - $investment->disbursement;
        $investment->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'investment' => $investment
        ]);
    }

    public function renew(Request $request, $id)
    {
        $validated = $request->validate([
            'new_maturity_date' => 'required|date|after:today',
            'explanation' => 'required|string|max:1000',
        ]);

        $investment = Investment::findOrFail($id);
        $previousMaturityDate = $investment->maturity_date;

        // Create renewal record
        InvestmentRenewal::create([
            'investment_id' => $investment->id,
            'previous_maturity_date' => $previousMaturityDate,
            'new_maturity_date' => $validated['new_maturity_date'],
            'explanation' => $validated['explanation'],
        ]);

        // Update maturity date
        $investment->update(['maturity_date' => $validated['new_maturity_date']]);

        return redirect()->back()->with('success', 'Investment renewed successfully.');
    }

    public function withdraw(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string|max:1000',
        ]);

        $investment = Investment::findOrFail($id);
        $currentBalance = $investment->beginning_balance;

        if ($validated['amount'] > $currentBalance) {
            return redirect()->back()->withErrors(['amount' => 'Withdrawal amount exceeds current balance']);
        }

        // Create withdrawal record
        InvestmentWithdrawal::create([
            'investment_id' => $investment->id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);

        // Update balance
        $newBalance = $currentBalance - $validated['amount'];
        $investment->update(['beginning_balance' => $newBalance]);

        // If fully withdrawn, mark as withdrawn (set maturity_date to null)
        if ($newBalance <= 0) {
            $investment->update(['maturity_date' => null]);
        }

        return redirect()->back()->with('success', 'Investment withdrawn successfully.');
    }

    public function addBalance(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string|max:1000',
        ]);

        $investment = Investment::findOrFail($id);

        // Create balance addition record
        InvestmentBalance::create([
            'investment_id' => $investment->id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);

        // Update balance
        $newBalance = $investment->beginning_balance + $validated['amount'];
        $investment->update(['beginning_balance' => $newBalance]);

        return redirect()->back()->with('success', 'Balance added successfully.');
    }
}
