@extends('layouts.authenticated')
@section('title', 'Expense details')
@section('page-content')
    <header class="dashboard-header">
        <div><span class="eyebrow login-eyebrow">FAMILY FINANCES</span><h1>Expense details</h1><p>Review the full record for this household expense.</p></div>
        <div class="header-actions"><a class="text-link" href="{{ route('expenses.index') }}">← All expenses</a><a class="primary-action" href="{{ route('expenses.edit', $expense) }}">Edit expense</a></div>
    </header>

    <section class="expense-detail-card">
        <div class="expense-detail-header">
            <div>
                <span class="expense-detail-kicker">{{ $expense->category->name }}</span>
                <h2>{{ $expense->notes ?: 'Expense' }}</h2>
                <p>Recorded {{ $expense->created_at->format('M j, Y') }}</p>
            </div>
            <div class="expense-detail-total"><span>NET EXPENSE</span><strong>৳ {{ number_format($expense->net_amount, 2) }}</strong></div>
        </div>

        <div class="expense-detail-grid">
            <div class="expense-detail-item"><span>Paid by</span><strong>{{ $expense->paid_by }}</strong></div>
            <div class="expense-detail-item"><span>Date</span><strong>{{ $expense->spent_on->format('F j, Y') }}</strong></div>
            <div class="expense-detail-item"><span>Amount given</span><strong>৳ {{ number_format($expense->amount, 2) }}</strong></div>
            <div class="expense-detail-item"><span>Amount returned</span><strong>৳ {{ number_format($expense->returned_amount, 2) }}</strong></div>
        </div>

        @if (filled($expense->notes))
            <div class="expense-detail-section"><h3>Notes</h3><p>{{ $expense->notes }}</p></div>
        @endif
        <div class="expense-detail-section expense-detail-description"><h3>Description</h3><p>{{ $expense->description }}</p></div>
    </section>
@endsection
