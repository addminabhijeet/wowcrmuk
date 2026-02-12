<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <!-- Standard meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Page title -->
    <title>{{ isset($title) ? $title : config('app.name') }}</title>

     <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">

    <!-- Styles and other head includes -->
    <x-head />
</head>

<body>

    <!-- Sidebar -->
    <x-sidebar />

    <main class="dashboard-main">

        <!-- Navbar -->
        <x-navbar />

        <div class="dashboard-main-body">

            <!-- Breadcrumb -->
            <x-breadcrumb
                title="{{ isset($title) ? $title : '' }}"
                subTitle="{{ isset($subTitle) ? $subTitle : '' }}"
            />

            <!-- Main content -->
            @yield('content')

        </div>

        <!-- Footer -->
        <x-footer />

    </main>

    <!-- Scripts -->
    <x-script script='{!! isset($script) ? $script : "" !!}' />

</body>

</html>
