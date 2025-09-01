<?php

namespace App\Http\Controllers\Operation;

use App\Http\Cache\CacheAccount;
use App\Http\Cache\CacheExpenseCategory;
use App\Http\Cache\CacheTransaction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Expense as ResourcesExpense;
use App\Models\Expense;
use App\Models\Status;
use App\Models\Transaction;
use App\RolePermission;
use App\UseBranch;
use App\UseRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the expenses list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $isDateSearch = RolePermission::isEnabled('record_search.expense_date_search');

        if ($isDateSearch) {
            $end_date = $request->end_date ? now()->parse($request->end_date) : now();
            $start_date = $request->start_date ? now()->parse($request->start_date) : now()->startOfMonth();
        } else {
            $end_date = now();
            $start_date = now()->startOfMonth();
        }

        $account_id = $request->account_id;

        // dd($end_date, $start_date, $account_id);

        $expenses = Expense::when($start_date, function ($query, $start_date) {
            $query->where('date', '>=', $start_date);
        })
            ->when($end_date, function ($query, $end_date) {
                $query->where('date', '<=', $end_date);
            })
            ->when($account_id, function ($query, $account_id) {
                $query->where('account_id', $account_id);
            })
            ->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();

        $params = [
            'accounts' => CacheAccount::get(),
            'categories' => CacheExpenseCategory::get(),
            'expenses' => $expenses,
            'filter' => [
                'account_id' => $account_id,
                'end_date' => $end_date->format('Y-m-d'),
                'start_date' => $start_date->format('Y-m-d'),
            ],
        ];

        return Inertia::render('Operation/Expense/Index', $params);
    }

    /**
     * Show the expense create.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        $params = [
            'account_id' => $request->account_id,
            'expense_categories' => CacheExpenseCategory::get(),
            'accounts' => CacheAccount::get(),
        ];

        return Inertia::render('Operation/Expense/Create', $params);
    }

    /**
     * Create new resource in storage.
     *
     * @param  Expense  $expense
     * @return Response
     */
    public function store(Request $request)
    {
        if (! UseBranch::id()) {
            return back()->with('fail', 'Please specify branch first.');
        }

        $request->validate([
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric'],
            'check_no' => ['nullable', 'string', 'unique:transactions'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();

        $transaction = new Transaction;
        $transaction->date = now()->parse($request->date);
        $transaction->check_no = $request->check_no;
        $transaction->amount = $request->amount;
        $transaction->direction = 'out';
        $transaction->account_id = $request->account_id;
        $transaction->creator_id = Auth::id();
        $transaction->save();

        $expense = new Expense;
        $expense->date = now()->parse($request->date);
        $expense->amount = $request->amount;
        $expense->account_id = $request->account_id;
        $expense->transaction_id = $transaction->id;
        $expense->expense_category_id = $request->expense_category_id;
        $expense->branch_id = UseBranch::id();
        $expense->save();

        UseRecord::makeRemark($request->remark, $expense);
        DB::commit();
        CacheTransaction::forget();

        return redirect()->route('expense.index')->with('success', 'Expense created successfully.');
    }

    /**
     * Show the expense detail page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Expense $expense)
    {
        $params = [
            'expense' => new ResourcesExpense($expense),
        ];

        return Inertia::render('Operation/Expense/Show', $params);
    }

    /**
     * Show the expense edit page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Expense $expense)
    {
        $params = [
            'expense' => new ResourcesExpense($expense),
            'accounts' => CacheAccount::get(),
            'expense_categories' => CacheExpenseCategory::get(),
        ];

        return Inertia::render('Operation/Expense/Edit', $params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric'],
            // 'check_no'              => ['nullable', 'unique:transactions,' . $expense->transaction->id],
            'check_no' => ['nullable', 'string', Rule::unique('transactions')->ignore($expense->transaction)],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();

        $transaction = $expense->transaction;
        if ($request->date != $transaction->date || $request->check_no != $transaction->check_no || $request->amount != $transaction->amount || $request->account_id != $transaction->account_id) {
            $transaction->date = now()->parse($request->date);
            $transaction->check_no = $request->check_no;
            $transaction->amount = $request->amount;
            $transaction->account_id = $request->account_id;
            $transaction->update();
        }

        if ($request->date != $expense->date || $request->amount != $expense->amount || $request->expense_category_id != $expense->expense_category_id || $request->account_id != $expense->account_id) {
            $expense->date = now()->parse($request->date);
            $expense->amount = $request->amount;
            $expense->account_id = $request->account_id;
            $expense->expense_category_id = $request->expense_category_id;
            $expense->update();
        }

        UseRecord::makeRemark($request->remark, $expense);
        DB::commit();
        CacheTransaction::forget();

        return back()->with('success', 'Expense updated successfully.');
    }

    /**
     * Delete the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Expense $expense)
    {
        DB::beginTransaction();
        $expense->delete();

        DB::commit();

        return redirect()->route('expense.index')->with('success', __('Expense removed successfully!'));
    }

    /**
     * Change status the specified resource in storage.
     *
     * @return Response
     */
    public function updateStatus(Request $request, Expense $expense)
    {
        if (! array_key_exists($request->status, $expense->statuses)) {
            return back()->with('fail', 'Status changing request failed! Invalid status!');
        }

        DB::beginTransaction();
        $expense->changeStatuses()->save(new Status([
            'previous_status' => $expense->status ?? '',
            'changed_status' => $request->status,
            'user_id' => Auth::id(),
        ]));
        $expense->status = $request->status;
        $expense->save();
        DB::commit();

        return back()->with('success', 'Status changed to "'.$expense->statuses[$request->status].'" successfully');
    }
}
