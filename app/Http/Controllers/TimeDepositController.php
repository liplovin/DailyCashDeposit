<?php

namespace App\Http\Controllers;

use App\Models\TimeDeposit;
use App\Models\TimeDepositRenewal;
use App\Models\TimeDepositWithdrawal;
use App\Models\TimeDepositBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeDepositController extends Controller
{
    public function index()
    {
        $timeDeposits = TimeDeposit::with('renewals', 'withdrawals', 'balances')->get();
        return Inertia::render('Treasury/Time Deposit/Index', [
            'timeDeposits' => $timeDeposits
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'time_deposit_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:time_deposits,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        TimeDeposit::create($validated);

        return redirect()->route('treasury.timedeposit')->with('success', 'Time Deposit created successfully');
    }

    public function destroy($id)
    {
        $timeDeposit = TimeDeposit::findOrFail($id);
        $timeDeposit->delete();

        return redirect()->route('treasury.timedeposit')->with('success', 'Time Deposit deleted successfully');
    }

    public function update(Request $request, $id)
    {
        $timeDeposit = TimeDeposit::findOrFail($id);

        $validated = $request->validate([
            'time_deposit_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:time_deposits,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        $timeDeposit->update($validated);

        return redirect()->route('treasury.timedeposit')->with('success', 'Time Deposit updated successfully');
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

    public function addCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $timeDeposit = TimeDeposit::findOrFail($id);
        $timeDeposit->collection += $validated['amount'];
        $timeDeposit->collection_date = $validated['date'];
        $timeDeposit->ending_balance = $timeDeposit->beginning_balance + $timeDeposit->collection - $timeDeposit->disbursement;
        $timeDeposit->save();

        return response()->json([
            'message' => 'Collection added successfully',
            'timeDeposit' => $timeDeposit
        ]);
    }

    public function addDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $timeDeposit = TimeDeposit::findOrFail($id);
        $timeDeposit->disbursement += $validated['amount'];
        $timeDeposit->disbursement_date = $validated['date'];
        $timeDeposit->ending_balance = $timeDeposit->beginning_balance + $timeDeposit->collection - $timeDeposit->disbursement;
        $timeDeposit->save();

        return response()->json([
            'message' => 'Disbursement added successfully',
            'timeDeposit' => $timeDeposit
        ]);
    }

    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $timeDeposit = TimeDeposit::findOrFail($id);
        $timeDeposit->collection = $validated['amount'];
        $timeDeposit->collection_date = $validated['date'];
        $timeDeposit->ending_balance = $timeDeposit->beginning_balance + $timeDeposit->collection - $timeDeposit->disbursement;
        $timeDeposit->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'timeDeposit' => $timeDeposit
        ]);
    }

    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $timeDeposit = TimeDeposit::findOrFail($id);
        $timeDeposit->disbursement = $validated['amount'];
        $timeDeposit->disbursement_date = $validated['date'];
        $timeDeposit->ending_balance = $timeDeposit->beginning_balance + $timeDeposit->collection - $timeDeposit->disbursement;
        $timeDeposit->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'timeDeposit' => $timeDeposit
        ]);
    }

    public function renew(Request $request, $id)
    {
        $validated = $request->validate([
            'maturity_date' => 'required|date|after:today',
            'explanation' => 'required|string',
        ], [
            'maturity_date.after' => 'New maturity date must be in the future',
        ]);

        $timeDeposit = TimeDeposit::findOrFail($id);
        
        $previousMaturityDate = $timeDeposit->maturity_date;
        
        // Create renewal record
        TimeDepositRenewal::create([
            'time_deposit_id' => $timeDeposit->id,
            'previous_maturity_date' => $previousMaturityDate,
            'new_maturity_date' => $validated['maturity_date'],
            'explanation' => $validated['explanation'],
        ]);
        
        // Update maturity date
        $timeDeposit->maturity_date = $validated['maturity_date'];
        $timeDeposit->save();

        return redirect()->back()->with('success', 'Time deposit renewed successfully!');
    }

    public function withdraw(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string',
        ]);

        $timeDeposit = TimeDeposit::findOrFail($id);
        
        $currentBalance = $timeDeposit->beginning_balance;
        $withdrawAmount = (float)$validated['amount'];
        $isFullWithdrawal = abs($withdrawAmount - $currentBalance) < 0.01; // Account for floating point precision
        
        // Create withdrawal record
        TimeDepositWithdrawal::create([
            'time_deposit_id' => $timeDeposit->id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);
        
        // Update beginning balance
        $timeDeposit->beginning_balance -= $validated['amount'];
        
        // If withdrawing all, set maturity date to null
        if ($isFullWithdrawal) {
            $timeDeposit->maturity_date = null;
        }
        
        $timeDeposit->save();

        return redirect()->back()->with('success', 'Time deposit withdrawal recorded successfully!');
    }

    public function addBalance(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'required|string',
        ]);

        $timeDeposit = TimeDeposit::findOrFail($id);
        
        // Create balance addition record
        TimeDepositBalance::create([
            'time_deposit_id' => $timeDeposit->id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation'],
        ]);
        
        // Update beginning balance
        $timeDeposit->beginning_balance += $validated['amount'];
        $timeDeposit->save();

        return redirect()->back()->with('success', 'Balance added successfully!');
    }
}
