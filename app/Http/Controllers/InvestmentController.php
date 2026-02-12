<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentController extends Controller
{
    public function index()
    {
        $investments = Investment::all();
        return Inertia::render('Treasury/Investment/Index', [
            'investments' => $investments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'investment_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:investments,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        Investment::create($validated);

        return redirect('/treasury/investment')->with('success', 'Investment created successfully.');
    }

    public function update(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $validated = $request->validate([
            'investment_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:investments,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        $investment->update($validated);

        return redirect('/treasury/investment')->with('success', 'Investment updated successfully.');
    }

    public function destroy($id)
    {
        $investment = Investment::findOrFail($id);
        $investment->delete();

        return redirect('/treasury/investment')->with('success', 'Investment deleted successfully.');
    }

    private function convertDateFormat($dateString)
    {
        $parts = explode('/', $dateString);
        return $parts[2] . '-' . $parts[0] . '-' . $parts[1];
    }
}
