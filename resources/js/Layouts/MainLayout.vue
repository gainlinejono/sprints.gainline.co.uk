<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { PageProps, User } from '@/Types';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

const sidebarOpen = ref(true);
const userMenuOpen = ref(false);
const isDark = ref(false);

// Initialize theme from localStorage or system preference
onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        isDark.value = savedTheme === 'dark';
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    updateTheme();
});

// Watch for theme changes
watch(isDark, () => {
    updateTheme();
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
});

const updateTheme = () => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
};

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: 'home' },
    { name: 'Projects', href: '/projects', icon: 'folder' },
];

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950/20">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out',
                'bg-white/80 dark:bg-slate-900/90 backdrop-blur-xl',
                'border-r border-slate-200/60 dark:border-slate-700/50',
                'shadow-[1px_0_30px_rgba(0,0,0,0.03)] dark:shadow-[1px_0_30px_rgba(0,0,0,0.2)]',
                sidebarOpen ? 'w-64' : 'w-20'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-4 border-b border-slate-200/60 dark:border-slate-700/50">
                <Link href="/dashboard" class="flex items-center space-x-3 group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl blur-lg opacity-40 group-hover:opacity-60 transition-opacity"></div>
                        <div class="relative w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/25">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div v-if="sidebarOpen" class="flex flex-col">
                        <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">Sprint</span>
                        <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wider -mt-0.5">Planner</span>
                    </div>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group',
                        $page.url.startsWith(item.href)
                            ? 'bg-gradient-to-r from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 text-indigo-600 dark:text-indigo-400 shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-slate-200'
                    ]"
                >
                    <!-- Home Icon -->
                    <svg v-if="item.icon === 'home'" :class="['w-5 h-5 transition-transform group-hover:scale-110', $page.url.startsWith(item.href) ? '' : 'text-slate-400 dark:text-slate-500']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <!-- Folder Icon -->
                    <svg v-if="item.icon === 'folder'" :class="['w-5 h-5 transition-transform group-hover:scale-110', $page.url.startsWith(item.href) ? '' : 'text-slate-400 dark:text-slate-500']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span v-if="sidebarOpen" class="ml-3 font-medium">{{ item.name }}</span>
                </Link>
            </nav>

            <!-- Bottom Section -->
            <div class="p-3 border-t border-slate-200/60 dark:border-slate-700/50">
                <!-- Theme Toggle -->
                <button
                    @click="toggleTheme"
                    :class="[
                        'flex items-center w-full px-3 py-2.5 rounded-xl transition-all duration-200',
                        'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-slate-200'
                    ]"
                >
                    <!-- Sun Icon (Light Mode) -->
                    <svg v-if="isDark" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon Icon (Dark Mode) -->
                    <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span v-if="sidebarOpen" class="ml-3 font-medium">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
                </button>
            </div>

            <!-- Toggle Button -->
            <button
                @click="toggleSidebar"
                class="absolute -right-3 top-20 w-6 h-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full flex items-center justify-center shadow-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 hover:scale-110"
            >
                <svg
                    :class="['w-4 h-4 text-slate-400 transition-transform duration-300', sidebarOpen ? '' : 'rotate-180']"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </aside>

        <!-- Main Content -->
        <div :class="['transition-all duration-300', sidebarOpen ? 'ml-64' : 'ml-20']">
            <!-- Top Bar -->
            <header class="sticky top-0 z-40 h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-700/50">
                <div class="flex items-center justify-between h-full px-6">
                    <div class="flex items-center space-x-4">
                        <slot name="header" />
                    </div>

                    <!-- User Menu -->
                    <div class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center space-x-3 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-all duration-200 group"
                        >
                            <div class="relative">
                                <img
                                    v-if="user?.avatar"
                                    :src="user.avatar"
                                    :alt="user.name"
                                    class="w-9 h-9 rounded-xl ring-2 ring-white dark:ring-slate-800 shadow-sm"
                                />
                                <div v-else class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center ring-2 ring-white dark:ring-slate-800 shadow-sm">
                                    <span class="text-white text-sm font-semibold">{{ user?.name?.charAt(0) }}</span>
                                </div>
                                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-900"></div>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">{{ user?.name }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ user?.email }}</p>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <div
                                v-if="userMenuOpen"
                                class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200/60 dark:border-slate-700/50 py-1 overflow-hidden"
                            >
                                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ user?.name }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ user?.email }}</p>
                                </div>
                                <button
                                    @click="logout"
                                    class="flex items-center w-full px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign out
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-2 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-2 opacity-0"
            >
                <div v-if="flash?.success || flash?.error" class="px-6 pt-4">
                    <div
                        v-if="flash?.success"
                        class="flex items-center p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-emerald-700 dark:text-emerald-300"
                    >
                        <svg class="w-5 h-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ flash.success }}
                    </div>
                    <div
                        v-if="flash?.error"
                        class="flex items-center p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-xl text-red-700 dark:text-red-300"
                    >
                        <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ flash.error }}
                    </div>
                </div>
            </Transition>

            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>

        <!-- Click outside to close user menu -->
        <div v-if="userMenuOpen" @click="userMenuOpen = false" class="fixed inset-0 z-30" />
    </div>
</template>
