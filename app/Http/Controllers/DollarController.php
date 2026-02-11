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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dollar_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:dollars,account_number',
            'beginning_balance' => 'required|numeric|min:0',
        ]);

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
        ]);

        $dollar->update($validated);

        return redirect('/treasury/dollar')->with('success', 'Dollar updated successfully.');
    }

    public function destroy($id)
    {
        $dollar = Dollar::findOrFail($id);
        $dollar->delete();

        return redirect('/treasury/dollar')->with('success', 'Dollar deleted successfully.');
    }
}
