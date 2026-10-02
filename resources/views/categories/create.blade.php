@extends('layouts.authenticated')
@section('title', 'Add category')
@section('page-content')

        <header class="dashboard-header"><div><span class="eyebrow login-eyebrow">FAMILY FINANCES</span><h1>Add a category</h1><p>Create a new category for household expenses.</p></div><a class="text-link" href="{{ route('categories.index') }}">← All categories</a></header>
        <section class="form-card"><form method="POST" action="{{ route('categories.store') }}" class="expense-form">@csrf
            <div class="form-field"><label for="name">Name</label><input class="text-input" id="name" name="name" type="text" maxlength="80" value="{{ old('name') }}" placeholder="e.g. House Repair" required>@error('name')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="form-field"><label for="color">Colour</label><input class="text-input" id="color" name="color" type="color" value="{{ old('color', '#5f8c70') }}" required><small class="input-help">Used for the category dot in lists and reports.</small>@error('color')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="form-actions"><a class="cancel-action" href="{{ route('categories.index') }}">Cancel</a><button class="primary-action" type="submit">Save category <span>→</span></button></div>
        </form></section>
    @endsection
