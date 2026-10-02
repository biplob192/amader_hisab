<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    private const PAYERS = ['Father', 'Mother', 'Sister', 'Brother', 'Myself', 'Wife'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'expense_category_id' => ['nullable', 'integer', Rule::exists('expense_categories', 'id')],
            'paid_by' => ['nullable', Rule::in(self::PAYERS)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', Rule::in([5, 10, 25, 50])],
        ]);
        $filters = array_filter($filters, fn ($value) => filled($value));
        $perPage = (int) ($filters['per_page'] ?? 5);
        $hasFilters = count(array_diff_key($filters, ['per_page' => true])) > 0;

        $query = Expense::with('category')->where('user_id', $request->user()->id);

        if (isset($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($query) use ($search) {
                $query->where('notes', 'like', "%{$search}%")
                    ->orWhere('paid_by', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $query
            ->when($filters['expense_category_id'] ?? null, fn ($query, $categoryId) => $query->where('expense_category_id', $categoryId))
            ->when($filters['paid_by'] ?? null, fn ($query, $paidBy) => $query->where('paid_by', $paidBy))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('spent_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('spent_on', '<=', $date));

        return view('expenses.index', [
            'expenses' => $query->latest('spent_on')->latest('id')->paginate($perPage)->withQueryString(),
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
            'payers' => self::PAYERS,
            'filters' => $filters,
            'perPage' => $perPage,
            'hasFilters' => $hasFilters,
        ]);
    }

    public function create(Request $request): View
    {
        return view('expenses.create', [
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
            'payers' => self::PAYERS,
        ]);
    }

    public function edit(Request $request, Expense $expense): View
    {
        abort_unless($expense->user_id === $request->user()->id, 404);

        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
            'payers' => self::PAYERS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->expenses()->create($this->validatedExpense($request));

        return to_route('expenses.index')->with('status', 'Expense added.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless($expense->user_id === $request->user()->id, 404);
        $expense->update($this->validatedExpense($request));

        return to_route('expenses.index')->with('status', 'Expense updated.');
    }

    private function validatedExpense(Request $request): array
    {
        return $request->validate([
            'expense_category_id' => ['required', 'integer', Rule::exists('expense_categories', 'id')],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'returned_amount' => ['required', 'numeric', 'gte:0', 'lte:amount', 'max:9999999999.99'],
            'paid_by' => ['required', Rule::in(self::PAYERS)],
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless($expense->user_id === $request->user()->id, 404);
        $expense->delete();

        return to_route('expenses.index')->with('status', 'Expense deleted.');
    }
}
