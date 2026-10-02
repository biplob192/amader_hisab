<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $categories = ExpenseCategory::query()
            ->withCount(['expenses as expense_count' => fn ($query) => $query->where('user_id', $userId)])
            ->withSum(['expenses as given_total' => fn ($query) => $query->where('user_id', $userId)], 'amount')
            ->withSum(['expenses as returned_total' => fn ($query) => $query->where('user_id', $userId)], 'returned_amount')
            ->get();

        $rows = $categories->map(fn (ExpenseCategory $category): array => [
            'name' => $category->name,
            'color' => $category->color,
            'count' => (int) $category->expense_count,
            'total' => (float) $category->given_total - (float) $category->returned_total,
        ]);

        $totalSpend = (float) $rows->sum('total');

        $rows = $rows
            ->map(fn (array $row): array => $row + [
                'share' => $totalSpend > 0 ? ($row['total'] / $totalSpend) * 100 : 0.0,
            ])
            ->sortBy([['total', 'desc'], ['name', 'asc']])
            ->values();

        return view('categories.index', [
            'categories' => $rows,
            'totalSpend' => $totalSpend,
            'expenseCount' => (int) $rows->sum('count'),
        ]);
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ExpenseCategory::create($this->validatedCategory($request));

        return to_route('categories.index')->with('status', 'Category added.');
    }

    private function validatedCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('expense_categories', 'name')],
            'color' => ['required', 'string', 'regex:/^#[0-9a-f]{6}$/i'],
        ]);
    }
}
