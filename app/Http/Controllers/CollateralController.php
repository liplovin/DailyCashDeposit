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
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

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
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yyyy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        $collateral->update($validated);

        return redirect()->route('treasury.collateral')->with('success', 'Collateral updated successfully');
    }

    public function addCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $collateral = Collateral::findOrFail($id);
        $collateral->collection += $validated['amount'];
        $collateral->collection_date = $validated['date'];
        $collateral->ending_balance = $collateral->beginning_balance + $collateral->collection - $collateral->disbursement;
        $collateral->save();

        return response()->json([
            'message' => 'Collection added successfully',
            'collateral' => $collateral
        ]);
    }

    public function addDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $collateral = Collateral::findOrFail($id);
        $collateral->disbursement += $validated['amount'];
        $collateral->disbursement_date = $validated['date'];
        $collateral->ending_balance = $collateral->beginning_balance + $collateral->collection - $collateral->disbursement;
        $collateral->save();

        return response()->json([
            'message' => 'Disbursement added successfully',
            'collateral' => $collateral
        ]);
    }

    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $collateral = Collateral::findOrFail($id);
        $collateral->collection = $validated['amount'];
        $collateral->collection_date = $validated['date'];
        $collateral->ending_balance = $collateral->beginning_balance + $collateral->collection - $collateral->disbursement;
        $collateral->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'collateral' => $collateral
        ]);
    }

    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $collateral = Collateral::findOrFail($id);
        $collateral->disbursement = $validated['amount'];
        $collateral->disbursement_date = $validated['date'];
        $collateral->ending_balance = $collateral->beginning_balance + $collateral->collection - $collateral->disbursement;
        $collateral->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'collateral' => $collateral
        ]);
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
}
