<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Mistica POS customer and user accounts.">
    <title>@yield('title', 'Home') | Mistica POS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">M</span><span>Mistica POS<small>ACCOUNT WORKSPACE</small></span></a>
            <nav aria-label="Main navigation">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
                <a href="{{ route('customers.index') }}" @if(request()->routeIs('customers.*')) aria-current="page" @endif>Customers</a>
                <a href="{{ route('users.index') }}" @if(request()->routeIs('users.*')) aria-current="page" @endif>Users</a>
                <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>About</a>
            </nav>
        </div>
    </header>
    <main id="main-content" class="container">
        @yield('content')
    </main>
    <footer class="site-footer container"><span>Mistica POS</span><span>&copy; {{ now()->year }} Joseph Gian Carlo Mistica</span></footer>
</body>
</html>
