<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\TransactionType;
use Illuminate\Http\Request;

class TransactionTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactionTypes = TransactionType::paginate(10);
        return view('transaction-types.index', compact('transactionTypes'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_name' => 'required|string|max:255|unique:transaction_types',
            'description' => 'nullable|string',
        ]);

        TransactionType::create($validated);
        return back()->with('success', 'Successfully created!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transactionType = TransactionType::findOrFail($id);
        return response()->json($transactionType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'transaction_name' => 'required|string|max:255|unique:transaction_types,transaction_name,' . $id,
            'description' => 'nullable|string',
            'status' => 'nullable|boolean'
        ]);

        $transactionType = TransactionType::findOrFail($id);
        $transactionType->update($validated);

        return back()->with('success', 'Successfully Edited');
    }


}
