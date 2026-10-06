@extends('layouts.authenticated')
@section('title', 'Dashboard')
@section('page-content')

        <header class="dashboard-header dashboard-toolbar">
            <div><span class="eyebrow login-eyebrow">FAMILY FINANCES</span><h1>Household overview</h1><p>Spending summary for {{ $month->format('F Y') }}</p></div>
            <div class="dashboard-actions"><form method="GET" action="{{ route('dashboard') }}" class="month-form"><label for="month">Month</label><input id="month" type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()"></form><a class="primary-action" href="{{ route('expenses.create') }}"><span aria-hidden="true">＋</span> Add expense</a></div>
        </header>

        <section class="stats-grid dashboard-summary">
            <article class="stat-card"><div class="stat-top"><span>Total Expense</span><span class="stat-icon balance-icon"><svg class="metric-icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg></span></div><div class="stat-value">&#2547; {{ number_format($totalExpense, 2) }}</div><div class="stat-foot"><span>All time, after returns</span></div></article>
            <article class="stat-card"><div class="stat-top"><span>Today</span><span class="stat-icon income-icon"><svg class="metric-icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4m8-4v4M4 9h16M8 13h.01M12 13h.01M16 13h.01"/></svg></span></div><div class="stat-value">&#2547; {{ number_format($todayTotal, 2) }}</div><div class="stat-foot"><span>{{ now()->format('M j, Y') }}</span></div></article>
            <article class="stat-card"><div class="stat-top"><span>This Week</span><span class="stat-icon expense-icon"><svg class="metric-icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 9h18M7 13h3m4 0h3M7 17h3m4 0h3"/></svg></span></div><div class="stat-value">&#2547; {{ number_format($weekTotal, 2) }}</div><div class="stat-foot"><span>{{ now()->startOfWeek()->format('M j') }} to {{ now()->endOfWeek()->format('M j') }}</span></div></article>
            <article class="stat-card"><div class="stat-top"><span>This Month</span><span class="stat-icon balance-icon"><svg class="metric-icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 9h18"/></svg></span></div><div class="stat-value">&#2547; {{ number_format($selectedMonthTotal, 2) }}</div><div class="stat-foot"><span>{{ $month->format('F Y') }}</span></div></article>
            <article class="stat-card"><div class="stat-top"><span>Avg/Day</span><span class="stat-icon income-icon"><svg class="metric-icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 19V5m0 14h17"/><path d="M8 15v-3m5 3V8m5 7V5"/></svg></span></div><div class="stat-value">&#2547; {{ number_format($averagePerDay, 2) }}</div><div class="stat-foot"><span>Daily average for {{ $month->format('F Y') }}</span></div></article>
        </section>

        <div class="dashboard-section-heading"><div><h2>Spending breakdown</h2><p>See where household money went</p></div><a class="text-link" href="{{ route('reports.index', ['month' => $month->format('Y-m')]) }}">View reports <span aria-hidden="true">→</span></a></div>
        <section class="dashboard-infographic">
            <article class="info-card"><div class="info-card-heading"><div><h3>By category</h3><p>Net spend per category</p></div><span class="info-period">{{ $month->format('M Y') }}</span></div>
                @php($maxCategorySpend = max(1, (float) $byCategory->max('total')))
                @forelse($byCategory as $item)<div class="info-row"><div class="info-row-label"><span><i class="category-dot" style="--category-color:{{ $item->category->color }}"></i>{{ $item->category->name }}</span><strong>৳ {{ number_format($item->total, 2) }}</strong></div><div class="info-track"><i style="--info-width:{{ (float) $item->total / $maxCategorySpend * 100 }}%;--info-color:{{ $item->category->color }}"></i></div></div>@empty<div class="info-empty">No category spending to show for this month.</div>@endforelse
            </article>
            <article class="info-card"><div class="info-card-heading"><div><h3>Paid by</h3><p>Family contribution by member</p></div><span class="info-period">{{ $month->format('M Y') }}</span></div>
                @forelse($byPayer as $payer)<div class="info-row"><div class="info-row-label"><span>{{ $payer->paid_by }}</span><strong>৳ {{ number_format($payer->total, 2) }}</strong></div><div class="info-track"><i class="payer-info-bar" style="--info-width:{{ $total > 0 ? (float) $payer->total / $total * 100 : 0 }}%"></i></div></div>@empty<div class="info-empty">Add expenses to see each family member’s contribution.</div>@endforelse
            </article>
        </section>

        <section class="activity-card dashboard-recent"><div class="activity-heading"><div><h2>Recent expenses</h2><p>Latest household transactions</p></div><a class="text-link" href="{{ route('expenses.index') }}">All expenses <span aria-hidden="true">→</span></a></div>
            @if($recentExpenses->isEmpty())<div class="empty-state"><div class="empty-icon">৳</div><strong>No expenses recorded yet</strong><p>Start tracking your household spending.</p><a class="add-transaction" href="{{ route('expenses.create') }}">＋ Add first expense</a></div>@else<div class="expense-list">@foreach($recentExpenses as $expense)<div class="expense-row"><span class="category-dot" style="--category-color:{{ $expense->category->color }}"></span><div class="expense-description"><strong>{{ $expense->notes ?: $expense->category->name }}</strong><small>{{ $expense->category->name }} · {{ $expense->spent_on->format('M j') }} · {{ $expense->paid_by }}</small></div><strong class="expense-amount">৳ {{ number_format($expense->net_amount, 2) }}</strong></div>@endforeach</div>@endif
        </section>

        <section class="dashboard-charts" aria-label="Expense charts">
            <article class="info-card dashboard-chart-card"><div class="info-card-heading"><div><h3>Daily expense</h3><p>Net spending each day in {{ $month->format('F Y') }}</p></div><span class="info-period">{{ $month->format('M Y') }}</span></div>{!! $dailyExpenseChart->renderHtml() !!}</article>
            <article class="info-card dashboard-chart-card"><div class="info-card-heading"><div><h3>Monthly expense</h3><p>Net spending by month in {{ $month->format('Y') }}</p></div><span class="info-period">{{ $month->format('Y') }}</span></div>{!! $monthlyExpenseChart->renderHtml() !!}</article>
        </section>

        <footer class="dashboard-footer"><span>Family finances, a little clearer.</span><span>AMADER HISAB · HOUSEHOLD</span></footer>
    @endsection

@section('javascript')
    {!! $dailyExpenseChart->renderChartJsLibrary() !!}
    {!! $dailyExpenseChart->renderJs() !!}
    {!! $monthlyExpenseChart->renderJs() !!}
@endsection
