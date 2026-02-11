<?php

namespace App\Http\Controllers;

use App\Models\TimeDeposit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeDepositController extends Controller
{
    public function index()
    {
        $timeDeposits = TimeDeposit::all();
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
        ]);

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
        ]);

        $timeDeposit->update($validated);

        return redirect()->route('treasury.timedeposit')->with('success', 'Time Deposit updated successfully');
    }
}
