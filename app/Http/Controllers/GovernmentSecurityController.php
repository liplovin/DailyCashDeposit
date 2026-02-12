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
            'maturity_date' => 'required|date_format:m/d/y',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

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
            'maturity_date' => 'required|date_format:m/d/y',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        $governmentSecurity->update($validated);

        return redirect()->route('treasury.government-securities')->with('success', 'Government Security updated successfully');
    }

    private function convertDateFormat($dateString)
    {
        // Convert mm/dd/yy to Y-m-d
        $parts = explode('/', $dateString);
        if (count($parts) === 3) {
            $month = $parts[0];
            $day = $parts[1];
            $year = $parts[2];

            // Convert 2-digit year to 4-digit
            if (strlen($year) === 2) {
                $year = (int)$year > 30 ? '19' . $year : '20' . $year;
            }

            return $year . '-' . $month . '-' . $day;
        }
        return $dateString;
    }
}
