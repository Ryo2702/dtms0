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
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-opacity-50 z-40 lg:hidden hidden"></div>

    <!-- Main Layout Container -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="mobile-sidebar"
            class="fixed left-0 top-0 h-screen bg-primary text-white w-64 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:flex-shrink-0">
            <x-sidebar />
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            @php
                $user = Auth::user();
                $isAdmin = $user->type === 'Admin';
                $logo =
                    !$isAdmin && $user->department && $user->department->logo
                        ? Storage::url($user->department->logo)
                        : null;
                $currentRoute = request()->route()->getName();
            @endphp

            <!-- Top Navigation Bar -->
            <nav class="bg-primary text-white navbar flex-shrink-0 z-30">
                <div class="navbar-start flex items-center">
                    <!-- Mobile Hamburger Menu Button -->
                    <button id="hamburger-btn" class="btn btn-ghost btn-square lg:hidden mr-2" aria-label="Open menu">
                        <i data-lucide="menu" class="h-6 w-6 text-white"></i>
                    </button>

                    <!-- Department Logo -->
                    @if ($logo)
                        <img src="{{ $logo }}" alt="Department Logo"
                            class="w-12 h-12 lg:w-13 lg:h-13 rounded-full object-cover">
                    @endif

                    <!-- Page Title -->
                    <h1 class="ml-2 lg:ml-4 text-xl lg:text-2xl font-semibold">
                        @if ($isAdmin)
                            <span class="hidden sm:inline">System Administrator</span>
                            <span class="sm:hidden">Admin</span>
                        @else
                            <span class="hidden md:inline">{{ $user->department->name ?? 'Municipal System' }}</span>
                            <span class="md:hidden">{{ Str::limit($user->department->name ?? 'Municipal', 10) }}</span>
                        @endif
                    </h1>
                </div>
            </nav>

            {{-- Flash Messages --}}
            <x-toast :message="session('success')" type="success" title="Success" :timeout="5000" position="top-right" />
            <x-toast :message="session('error')" type="error" title="Error" :timeout="6000" position="top-right" />
            <x-toast :messages="$errors->all()" type="warning" title="Validation Failed" :timeout="8000"
                position="top-right" />

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-dtms-bg">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get DOM elements
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const closeBtn = document.getElementById('close-btn');
            const overlay = document.getElementById('sidebar-overlay');
            const sidebar = document.getElementById('mobile-sidebar');
            const sidebarLinks = document.querySelectorAll('#mobile-sidebar a');

            // Debug logging
            console.log('Sidebar elements initialized:', {
                hamburgerBtn: !!hamburgerBtn,
                closeBtn: !!closeBtn,
                overlay: !!overlay,
                sidebar: !!sidebar,
                linksCount: sidebarLinks.length
            });

            // Sidebar control functions
            function openSidebar() {
                if (sidebar && overlay) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeSidebar() {
                if (sidebar && overlay) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }

            // Event listeners
            hamburgerBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                openSidebar();
            });

            closeBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                closeSidebar();
            });

            overlay?.addEventListener('click', (e) => {
                e.preventDefault();
                closeSidebar();
            });

            // Close sidebar when clicking navigation links on mobile
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 1024) {
                        setTimeout(closeSidebar, 100);
                    }
                });
            });

            // Keyboard and resize event handlers
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && window.innerWidth < 1024) {
                    closeSidebar();
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    closeSidebar();
                }
            });

            // Initialize sidebar state
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    </script>
</body>

</html>
