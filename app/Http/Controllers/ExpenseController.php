<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (auth()->user()->type == 'admin') {
            $expenses = Expense::query();
        } else {
            $expenses = Expense::where('user_id', auth()->user()->id);
            $today = now()->startOfDay();
            if ($request->start_date && $request->end_date) {
                $expenses = $expenses->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
            } else {
                $expenses = $expenses->whereDate('created_at', $today);
            }
        }

        $expenses = $expenses->with(['user'])->latest()->paginate(25);



        return view('expense.index', ['expenses' => $expenses]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('expense.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'head' => 'required|string',
            'amount' => 'required|decimal:0,2',
            'notes' => 'nullable|string',
        ]);


        $expense = Expense::create([
            'user_id' => auth()->user()->id,
            'head' => $request->head,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        if (!$expense) {
            return redirect()->back()->with('error', __('error_creating'));
        }
        return redirect()->route('expenses.index')->with('success', __('success_creating'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return redirect()->back()->with('error', __('error_editing'));
        }

        return view('expense.edit')->with('expense', $expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'head' => 'required|string',
            'amount' => 'required|decimal:0,2',
            'notes' => 'nullable|string',
        ]);


        $expense = Expense::find($id);
        if (!$expense) {
            return redirect()->back()->with('error', __('error_updating'));
        }

        $expense->head = $request->head;
        $expense->amount = $request->amount;
        $expense->notes = $request->notes;

        if (!$expense->save()) {
            return redirect()->back()->with('error', __('error_updating'));
        }
        return redirect()->route('expenses.index')->with('success', __('success_updating'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return redirect()->back()->with('error', __('error_deleting'));
        }

        if (!$expense->delete()) {
            return redirect()->back()->with('error', __('error_deleting'));
        }
        Log::info('Expense deleted: ' . $expense->id);
        return redirect()->route('expenses.index')->with('success', __('success_deleting'));
    }
}
