@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<main class="login-shell">
    <section class="login-art" aria-label="Amader Hisab introduction">
        <div class="art-topline"><a class="brand brand-light" href="{{ route('login') }}"><span class="brand-mark">a.</span><span>amader<span class="brand-weight">hisab</span></span></a><span class="art-label">A clearer view of your money</span></div>
        <div class="art-copy"><span class="eyebrow"><span class="eyebrow-dot"></span> MONEY, MADE SIMPLE</span><h1>Make room for<br>what <em>matters.</em></h1><p>A calmer, more confident way to keep track of your finances.</p></div>
        <div class="art-card"><div class="art-card-heading"><span>MONTHLY OVERVIEW</span><span class="card-period">THIS MONTH <span>⌄</span></span></div><div class="chart-total">৳ 48,650 <span class="chart-change">↗ 12.8%</span></div><div class="chart-caption">Your balance is looking good</div><div class="chart" aria-hidden="true"><div class="chart-grid"><i></i><i></i><i></i><i></i></div><svg viewBox="0 0 500 115" preserveAspectRatio="none"><defs><linearGradient id="fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#a8d3c2" stop-opacity=".24"/><stop offset="1" stop-color="#a8d3c2" stop-opacity="0"/></linearGradient></defs><path d="M0 89 C35 83 39 75 72 78 S112 91 144 68 S186 77 215 56 S260 72 286 48 S327 54 357 37 S397 49 425 26 S468 37 500 13 L500 115 L0 115Z" fill="url(#fill)"/><path d="M0 89 C35 83 39 75 72 78 S112 91 144 68 S186 77 215 56 S260 72 286 48 S327 54 357 37 S397 49 425 26 S468 37 500 13" fill="none" stroke="#a8d3c2" stroke-width="2.5" vector-effect="non-scaling-stroke"/></svg></div><div class="chart-months"><span>MAR 01</span><span>MAR 08</span><span>MAR 15</span><span>MAR 22</span><span>MAR 31</span></div><div class="art-card-foot"><span><i class="legend-dot income-dot"></i> Income <strong>৳ 62,400</strong></span><span><i class="legend-dot expense-dot"></i> Expenses <strong>৳ 13,750</strong></span></div></div>
        <div class="art-footer"><span>MADE FOR YOUR EVERYDAY</span><span>© {{ date('Y') }} AMADER HISAB</span></div>
    </section>
    <section class="login-panel"><div class="login-panel-inner"><div class="mobile-brand"><a class="brand" href="{{ route('login') }}"><span class="brand-mark">a.</span><span>amader<span class="brand-weight">hisab</span></span></a></div><div class="login-heading"><span class="eyebrow login-eyebrow">WELCOME BACK</span><h2>Good to see you.</h2><p>Sign in to pick up where you left off.</p></div>
        @if (session('status'))<div hidden data-swal-toast="success">{{ session('status') }}</div>@endif
        <form class="login-form" method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="form-field"><label for="email">Email address</label><div class="input-wrap"><svg viewBox="0 0 20 20" aria-hidden="true"><rect x="2.5" y="4" width="15" height="12" rx="2"/><path d="m3.5 5 6.5 5 6.5-5"/></svg><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="username" required autofocus class="{{ $errors->has('email') ? 'input-error' : '' }}"></div>@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="form-field"><div class="label-row"><label for="password">Password</label></div><div class="input-wrap"><svg viewBox="0 0 20 20" aria-hidden="true"><rect x="3.5" y="8" width="13" height="9" rx="2"/><path d="M6.5 8V6a3.5 3.5 0 0 1 7 0v2"/><circle cx="10" cy="12.5" r="1"/></svg><input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required class="{{ $errors->has('password') ? 'input-error' : '' }}"></div>@error('password')<span class="field-error">{{ $message }}</span>@enderror</div>
            <label class="remember-row"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}><span class="custom-check"></span><span>Keep me signed in</span></label>
            <button class="sign-in-button" type="submit">Sign in <span aria-hidden="true">→</span></button>
        </form>
        <p class="login-note"><span class="secure-icon">✳</span> Your financial information stays private and secure.</p><div class="login-bottom"><span>Need help? <a href="mailto:support@amaderhisab.com">Contact support</a></span><span>EN <span class="language-caret">⌄</span></span></div>
    </div></section>
</main>
@endsection
