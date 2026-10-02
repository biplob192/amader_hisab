<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $requestedMonth = $request->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? null;
        $month = $requestedMonth ? Carbon::createFromFormat('Y-m', $requestedMonth)->startOfMonth() : now()->startOfMonth();
        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();
        $expenses = Expense::query()->where('user_id', $request->user()->id)->whereBetween('spent_on', [$start, $end]);
        $householdExpenses = Expense::query()->where('user_id', $request->user()->id);
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();
        $selectedMonthTotal = (clone $householdExpenses)->whereBetween('spent_on', [$start, $end])->sum(DB::raw('amount - returned_amount'));
        $daysInSelectedMonth = $month->format('Y-m') === now()->format('Y-m')
            ? (int) now()->format('j')
            : (int) $month->copy()->endOfMonth()->format('j');

        return view('dashboard', [
            'month' => $month,
            'total' => (clone $expenses)->sum(DB::raw('amount - returned_amount')),
            'totalExpense' => (clone $householdExpenses)->sum(DB::raw('amount - returned_amount')),
            'todayTotal' => (clone $householdExpenses)->whereDate('spent_on', $today)->sum(DB::raw('amount - returned_amount')),
            'weekTotal' => (clone $householdExpenses)->whereBetween('spent_on', [$weekStart, $weekEnd])->sum(DB::raw('amount - returned_amount')),
            'selectedMonthTotal' => $selectedMonthTotal,
            'averagePerDay' => $selectedMonthTotal / $daysInSelectedMonth,
            'count' => (clone $expenses)->count(),
            'byPayer' => (clone $expenses)->selectRaw('paid_by, SUM(amount - returned_amount) as total')->groupBy('paid_by')->havingRaw('SUM(amount - returned_amount) > 0')->orderByDesc('total')->get(),
            'byCategory' => (clone $expenses)->selectRaw('expense_category_id, SUM(amount - returned_amount) as total')->with('category')->groupBy('expense_category_id')->havingRaw('SUM(amount - returned_amount) > 0')->orderByDesc('total')->get(),
            'topCategory' => (clone $expenses)->selectRaw('expense_category_id, SUM(amount - returned_amount) as total')->with('category')->groupBy('expense_category_id')->havingRaw('SUM(amount - returned_amount) > 0')->orderByDesc('total')->first(),
            'recentExpenses' => Expense::with('category')->where('user_id', $request->user()->id)->latest('spent_on')->latest('id')->limit(6)->get(),
        ]);
    }
}
