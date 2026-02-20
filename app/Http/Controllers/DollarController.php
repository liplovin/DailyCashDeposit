<?php

namespace App\Http\Controllers;

use App\Models\Dollar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DollarController extends Controller
{
    public function index()
    {
        $dollars = Dollar::all();
        return Inertia::render('Treasury/Dollar/Index', [
            'dollars' => $dollars,
        ]);
    }

    /**
     * Add collection to dollar
     */
    public function addCollection(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $dollar = Dollar::findOrFail($id);

            // Update collection fields - accumulate the amount
            $dollar->collection += $validated['amount'];
            $dollar->collection_date = $validated['date'];

            // Calculate ending balance: beginning + collection - disbursement
            $beginning = (float) $dollar->beginning_balance;
            $collection = (float) $dollar->collection;
            $disbursement = (float) ($dollar->disbursement ?? 0);
            $dollar->ending_balance = $beginning + $collection - $disbursement;

            $dollar->save();

            return response()->json([
                'message' => 'Collection added successfully',
                'dollar' => $dollar
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Add disbursement to dollar
     */
    public function addDisbursement(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $dollar = Dollar::findOrFail($id);

            // Update disbursement fields - accumulate the amount
            $dollar->disbursement += $validated['amount'];
            $dollar->disbursement_date = $validated['date'];

            // Calculate ending balance: beginning + collection - disbursement
            $beginning = (float) $dollar->beginning_balance;
            $collection = (float) ($dollar->collection ?? 0);
            $disbursement = (float) $dollar->disbursement;
            $dollar->ending_balance = $beginning + $collection - $disbursement;

            $dollar->save();

            return response()->json([
                'message' => 'Disbursement added successfully',
                'dollar' => $dollar
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dollar_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:dollars,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        Dollar::create($validated);

        return redirect('/treasury/dollar')->with('success', 'Dollar created successfully.');
    }

    public function update(Request $request, $id)
    {
        $dollar = Dollar::findOrFail($id);

        $validated = $request->validate([
            'dollar_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:dollars,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);
        $dollar->update($validated);

        return redirect('/treasury/dollar')->with('success', 'Dollar updated successfully.');
    }

    public function destroy($id)
    {
        $dollar = Dollar::findOrFail($id);
        $dollar->delete();

        return redirect('/treasury/dollar')->with('success', 'Dollar deleted successfully.');
    }

    /**
     * Update collection amount
     */
    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $dollar = Dollar::findOrFail($id);
        $dollar->collection = $validated['amount'];
        $dollar->ending_balance = $dollar->beginning_balance + $dollar->collection - $dollar->disbursement;
        $dollar->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'dollar' => $dollar
        ]);
    }

    /**
     * Update disbursement amount
     */
    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $dollar = Dollar::findOrFail($id);
        $dollar->disbursement = $validated['amount'];
        $dollar->ending_balance = $dollar->beginning_balance + $dollar->collection - $dollar->disbursement;
        $dollar->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'dollar' => $dollar
        ]);
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
