@extends('layouts.authenticated')
@section('title', 'Categories')
@section('page-content')
<header class="dashboard-header"><div><span class="eyebrow login-eyebrow">FAMILY FINANCES</span><h1>Categories</h1><p>Every category available for household expenses.</p></div><a class="primary-action" href="{{ route('categories.create') }}">＋ Add category</a></header>
@if(session('status'))<div hidden data-swal-toast="success">{{ session('status') }}</div>@endif
<section class="activity-card expense-table-card">
    <div class="activity-heading"><div><h2>All categories</h2><p>Your net spending in each category</p></div><span class="activity-count">৳ {{ number_format($totalSpend, 2) }}</span></div>
    @if($categories->isEmpty())
        <div class="empty-state"><div class="empty-icon">❐</div><strong>No categories yet</strong><p>Default categories will appear after setup.</p><a class="add-transaction" href="{{ route('categories.create') }}">＋ Add category</a></div>
    @else
        <div class="table-scroll"><table class="expense-table"><thead><tr><th>CATEGORY</th><th>EXPENSES</th><th>SHARE</th><th class="align-right">NET SPEND</th><th></th></tr></thead><tbody>
            @foreach($categories as $row)<tr><td><span class="category-label"><i style="--category-color:{{ $row['color'] }}"></i>{{ $row['name'] }}</span></td><td>{{ $row['count'] }} {{ \Illuminate\Support\Str::plural('expense', $row['count']) }}</td><td>{{ number_format($row['share'], 1) }}%</td><td class="align-right"><strong>৳ {{ number_format($row['total'], 2) }}</strong></td><td class="expense-actions"><a class="edit-expense-link" href="{{ route('categories.edit', $row['id']) }}" aria-label="Edit category">Edit</a></td></tr>@endforeach
        </tbody></table></div>
        <div class="pagination-wrap"><span class="pagination-summary">Showing {{ $expenseCount }} {{ \Illuminate\Support\Str::plural('expense', $expenseCount) }} across {{ $categories->count() }} {{ \Illuminate\Support\Str::plural('category', $categories->count()) }}</span></div>
    @endif
</section>
@endsection
