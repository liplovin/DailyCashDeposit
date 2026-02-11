<?php

namespace App\Http\Controllers;

use App\Models\OperatingAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperatingAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $operatingAccounts = OperatingAccount::all();
        return Inertia::render('Treasury/Operating Accounts/Index', [
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
        ]);

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
        ]);

        $operatingAccount->update($validated);

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account updated successfully.');
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
}
