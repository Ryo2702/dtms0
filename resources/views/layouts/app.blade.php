<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dtms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DTMS System')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-dtms-bg text-dtms-text">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-opacity-50 lg:hidden"></div>

    <!-- Main Layout Container -->
    <nav class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="mobile-sidebar"
            class="fixed top-0 left-0 z-50 w-64 h-screen text-white transition-transform duration-300 ease-in-out transform -translate-x-full bg-primary lg:translate-x-0 lg:static lg:shrink-0">
            <x-sidebar />
        </aside>


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


            <div class="navbar flex bg-primary text-white justify-between">
                <div class="navbar-start flex items-center justify-center">
                    <h1 class="text-3xl flex font-bold ml-2">
                        <div class="mr-3">
                            @if ($logo)
                                <img src="{{ $logo }}" alt="Department Logo" class="w-15 h-15 rounded-full lg:ml-0">
                            @endif
                        </div>

                        <div class="items-center">
                            @if ($isAdmin)
                                <span class="hidden sm:inline">System Administrator</span>
                                <span class="sm:hidden">Admin</span>
                            @else
                                <span class="hidden md:inline">{{ $user->department->name ?? 'System Administrator' }}</span>
                                <span class="md:hidden">{{ Str::limit($user->department->name ?? 'System Administrator', 8) }}</span>
                            @endif
                        </div>

                    </h1>
                </div>


                @if ($user->type !== 'Admin')

                    <div class="navbar-center hidden md:flex">
                        <form id="document-search-form" class="join">
                            <input id="document-search" name="document_id" type="text" placeholder="Search Document"
                                class="input input-bordered join-item text-sm" />
                            <button type="submit" class="btn btn-square join-item" aria-label="Search">
                                <i data-lucide="search" class="h-4 w-4"></i>
                            </button>
                        </form>
                    </div>


                    <div class="navbar-end gap-3">
                        <!-- Notification bell -->
                        <div class="relative z-100">
                            <button id="notification-bell" class="relative inline-flex items-center justify-center p-2 text-white hover:bg-blue-700 rounded-full transition-colors">
                                <div class="relative">
                                    <svg data-lucide="bell" fill="none" class="h-6 w-6 text-white"></svg>
                                    <span id="notification-badge"
                                        class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
                                </div>
                            </button>

                            <!-- Notification Dropdown -->
                            <div id="notification-dropdown"
                                class="hidden absolute right-0 mt-3 w-80 max-h-96 overflow-y-auto bg-white text-gray-900 rounded-lg shadow-xl border border-gray-200 z-100">
                                <div class="p-4 border-b border-gray-200 bg-gray-50">
                                    <h3 class="font-bold text-lg text-gray-900">Notifications</h3>
                                    <p class="text-sm text-gray-600">Recent updates</p>
                                </div>

                                <div id="notification-list" class="divide-y divide-gray-200">
                                    <div class="p-4 text-center text-sm text-gray-500">
                                        Loading notifications...
                                    </div>
                                </div>

                                <div class="p-3 border-t border-gray-200 bg-gray-50 text-center">
                                    <button id="mark-all-read" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors">
                                        Mark all as read
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Main Content Area -->
            <main class="flex-1 p-6 overflow-y-auto bg-dtms-bg">
                @yield('content')
            </main>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <x-toast :message="session('success')" type="success" title="Success" :timeout="5000" position="bottom-right" />
    <x-toast :message="session('error')" type="error" title="Error" :timeout="6000" position="bottom-right" />
    <x-toast :messages="$errors->all()" type="warning" title="Validation Failed" :timeout="8000"
        position="bottom-right" />
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile sidebar functionality
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

            // Only bind hamburger for mobile (LG breakpoint)
            hamburgerBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                if (window.innerWidth < 1024) {
                    openSidebar();
                }
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

            // Enhanced search form handling
            const searchForm = document.getElementById('document-search-form');
            const searchInput = document.getElementById('document-search');

            searchForm?.addEventListener('submit', function (e) {
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

            // Notification bell functionality
            const notificationBell = document.getElementById('notification-bell');
            const notificationBadge = document.getElementById('notification-badge');

            @if(in_array($user->type, ['Head']))
                // Update notification count for heads
                function updateNotificationBadge() {
                    fetch('{{ route("notifications.counts") }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const totalCount = data.counts.pending + data.counts.received + data.counts.rejected + data.counts.canceled;
                                if (totalCount > 0) {
                                    notificationBadge.textContent = totalCount > 99 ? '99+' : totalCount;
                                    notificationBadge.classList.remove('hidden');
                                } else {
                                    notificationBadge.classList.add('hidden');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching notification counts:', error);
                        });
                }

                updateNotificationBadge();
                setInterval(updateNotificationBadge, 30000);

                document.addEventListener('visibilitychange', function () {
                    if (!document.hidden) {
                        updateNotificationBadge();
                    }
                });
            @endif
        });

        const notificationBell = document.getElementById('notification-bell');
        const notificationBadge = document.getElementById('notification-badge');
        const notificationList = document.getElementById('notification-list');
        const notificationDropdown = document.getElementById('notification-dropdown');
        const markAllReadBtn = document.getElementById('mark-all-read');

        let notificationData = [];

        // Function to close dropdown
        function closeNotificationDropdown() {
            if (notificationDropdown) {
                notificationDropdown.classList.add('hidden');
            }
        }

        // Function to toggle dropdown
        function toggleNotificationDropdown() {
            if (notificationDropdown) {
                const isHidden = notificationDropdown.classList.contains('hidden');
                if (isHidden) {
                    notificationDropdown.classList.remove('hidden');
                    fetchNotifications(); // Fetch when opening
                } else {
                    notificationDropdown.classList.add('hidden');
                }
            }
        }

        // Click outside to close
        document.addEventListener('click', function (event) {
            if (notificationBell && notificationDropdown) {
                const isClickInsideBell = notificationBell.contains(event.target);
                const isClickInsideDropdown = notificationDropdown.contains(event.target);

                if (!isClickInsideBell && !isClickInsideDropdown) {
                    closeNotificationDropdown();
                }
            }
        });

        // Fetch and display notifications
        function fetchNotifications() {
            fetch('{{ route("notifications.list") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notificationData = data.notifications;
                        renderNotifications(data.notifications);
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                });
        }

        function renderNotifications(notifications) {
            if (!notifications || notifications.length === 0) {
                notificationList.innerHTML = `
                <div class="p-4 text-center text-sm text-gray-500">
                    No notifications
                </div>
            `;
                return;
            }

            notificationList.innerHTML = notifications.map(notification => {
                const unreadClass = !notification.is_read ? 'bg-blue-50' : 'bg-white';
                const iconColor = getNotificationIconColor(notification.type);
                const icon = getNotificationIcon(notification.type);
                const timeAgo = formatTimeAgo(notification.created_at);

                return `
                <div class="notification-item p-4 hover:bg-gray-100 cursor-pointer transition ${unreadClass}" 
                     data-id="${notification.id}" 
                     data-document-id="${notification.document_id}"
                     data-type="${notification.type}">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full ${iconColor} flex items-center justify-center">
                                <svg data-lucide="${icon}" class="h-5 w-5"></svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm text-gray-900">${notification.title}</p>
                            <p class="text-xs text-gray-600 truncate">${notification.message}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-500">${timeAgo}</span>
                                ${!notification.is_read ? '<span class="inline-block px-2 py-0.5 text-xs font-semibold text-white bg-blue-500 rounded">New</span>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            }).join('');

            // Reinitialize lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Add click handlers
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function () {
                    const notificationId = this.dataset.id;
                    const documentId = this.dataset.documentId;
                    const type = this.dataset.type;

                    markAsRead(notificationId);

                    // Close the dropdown
                    closeNotificationDropdown();

                    // Navigate to document
                    if (documentId) {
                        window.location.href = `/documents/reviews/${documentId}`;
                    }
                });
            });
        }

        // Update notification badge
        function updateNotificationBadge(count) {
            if (count > 0) {
                notificationBadge.textContent = count > 99 ? '99+' : count;
                notificationBadge.classList.remove('hidden');
            } else {
                notificationBadge.classList.add('hidden');
            }
        }

        // Mark notification as read
        function markAsRead(notificationId) {
            fetch('{{ route("notifications.mark-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotifications();
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
        }

        // Mark all as read
        markAllReadBtn?.addEventListener('click', function () {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotifications();
                        // Close dropdown after marking all as read
                        setTimeout(() => closeNotificationDropdown(), 300);
                    }
                })
                .catch(error => {
                    console.error('Error marking all as read:', error);
                });
        });

        // Helper functions
        function getNotificationIcon(type) {
            const icons = {
                'approved': 'check-circle',
                'rejected': 'x-circle',
                'canceled': 'ban',
                'pending': 'clock',
                'received': 'inbox',
                'completed': 'check-square',
                'overdue': 'alert-triangle'
            };
            return icons[type] || 'bell';
        }

        function getNotificationIconColor(type) {
            const colors = {
                'approved': 'bg-green-100 text-green-600',
                'rejected': 'bg-red-100 text-red-600',
                'canceled': 'bg-yellow-100 text-yellow-600',
                'pending': 'bg-blue-100 text-blue-600',
                'received': 'bg-indigo-100 text-indigo-600',
                'completed': 'bg-green-100 text-green-600',
                'overdue': 'bg-orange-100 text-orange-600'
            };
            return colors[type] || 'bg-gray-100 text-gray-600';
        }

        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);

            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
            if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
            if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
            return date.toLocaleDateString();
        }

        // Fetch only unread count on load (without showing dropdown)
        function fetchUnreadCount() {
            fetch('{{ route("notifications.list") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => {
                    console.error('Error fetching unread count:', error);
                });
        }

        // Toggle dropdown on bell click
        notificationBell?.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent document click handler
            toggleNotificationDropdown();
        });

        fetchUnreadCount();
        setInterval(fetchUnreadCount, 30000);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                fetchUnreadCount();
            }
        });


    </script>
</body>

</html>