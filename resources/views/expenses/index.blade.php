@extends('layouts.authenticated')
@section('title', 'Expenses')
@section('page-content')

        <header class="dashboard-header"><div><span class="eyebrow login-eyebrow">FAMILY FINANCES</span><h1>Expenses</h1><p>Search and review household expenses.</p></div><a class="primary-action" href="{{ route('expenses.create') }}">＋ Add expense</a></header>
        @if(session('status'))<div hidden data-swal-toast="success">{{ session('status') }}</div>@endif
        <section class="expense-filter-card">
            <form method="GET" action="{{ route('expenses.index') }}" class="expense-filter-form">
                <div class="expense-search-field"><label for="expense-search">Search</label><input id="expense-search" class="text-input" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Description, category, or payer"></div>
                <div class="form-field"><label for="filter-category">Category</label><select id="filter-category" class="text-input" name="expense_category_id"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['expense_category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="form-field"><label for="filter-payer">Paid by</label><select id="filter-payer" class="text-input" name="paid_by"><option value="">Everyone</option>@foreach($payers as $payer)<option value="{{ $payer }}" @selected(($filters['paid_by'] ?? '') === $payer)>{{ $payer }}</option>@endforeach</select></div>
                <div class="form-field"><label for="filter-from">From date</label><input id="filter-from" class="text-input" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div class="form-field"><label for="filter-to">To date</label><input id="filter-to" class="text-input" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
                <div class="form-field"><label for="per-page">Records per page</label><select id="per-page" class="text-input" name="per_page"><option value="5" @selected($perPage === 5)>5</option><option value="10" @selected($perPage === 10)>10</option><option value="25" @selected($perPage === 25)>25</option><option value="50" @selected($perPage === 50)>50</option></select></div>
                <div class="filter-actions"><button class="primary-action" type="submit">Apply</button><a class="clear-filters" href="{{ route('expenses.index') }}">Clear</a></div>
            </form>
        </section>
        <section class="activity-card expense-table-card">
            <div class="activity-heading"><div><h2>All expenses</h2><p>{{ $expenses->total() }} {{ \Illuminate\Support\Str::plural('result', $expenses->total()) }}</p></div><span class="activity-count">{{ $expenses->total() }} records</span></div>
            @if($expenses->isEmpty())
                <div class="empty-state"><div class="empty-icon">৳</div><strong>{{ $hasFilters ? 'No matching expenses' : 'No expenses yet' }}</strong><p>{{ $hasFilters ? 'Try changing or clearing your filters.' : 'Add your first family expense to get started.' }}</p>@if($hasFilters)<a class="add-transaction" href="{{ route('expenses.index') }}">Clear filters</a>@else<a class="add-transaction" href="{{ route('expenses.create') }}">＋ Add expense</a>@endif</div>
            @else
                <div class="table-scroll"><table class="expense-table"><thead><tr><th>DESCRIPTION</th><th>CATEGORY</th><th>PAID BY</th><th>DATE</th><th class="align-right">NET EXPENSE</th><th></th></tr></thead><tbody>
                    @foreach($expenses as $expense)<tr><td><strong>{{ $expense->notes ?: $expense->category->name }}</strong></td><td><span class="category-label"><i style="--category-color:{{ $expense->category->color }}"></i>{{ $expense->category->name }}</span></td><td><span class="payer-chip">{{ $expense->paid_by }}</span></td><td>{{ $expense->spent_on->format('M j, Y') }}</td><td class="align-right"><strong>৳ {{ number_format($expense->net_amount, 2) }}</strong><small>Given {{ number_format($expense->amount, 2) }} · Returned {{ number_format($expense->returned_amount, 2) }}</small></td><td class="expense-actions"><a class="edit-expense-link" href="{{ route('expenses.edit', $expense) }}" aria-label="Edit expense">Edit</a><form method="POST" action="{{ route('expenses.destroy', $expense) }}" data-swal-confirm="Delete expense" data-swal-title="Delete this expense?" data-swal-text="This expense will be permanently removed." data-swal-icon="warning">@csrf @method('DELETE')<button class="delete-button" type="submit" aria-label="Delete this expense">×</button></form></td></tr>@endforeach
                </tbody></table></div>
                @php
                    $startPage = max(1, $expenses->currentPage() - 2);
                    $endPage = min($expenses->lastPage(), $expenses->currentPage() + 2);
                @endphp
                <div class="pagination-wrap">
                    <span class="pagination-summary">Showing {{ $expenses->firstItem() }}–{{ $expenses->lastItem() }} of {{ $expenses->total() }}</span>
                    <nav class="simple-pagination" aria-label="Expense pages">
                        <a class="page-control {{ $expenses->onFirstPage() ? 'disabled' : '' }}" href="{{ $expenses->previousPageUrl() ?: '#' }}" @if($expenses->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>← <span>Previous</span></a>
                        @if($startPage > 1)
                            <a class="page-number" href="{{ $expenses->url(1) }}">1</a>
                            @if($startPage > 2)<span class="page-ellipsis">…</span>@endif
                        @endif
                        @foreach(range($startPage, max($startPage, $endPage)) as $page)<a class="page-number {{ $page === $expenses->currentPage() ? 'current' : '' }}" href="{{ $expenses->url($page) }}" @if($page === $expenses->currentPage()) aria-current="page" @endif>{{ $page }}</a>@endforeach
                        @if($endPage < $expenses->lastPage())
                            @if($endPage < $expenses->lastPage() - 1)<span class="page-ellipsis">…</span>@endif
                            <a class="page-number" href="{{ $expenses->url($expenses->lastPage()) }}">{{ $expenses->lastPage() }}</a>
                        @endif
                        <a class="page-control {{ $expenses->hasMorePages() ? '' : 'disabled' }}" href="{{ $expenses->nextPageUrl() ?: '#' }}" @unless($expenses->hasMorePages()) aria-disabled="true" tabindex="-1" @endunless><span>Next</span> →</a>
                    </nav>
                </div>
            @endif
        </section>
    @endsection