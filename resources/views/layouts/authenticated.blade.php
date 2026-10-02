@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    @include('layouts.partials.sidebar')
    <main class="dashboard-main">
        @yield('page-content')
    </main>
</div>
@endsection
