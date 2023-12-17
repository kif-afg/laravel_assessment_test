<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... other head elements ... -->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
</head>
<body>
    <div class="navbar">
        @if (session('github_user'))
            <!-- If user is authenticated via GitHub -->
            <a>Welcome, {{ session('github_user')->nickname }}</a>
            <a href="{{ url('/gists') }}" class="btn">View  Gists</a>
            <a href="{{ url('/create_gist') }}" class="btn">Create New Gist</a>
            <a href="{{ url('logout') }}" class="btn">Logout</a>
        @else
            <a href="{{ url('login/github') }}" class="btn">Login with GitHub</a>
        @endif
    </div>

    @yield('content')

</body>
</html>
