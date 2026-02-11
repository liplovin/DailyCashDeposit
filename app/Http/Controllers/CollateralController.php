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
        ]);

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
        ]);

        $collateral->update($validated);

        return redirect()->route('treasury.collateral')->with('success', 'Collateral updated successfully');
    }
}
