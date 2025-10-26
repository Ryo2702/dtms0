<div class="navbar-end gap-3 z-50">
    <!-- Notification bell -->
    <div class="dropdown dropdown-end">
        <button id="notification-bell" tabindex="0" class="btn btn-ghost btn-circle text-white">
            <div class="indicator">
                <i data-lucide="bell" fill="none" class="h-6 w-6 text-white"></i>
                <span id="notification-badge"
                    class="badge badge-xs badge-error indicator-item hidden"></span>
            </div>
        </button>
        
        <!-- Notification Dropdown -->
        <div tabindex="0" class="dropdown-content z-[100] mt-3 w-80 max-h-96 overflow-y-auto bg-base-100 rounded-box shadow-xl border border-base-300">
            <div class="p-4 border-b border-base-300 bg-base-200">
                <h3 class="font-bold text-lg">Notifications</h3>
                <p class="text-sm opacity-70">Recent updates</p>
            </div>
            
            <div id="notification-list" class="divide-y divide-base-300">
                <div class="p-4 text-center text-sm opacity-50">
                    Loading notifications...
                </div>
            </div>
            
            <div class="p-3 border-t border-base-300 bg-base-200 text-center">
                <button id="mark-all-read" class="btn btn-sm btn-ghost">
                    Mark all as read
                </button>
            </div>
        </div>
    </div>
</div>
