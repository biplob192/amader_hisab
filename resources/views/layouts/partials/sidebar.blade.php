<aside class="sidebar">
    <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">a.</span><span>amader<span class="brand-weight">hisab</span></span></a>
    <div class="sidebar-label">YOUR HOUSEHOLD</div>
    <nav class="side-nav" aria-label="Main navigation">
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span aria-hidden="true">&#9638;</span> Overview</a>
        <a class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}"><span aria-hidden="true">&#8597;</span> Expenses</a>
        <a class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><span aria-hidden="true">&#8803;</span> Reports</a>
        <a class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><span aria-hidden="true">&#10064;</span> Categories</a>
    </nav>
    <div class="sidebar-bottom">
        <div class="profile-row">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit" aria-label="Sign out">&#8599;</button></form>
        </div>
    </div>
</aside>
