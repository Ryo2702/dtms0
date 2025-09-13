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
    <div class="drawer lg:drawer-open ">
        <x-sidebar />
        @php
            $user = Auth::user();
            $isAdmin = $user->type === 'Admin';
            $logo =
                !$isAdmin && $user->department && $user->department->logo
                    ? Storage::url($user->department->logo)
                    : null;
            $currentRoute = request()->route()->getName();
        @endphp
        <!-- Page Content -->
        <div class="drawer-content flex flex-col">
            <div class="bg-primary text-white navbar">
                <div class="navbar-start flex">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="Logo"
                            class="w-12 h-12 lg:w-13 lg:h-13 rounded-full object-cover">
                    @endif

                    <h1 class="ml-2 lg:ml-4 text-xxl lg:text-2xl">
                        @if ($isAdmin)
                            <span class="hidden sm:inline">System Administrator</span>
                            <span class="sm:hidden">Admin</span>
                        @else
                            <span class="hidden md:inline">{{ $user->department->name ?? 'Municipal System' }}</span>
                            <span class="md:hidden">{{ Str::limit($user->department->name ?? 'Municipal', 10) }}</span>
                        @endif
                    </h1>
                </div>
            </div>


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
