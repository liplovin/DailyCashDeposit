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
        $user = auth()->user();
        $operatingAccounts = OperatingAccount::all();
        
        // Render based on user role
        $component = match($user->role) {
            'treasury3' => 'Treasury3/OperatingAccount/Index',
            default => 'Treasury/Operating Accounts/Index'
        };
        
        return Inertia::render($component, [
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
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yyyy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

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
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yyyy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        $operatingAccount->update($validated);

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account updated successfully.');
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
        $operatingAccount = OperatingAccount::findOrFail($id);
        $operatingAccount->delete();

        return redirect('/treasury/operating-accounts')->with('success', 'Operating Account deleted successfully.');
    }
}
