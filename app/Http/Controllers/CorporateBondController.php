<?php

namespace App\Http\Controllers;

use App\Models\CorporateBond;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CorporateBondController extends Controller
{
    public function index()
    {
        $corporateBonds = CorporateBond::all();
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
}
