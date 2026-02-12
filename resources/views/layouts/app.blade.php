<!DOCTYPE html>
<html>
<head>
    <title>Job Portal</title>
</head>
<body>
    @auth
        <div style="padding:10px;background:#eee;">
            Logged in as {{ auth()->user()->name }} ({{ auth()->user()->role }})
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    @endauth

    <div>
        @yield('content')
    </div>
</body>
</html>
