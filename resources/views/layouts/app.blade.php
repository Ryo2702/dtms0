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

        <!-- Sidebar -->
        <div class="drawer-side">
            <label for="sidebar" class="drawer-overlay"></label>

            @auth
                <aside class="bg-dtms-primary text-white w-64 min-h-screen">
                    <div class="p-4 text-xl font-bold border-b border-dtms-secondary flex items-center space-x-2">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-12 h-12 rounded-full">
                        <span class="ml-4">Municipal System</span>
                    </div>

                    <ul class="menu p-4 text-base">
                        <li class="mb-3">
                            <a href="{{ route('dashboard') }}" class="hover:bg-dtms-secondary">Dashboard</a>
                        </li>

                        @role('Admin')
                            <li class="mb-3"><a href="{{ route('admin.users.index') }}" class="hover:bg-dtms-secondary">User
                                    Management</a></li>
                            <li class="mb-3"><a href="{{ route('admin.users.archives') }}"
                                    class="hover:bg-dtms-secondary">Archives</a></li>
                            <li class="mb-3"><a href="{{ route('admin.departments.index') }}"
                                    class="hover:bg-dtms-secondary">Department</a></li>
                            <li class="mb-3"><a href="{{ route('documents.index') }}"
                                    class="hover:bg-dtms-secondary">Documents</a></li>
                        @endrole

                        @role('Staff')
                            <li><a href="/staff-area" class="hover:bg-dtms-secondary">Staff Area</a></li>
                        @endrole

                        @role('Officer')
                            <li><a href="/officer-area" class="hover:bg-dtms-secondary">Officer Area</a></li>
                        @endrole

                        <li class="p-4 border-t border-dtms-secondary">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-logout w-full">Logout</button>
                            </form>
                        </li>
                    </ul>
                </aside>
            @endauth
        </div>

        <!-- Page Content -->
        <div class="drawer-content flex flex-col">
            <main class="p-6 bg-dtms-bg min-h-screen">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
