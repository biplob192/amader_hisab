<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $requestedMonth = $request->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? null;
        $month = $requestedMonth ? Carbon::createFromFormat('Y-m', $requestedMonth)->startOfMonth() : now()->startOfMonth();
        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();
        $previousStart = $month->copy()->subMonth()->startOfMonth()->toDateString();
        $previousEnd = $month->copy()->subMonth()->endOfMonth()->toDateString();
        $userId = $request->user()->id;

        $current = Expense::query()->where('user_id', $userId)->whereBetween('spent_on', [$start, $end])
            ->selectRaw('expense_category_id, SUM(amount - returned_amount) as total')->groupBy('expense_category_id')->with('category')->get()->keyBy('expense_category_id');
        $previous = Expense::query()->where('user_id', $userId)->whereBetween('spent_on', [$previousStart, $previousEnd])
            ->selectRaw('expense_category_id, SUM(amount - returned_amount) as total')->groupBy('expense_category_id')->pluck('total', 'expense_category_id');

        $categories = ExpenseCategory::query()->orderBy('name')->get()->map(function ($category) use ($current, $previous) {
            $amount = (float) ($current->get($category->id)?->total ?? 0);
            $priorAmount = (float) ($previous->get($category->id) ?? 0);

            return (object) [
                'category' => $category,
                'amount' => $amount,
                'previous' => $priorAmount,
                'change' => $priorAmount > 0 ? (($amount - $priorAmount) / $priorAmount) * 100 : null,
            ];
        })->sortByDesc('amount')->values();

        $daily = Expense::where('user_id', $userId)->whereBetween('spent_on', [$start, $end])
            ->selectRaw('DATE(spent_on) as day, SUM(amount - returned_amount) as total')->groupBy('day')->orderBy('day')->get();

        return view('reports.index', [
            'month' => $month,
            'categories' => $categories,
            'total' => $categories->sum('amount'),
            'previousTotal' => $categories->sum('previous'),
            'daily' => $daily,
            'highest' => $categories->first(),
        ]);
    }
}
