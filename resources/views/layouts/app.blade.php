<!DOCTYPE html>
<html lang="en" data-theme="dtms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Municipal System')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-dtms-bg text-dtms-text">
    <div class="drawer lg:drawer-open">
        <x-sidebar />
        <!-- Page Content -->
        <div class="drawer-content flex flex-col">
            {{-- Flash success --}}
            <x-toast :message="session('success')" type="success" title="Success" :timeout="5000" position="top-right" />

            {{-- Flash error --}}
            <x-toast :message="session('error')" type="error" title="Error" :timeout="6000" position="top-right" />

            {{-- Validation errors (multiple) --}}
            <x-toast :messages="$errors->all()" type="warning" title="Validation Failed" :timeout="8000"
                position="top-right" />

            <main class="p-6 bg-dtms-bg min-h-screen">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
