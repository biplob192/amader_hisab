<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $requestedMonth = $request->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? null;
        $month = $requestedMonth ? Carbon::createFromFormat('Y-m', $requestedMonth)->startOfMonth() : now()->startOfMonth();
        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();
        $yearStart = $month->copy()->startOfYear()->toDateString();
        $yearEnd = $month->copy()->endOfYear()->toDateString();
        $userId = (int) $request->user()->id;
        $expenses = Expense::query()->where('user_id', $request->user()->id)->whereBetween('spent_on', [$start, $end]);
        $householdExpenses = Expense::query()->where('user_id', $request->user()->id);
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();
        $selectedMonthTotal = (clone $householdExpenses)->whereBetween('spent_on', [$start, $end])->sum(DB::raw('amount - returned_amount'));
        $daysInSelectedMonth = $month->format('Y-m') === now()->format('Y-m')
            ? (int) now()->format('j')
            : (int) $month->copy()->endOfMonth()->format('j');
        $dailyExpenseChart = new LaravelChart([
            'chart_title' => 'Daily expense', 'chart_type' => 'line', 'report_type' => 'group_by_date',
            'model' => Expense::class, 'group_by_field' => 'spent_on', 'group_by_period' => 'day',
            'date_format' => 'M j', 'aggregate_function' => 'sum', 'aggregate_field' => 'net_amount',
            'filter_field' => 'spent_on', 'range_date_start' => $start, 'range_date_end' => $end,
            'where_raw' => 'user_id = '.$userId, 'chart_color' => '43, 107, 76, 1', 'chart_height' => '280px',
        ]);
        $monthlyExpenseChart = new LaravelChart([
            'chart_title' => 'Monthly expense', 'chart_type' => 'bar', 'report_type' => 'group_by_date',
            'model' => Expense::class, 'group_by_field' => 'spent_on', 'group_by_period' => 'month',
            'date_format' => 'M Y', 'aggregate_function' => 'sum', 'aggregate_field' => 'net_amount',
            'filter_field' => 'spent_on', 'range_date_start' => $yearStart, 'range_date_end' => $yearEnd,
            'where_raw' => 'user_id = '.$userId, 'chart_color' => '43, 107, 76, 1', 'chart_height' => '280px',
        ]);

        return view('dashboard', [
            'month' => $month,
            'dailyExpenseChart' => $dailyExpenseChart,
            'monthlyExpenseChart' => $monthlyExpenseChart,
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
