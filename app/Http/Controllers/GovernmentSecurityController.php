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
            'reference_number' => 'required|string',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
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
            'reference_number' => 'required|string',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        $governmentSecurity->update($validated);

        return redirect()->route('treasury.government-securities')->with('success', 'Government Security updated successfully');
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

    public function addCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $governmentSecurity = GovernmentSecurity::findOrFail($id);
        $governmentSecurity->collection += $validated['amount'];
        $governmentSecurity->collection_date = $validated['date'];
        $governmentSecurity->ending_balance = $governmentSecurity->beginning_balance + $governmentSecurity->collection - $governmentSecurity->disbursement;
        $governmentSecurity->save();

        return response()->json([
            'message' => 'Collection added successfully',
            'governmentSecurity' => $governmentSecurity
        ]);
    }

    public function addDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $governmentSecurity = GovernmentSecurity::findOrFail($id);
        $governmentSecurity->disbursement += $validated['amount'];
        $governmentSecurity->disbursement_date = $validated['date'];
        $governmentSecurity->ending_balance = $governmentSecurity->beginning_balance + $governmentSecurity->collection - $governmentSecurity->disbursement;
        $governmentSecurity->save();

        return response()->json([
            'message' => 'Disbursement added successfully',
            'governmentSecurity' => $governmentSecurity
        ]);
    }

    public function updateCollection(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $governmentSecurity = GovernmentSecurity::findOrFail($id);
        $governmentSecurity->collection = $validated['amount'];
        $governmentSecurity->ending_balance = $governmentSecurity->beginning_balance + $governmentSecurity->collection - $governmentSecurity->disbursement;
        $governmentSecurity->save();

        return response()->json([
            'message' => 'Collection updated successfully',
            'governmentSecurity' => $governmentSecurity
        ]);
    }

    public function updateDisbursement(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $governmentSecurity = GovernmentSecurity::findOrFail($id);
        $governmentSecurity->disbursement = $validated['amount'];
        $governmentSecurity->ending_balance = $governmentSecurity->beginning_balance + $governmentSecurity->collection - $governmentSecurity->disbursement;
        $governmentSecurity->save();

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'governmentSecurity' => $governmentSecurity
        ]);
    }
}
