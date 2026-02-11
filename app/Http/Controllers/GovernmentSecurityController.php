<?php

namespace App\Http\Controllers;

use App\Models\GovernmentSecurity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GovernmentSecurityController extends Controller
{
    public function index()
    {
        $governmentSecurities = GovernmentSecurity::all();
        return Inertia::render('Treasury/Goverment Securities/Index', [
            'governmentSecurities' => $governmentSecurities
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'government_security_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:government_securities,account_number',
            'beginning_balance' => 'required|numeric|min:0',
        ]);

        GovernmentSecurity::create($validated);

        return redirect()->route('treasury.government-securities')->with('success', 'Government Security created successfully');
    }

    public function destroy($id)
    {
        $governmentSecurity = GovernmentSecurity::findOrFail($id);
        $governmentSecurity->delete();

        return redirect()->route('treasury.government-securities')->with('success', 'Government Security deleted successfully');
    }

    public function update(Request $request, $id)
    {
        $governmentSecurity = GovernmentSecurity::findOrFail($id);

        $validated = $request->validate([
            'government_security_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:government_securities,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
        ]);

        $governmentSecurity->update($validated);

        return redirect()->route('treasury.government-securities')->with('success', 'Government Security updated successfully');
    }
}
