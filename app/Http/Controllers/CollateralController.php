<?php

namespace App\Http\Controllers;

use App\Models\Collateral;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CollateralController extends Controller
{
    public function index()
    {
        $collaterals = Collateral::all();
        return Inertia::render('Treasury/Collateral/Index', [
            'collaterals' => $collaterals
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'collateral' => 'required|string|max:255',
            'account_number' => 'required|string|unique:collaterals,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/y',
        ]);

        // Convert mm/dd/yy format to yyyy-mm-dd for database
        if ($validated['maturity_date']) {
            $date = \DateTime::createFromFormat('m/d/y', $validated['maturity_date']);
            $validated['maturity_date'] = $date->format('Y-m-d');
        }

        Collateral::create($validated);

        return redirect()->route('treasury.collateral')->with('success', 'Collateral created successfully');
    }

    public function destroy($id)
    {
        $collateral = Collateral::findOrFail($id);
        $collateral->delete();

        return redirect()->route('treasury.collateral')->with('success', 'Collateral deleted successfully');
    }

    public function update(Request $request, $id)
    {
        $collateral = Collateral::findOrFail($id);

        $validated = $request->validate([
            'collateral' => 'required|string|max:255',
            'account_number' => 'required|string|unique:collaterals,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/y',
        ]);

        // Convert mm/dd/yy format to yyyy-mm-dd for database
        if ($validated['maturity_date']) {
            $date = \DateTime::createFromFormat('m/d/y', $validated['maturity_date']);
            $validated['maturity_date'] = $date->format('Y-m-d');
        }

        $collateral->update($validated);

        return redirect()->route('treasury.collateral')->with('success', 'Collateral updated successfully');
    }
}
