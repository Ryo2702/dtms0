<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite(['resources/js/app.js'])
</head>

<body class="min-h-screen bg-base-200">
    <div class="drawer lg:drawer-open">
        <input id="sidebar" type="checkbox" class="drawer-toggle" />

        <!-- Sidebar -->
        <div class="drawer-side">
            <label for="sidebar" class="drawer-overlay"></label>
            <aside class="bg-blue-900 text-white w-64 min-h-screen">
                <div class="p-4 text-xl font-bold border-b border-blue-700 flex items-center space-x-2">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-12 h-12 rounded-full">
                    <span class="ml-4">Municipal System</span>
                </div>
                @auth


                    <ul class="menu p-4 text-base">
                        <li class="mb-3"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        @role('Admin')
                            <li class="mb-3"><a href="{{ route('admin.users.index') }}" class="hover:bg-blue-800">User
                                    Management</a></li>
                            <li class="mb-3"><a href="{{ route('admin.users.archives') }}"
                                    class="hover:bg-blue-800">Archives</a></li>
                        @endrole

                        @role('Staff')
                            <li><a href="/staff-area" class="hover:bg-blue-800">Staff Area</a></li>
                        @endrole

                        @role('Officer')
                            <li><a href="/officer-area" class="hover:bg-blue-800">Officer Area</a></li>
                        @endrole

                        <li class="p-4 border-t">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-error w-full">Logout</button>
                            </form>
                        </li>
                    </ul>
                </aside>
            </div>

            <!-- Page Content -->
            <div class="drawer-content flex flex-col">
                <!-- Navbar for small screens -->
                <div class="w-full navbar bg-base-100 lg:hidden">
                    <div class="flex-none">
                        <label for="sidebar" class="btn btn-square btn-ghost">
                            <!-- Menu Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </label>
                    </div>
                    <div class="flex-1">
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost normal-case text-xl">Municipal System</a>
                    </div>
                </div>
            @endauth

            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
