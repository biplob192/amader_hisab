@extends('layouts.authenticated')
@section('title', 'Add expense')
@section('page-content')

        <header class="dashboard-header"><div><span class="eyebrow login-eyebrow">FAMILY FINANCES</span><h1>Add an expense</h1><p>Record a purchase for your household.</p></div><a class="text-link" href="{{ route('expenses.index') }}">← All expenses</a></header>
        <section class="form-card"><form method="POST" action="{{ route('expenses.store') }}" class="expense-form">@csrf
            <div class="form-grid">
                <div class="form-field"><label for="amount">Amount given (BDT)</label><input class="text-input" id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" placeholder="0.00" required>@error('amount')<span class="field-error">{{ $message }}</span>@enderror</div>
                <div class="form-field"><label for="returned_amount">Amount returned (BDT)</label><input class="text-input" id="returned_amount" name="returned_amount" type="number" min="0" step="0.01" value="{{ old('returned_amount', '0.00') }}" placeholder="0.00" required><small class="input-help">Net expense = amount given − amount returned</small>@error('returned_amount')<span class="field-error">{{ $message }}</span>@enderror</div>
            </div>
            <div class="form-grid">
                <div class="form-field"><label for="spent_on">Date</label><input class="text-input" id="spent_on" name="spent_on" type="date" max="{{ now()->toDateString() }}" value="{{ old('spent_on', now()->toDateString()) }}" required>@error('spent_on')<span class="field-error">{{ $message }}</span>@enderror</div>
                <div class="form-field"><label for="expense_category_id">Category</label><select class="text-input" id="expense_category_id" name="expense_category_id" required><option value="">Choose a category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('expense_category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>@error('expense_category_id')<span class="field-error">{{ $message }}</span>@enderror</div>
            </div>
            <div class="form-field"><label for="paid_by">Paid by</label><select class="text-input" id="paid_by" name="paid_by" required><option value="">Who paid?</option>@foreach($payers as $payer)<option value="{{ $payer }}" @selected(old('paid_by') === $payer)>{{ $payer }}</option>@endforeach</select>@error('paid_by')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="form-field"><label for="notes">Description <span class="optional-label">OPTIONAL</span></label><textarea class="text-input notes-input" id="notes" name="notes" rows="3" maxlength="2000" placeholder="e.g. Groceries for the week">{{ old('notes') }}</textarea>@error('notes')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="form-actions"><a class="cancel-action" href="{{ route('expenses.index') }}">Cancel</a><button class="primary-action" type="submit">Save expense <span>→</span></button></div>
        </form></section>
    @endsection