@extends('layouts.authenticated')
@section('title', 'Reports')
@section('page-content')
    <header class="dashboard-header">
        <div><span class="eyebrow login-eyebrow">FAMILY FINANCES</span>
            <h1>Spending reports</h1>
            <p>Compare categories and spot changes over time.</p>
        </div>
        <form method="GET" action="{{ route('reports.index') }}" class="month-form"><label for="month">Report month</label><input id="month" type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()"></form>
    </header>
    <section class="report-summary">
        <div><span class="banner-eyebrow">TOTAL SPENDING · {{ strtoupper($month->format('F Y')) }}</span><strong>৳ {{ number_format($total, 2) }}</strong><small>Previous month: ৳ {{ number_format($previousTotal, 2) }}</small></div>
        <div><span class="banner-eyebrow">HIGHEST CATEGORY</span><strong class="report-top-category">{{ $highest && $highest->amount > 0 ? $highest->category->name : 'No spending yet' }}</strong><small>{{ $highest && $highest->amount > 0 ? '৳ ' . number_format($highest->amount, 2) . ' this month' : 'Add expenses to compare' }}</small></div>
    </section>
    <section class="activity-card report-card">
        <div class="activity-heading">
            <div>
                <h2>Category comparison</h2>
                <p>This month compared with the previous month</p>
            </div><span class="activity-count">{{ $month->format('M Y') }} vs {{ $month->copy()->subMonth()->format('M Y') }}</span>
        </div>@php($maxValue = max(1, (float) $categories->max('amount'), (float) $categories->max('previous')))<div class="category-report-list">
            @forelse($categories as $row)
                <div class="category-report-row">
                    <div class="category-report-heading"><span class="category-label"><i style="--category-color:{{ $row->category->color }}"></i>{{ $row->category->name }}</span><strong>৳ {{ number_format($row->amount, 2) }}</strong></div>
                    <div class="comparison-track"><i class="comparison-current" style="--bar-width:{{ ($row->amount / $maxValue) * 100 }}%;--category-color:{{ $row->category->color }}"></i></div>
                    <div class="category-report-meta"><span>Previous: ৳ {{ number_format($row->previous, 2) }}</span>
                        @if ($row->change !== null)
                            <span class="{{ $row->change > 0 ? 'trend-up' : ($row->change < 0 ? 'trend-down' : '') }}">{{ $row->change > 0 ? '↑' : ($row->change < 0 ? '↓' : '→') }} {{ number_format(abs($row->change), 1) }}% {{ $row->change > 0 ? 'more' : ($row->change < 0 ? 'less' : 'unchanged') }}</span>
                        @elseif($row->amount > 0)
                        <span class="trend-new">New this month</span>@else<span>No expenses</span>
                        @endif
                    </div>
            </div>@empty<div class="empty-state"><strong>No categories yet</strong>
                    <p>Default categories will appear after setup.</p>
                </div>
            @endforelse
        </div>
    </section>
    <section class="activity-card payer-report">
        <div class="activity-heading">
            <div>
                <h2>Daily spending</h2>
                <p>Choose a date to view its expenses for {{ $month->format('F') }}</p>
            </div>
        </div>
        <div class="daily-report">
            @forelse($daily as $day)@php($date = \Illuminate\Support\Carbon::parse($day->day))
                <details class="daily-day">
                    <summary class="daily-row"><time>{{ $date->format('M j') }}</time>
                        <div class="comparison-track"><i class="comparison-current" style="--bar-width:{{ ((float) $day->total / max(1, (float) $daily->max('total'))) * 100 }}%;--category-color:#568064"></i></div><strong>৳ {{ number_format($day->total, 2) }}</strong>
                    </summary>
                    <div class="daily-expenses">
                        @foreach ($dailyExpenses->get($date->toDateString(), collect()) as $expense)
                            <div class="daily-expense"><span class="category-label"><i style="--category-color:{{ $expense->category->color }}"></i>{{ $expense->category->name }}</span><span class="daily-expense-description">{{ $expense->notes ?: 'No notes' }}</span><span class="payer-chip">{{ $expense->paid_by }}</span><strong>৳ {{ number_format($expense->net_amount, 2) }}</strong></div>
                        @endforeach
                    </div>
            </details>@empty<div class="payer-empty">Daily spending will appear when you record expenses.</div>
            @endforelse
        </div>
</section>@endsection
