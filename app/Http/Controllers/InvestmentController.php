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
            'reference_number' => 'required|string',
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
            'reference_number' => 'required|string',
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

    public function addCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->collection += $validated['amount'];
        $investment->collection_date = $validated['date'];
        $investment->ending_balance = $investment->beginning_balance + $investment->collection - ($investment->disbursement ?? 0);
        $investment->save();

        return response()->json([
            'message' => 'Collection added successfully',
            'investment' => $investment
        ]);
    }

    public function addDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->disbursement += $validated['amount'];
        $investment->disbursement_date = $validated['date'];
        $investment->ending_balance = $investment->beginning_balance + $investment->collection - $investment->disbursement;
        $investment->save();

        return response()->json([
            'message' => 'Disbursement added successfully',
            'investment' => $investment
        ]);
    }

    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->collection = $validated['amount'];
        $investment->ending_balance = $investment->beginning_balance + $investment->collection - $investment->disbursement;
        $investment->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'investment' => $investment
        ]);
    }

    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->disbursement = $validated['amount'];
        $investment->ending_balance = $investment->beginning_balance + $investment->collection - $investment->disbursement;
        $investment->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'investment' => $investment
        ]);
    }
}
