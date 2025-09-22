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
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-opacity-50 lg:hidden"></div>

    <!-- Main Layout Container -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="mobile-sidebar"
            class="fixed top-0 left-0 z-50 w-64 h-screen text-white transition-transform duration-300 ease-in-out transform -translate-x-full bg-primary lg:translate-x-0 lg:static lg:flex-shrink-0">
            <x-sidebar />
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-hidden">
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
            <nav class="z-30 flex-shrink-0 text-white bg-primary navbar">
                <div class="flex items-center navbar-start">
                    <!-- Mobile Hamburger Menu Button - Hidden on desktop -->
                    <button id="hamburger-btn" class="mr-2 btn btn-ghost btn-square lg:!hidden" aria-label="Open menu">
                        <i data-lucide="menu" class="w-6 h-6 text-white"></i>
                    </button>

                    @if ($logo)
                        <img src="{{ $logo }}" alt="Department Logo"
                            class="object-cover w-12 h-12 rounded-full lg:w-13 lg:h-13">
                    @endif

                    <h1 class="ml-2 text-xl font-semibold lg:ml-4 lg:text-2xl">
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
            <main class="flex-1 p-6 overflow-y-auto bg-dtms-bg">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
