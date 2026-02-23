<?php

namespace App\Http\Controllers;

use App\Models\GovernmentSecurity;
use App\Models\GovernmentSecurityRenewal;
use App\Models\GovernmentSecurityWithdrawal;
use App\Models\GovernmentSecurityBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GovernmentSecurityController extends Controller
{
    public function index()
    {
        $governmentSecurities = GovernmentSecurity::with('renewals', 'withdrawals', 'balances')->get();
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

    public function renew(Request $request, $id)
    {
        $validated = $request->validate([
            'new_maturity_date' => 'required|date|after:today',
            'explanation' => 'nullable|string',
        ]);

        $governmentSecurity = GovernmentSecurity::findOrFail($id);

        // Validate new date is after current maturity date
        if ($governmentSecurity->maturity_date && strtotime($validated['new_maturity_date']) <= strtotime($governmentSecurity->maturity_date)) {
            return redirect()->back()->withErrors(['new_maturity_date' => 'New maturity date must be after current maturity date']);
        }

        // Create renewal record
        GovernmentSecurityRenewal::create([
            'government_security_id' => $id,
            'previous_maturity_date' => $governmentSecurity->maturity_date,
            'new_maturity_date' => $validated['new_maturity_date'],
            'explanation' => $validated['explanation']
        ]);

        // Update maturity date
        $governmentSecurity->update(['maturity_date' => $validated['new_maturity_date']]);

        return redirect()->back()->with('success', 'Government Security renewed successfully!');
    }

    public function withdraw(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'explanation' => 'nullable|string',
        ]);

        $governmentSecurity = GovernmentSecurity::findOrFail($id);
        $currentBalance = $governmentSecurity->beginning_balance;

        // Validate amount does not exceed balance
        if ($validated['amount'] > $currentBalance) {
            return redirect()->back()->withErrors(['amount' => 'Withdrawal amount cannot exceed available balance']);
        }

        // Create withdrawal record
        GovernmentSecurityWithdrawal::create([
            'government_security_id' => $id,
            'amount' => $validated['amount'],
            'explanation' => $validated['explanation']
        ]);

        // Check if this is a full withdrawal (with floating-point tolerance)
        $remainingBalance = $currentBalance - $validated['amount'];
        if (abs($remainingBalance) < 0.01) {
            // Full withdrawal - set maturity_date to NULL
            $governmentSecurity->update(['maturity_date' => null]);
        }

        return redirect()->back()->with('success', 'Withdrawal recorded successfully!');
    }

    public function addBalance(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        $governmentSecurity = GovernmentSecurity::findOrFail($id);

        // Create balance record
        GovernmentSecurityBalance::create([
            'government_security_id' => $id,
            'amount' => $validated['amount'],
            'reason' => $validated['reason']
        ]);

        return redirect()->back()->with('success', 'Balance added successfully!');
    }
}
