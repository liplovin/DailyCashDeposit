<?php

namespace App\Http\Controllers;

use App\Models\CashInfusion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashInfusionController extends Controller
{
    public function index()
    {
        $cashInfusions = CashInfusion::all();
        return Inertia::render('Treasury/Cash Infusion/Index', [
            'cashInfusions' => $cashInfusions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cash_infusion_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:cash_infusions,account_number',
            'beginning_balance' => 'required|numeric|min:0',
        ]);

        CashInfusion::create($validated);

        return redirect('/treasury/cash-infusion')->with('success', 'Cash Infusion created successfully.');
    }

    public function update(Request $request, $id)
    {
        $cashInfusion = CashInfusion::findOrFail($id);

        $validated = $request->validate([
            'cash_infusion_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:cash_infusions,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
        ]);

        $cashInfusion->update($validated);

        return redirect('/treasury/cash-infusion')->with('success', 'Cash Infusion updated successfully.');
    }

    public function destroy($id)
    {
        $cashInfusion = CashInfusion::findOrFail($id);
        $cashInfusion->delete();

        return redirect('/treasury/cash-infusion')->with('success', 'Cash Infusion deleted successfully.');
    }
}
