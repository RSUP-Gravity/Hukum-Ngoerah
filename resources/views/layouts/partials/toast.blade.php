<!-- Toast Notifications Container -->
<div 
    x-data="toast()"
    @toast.window="add($event.detail.message, $event.detail.type || 'info', $event.detail.duration || 5000)"
    class="fixed bottom-4 right-4 z-[70] flex flex-col gap-2"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div 
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="glass-card-static p-4 min-w-[300px] max-w-md flex items-start gap-3"
        >
            <!-- Icon based on type -->
            <div 
                class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                :class="{
                    'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400': notification.type === 'success',
                    'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400': notification.type === 'info',
                    'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400': notification.type === 'warning',
                    'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400': notification.type === 'error'
                }"
            >
                <!-- Success Icon -->
                <svg x-show="notification.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <!-- Info Icon -->
                <svg x-show="notification.type === 'info'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <!-- Warning Icon -->
                <svg x-show="notification.type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <!-- Error Icon -->
                <svg x-show="notification.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            <!-- Message -->
            <div class="flex-1">
                <p class="text-sm font-medium text-[var(--text-primary)]" x-text="notification.message"></p>
            </div>

            <!-- Close Button -->
            <button 
                @click="remove(notification.id)"
                class="flex-shrink-0 p-1 rounded text-[var(--text-tertiary)] hover:text-[var(--text-primary)] hover:bg-[var(--surface-glass)] transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </template>
</div>
