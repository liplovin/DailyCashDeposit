<?php

namespace App\Http\Controllers;

use App\Models\OtherInvestment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OtherInvestmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $otherInvestments = OtherInvestment::all();
        return Inertia::render('Treasury/Other Investment/Index', [
            'otherInvestments' => $otherInvestments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'other_investment_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:other_investments,account_number',
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        OtherInvestment::create($validated);

        return redirect('/treasury/other-investment')->with('success', 'Other Investment created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $otherInvestment = OtherInvestment::findOrFail($id);

        $validated = $request->validate([
            'other_investment_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:other_investments,account_number,' . $id,
            'beginning_balance' => 'required|numeric|min:0',
            'maturity_date' => 'required|date_format:m/d/Y',
        ]);

        // Convert mm/dd/yy to Y-m-d format for storage
        $validated['maturity_date'] = $this->convertDateFormat($validated['maturity_date']);

        $otherInvestment->update($validated);

        return redirect('/treasury/other-investment')->with('success', 'Other Investment updated successfully.');
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $otherInvestment = OtherInvestment::findOrFail($id);
        $otherInvestment->delete();

        return redirect('/treasury/other-investment')->with('success', 'Other Investment deleted successfully.');
    }
}
