<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dtms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

                 <!-- Center Search Bar -->
                <div class="navbar-center">
                    <div class="relative w-full max-w-sm">
                        <form id="document-search-form" class="flex">
                            <div class="relative flex-1">
                                <input type="text" 
                                       id="document-search" 
                                       name="document_id"
                                       placeholder="Search Document ID..." 
                                       class="w-full px-4 py-2 pl-10 pr-12 text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
                                </div>
                            </div>
                            <button type="submit" 
                                    class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
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
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const closeBtn = document.getElementById('close-btn');
            const overlay = document.getElementById('sidebar-overlay');
            const sidebar = document.getElementById('mobile-sidebar');
            const sidebarLinks = document.querySelectorAll('#mobile-sidebar a');


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

            if (window.innerWidth < 1024) {
                closeSidebar();
            }

              const searchForm = document.getElementById('document-search-form');
            const searchInput = document.getElementById('document-search');

            searchForm?.addEventListener('submit', function(e) {
                e.preventDefault();
                const inputValue = searchInput.value.trim();
                
                if (inputValue) {
                    let documentId = inputValue;
                    
                    if (inputValue.includes('http') || inputValue.includes('/document/')) {
                        // Extract document ID from URL
                        const urlMatch = inputValue.match(/\/document\/([^\/\?#]+)/);
                        if (urlMatch) {
                            documentId = urlMatch[1];
                        } else {
                        
                            const parts = inputValue.split('/');
                            const lastPart = parts[parts.length - 1];
                           
                            if (lastPart && (lastPart.match(/^[A-Z]{2,}-\d{8}-[A-Z0-9]+$/i) || lastPart.length > 5)) {
                                documentId = lastPart;
                            } else {
                                alert('Could not extract document ID from URL. Please check the format.');
                                return;
                            }
                        }
                    }
                    
                    // Validate document ID format (optional but recommended)
                    if (documentId && documentId.length > 2) {
                        // Redirect to document review page with the extracted document ID
                        window.location.href = `/document/${encodeURIComponent(documentId)}`;
                    } else {
                        alert('Please enter a valid document ID or URL');
                    }
                } else {
                    alert('Please enter a document ID or paste a URL to search');
                }
            });
        });
    </script>
</body>

</html>
