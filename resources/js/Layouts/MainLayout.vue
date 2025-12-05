<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { PageProps, User } from '@/Types';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

const sidebarOpen = ref(true);
const userMenuOpen = ref(false);

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
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300',
                sidebarOpen ? 'w-64' : 'w-20'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                <Link href="/dashboard" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span v-if="sidebarOpen" class="text-xl font-bold text-gray-900 dark:text-white">Sprint</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center px-3 py-2 rounded-lg transition-colors',
                        $page.url.startsWith(item.href)
                            ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400'
                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]"
                >
                    <!-- Home Icon -->
                    <svg v-if="item.icon === 'home'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <!-- Folder Icon -->
                    <svg v-if="item.icon === 'folder'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span v-if="sidebarOpen" class="ml-3">{{ item.name }}</span>
                </Link>
            </nav>

            <!-- Toggle Button -->
            <button
                @click="toggleSidebar"
                class="absolute -right-3 top-20 w-6 h-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full flex items-center justify-center shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
            >
                <svg
                    :class="['w-4 h-4 text-gray-500 transition-transform', sidebarOpen ? '' : 'rotate-180']"
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
            <header class="sticky top-0 z-40 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between h-full px-6">
                    <div class="flex items-center space-x-4">
                        <slot name="header" />
                    </div>

                    <!-- User Menu -->
                    <div class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <img
                                v-if="user?.avatar"
                                :src="user.avatar"
                                :alt="user.name"
                                class="w-8 h-8 rounded-full"
                            />
                            <div v-else class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                <span class="text-white text-sm font-medium">{{ user?.name?.charAt(0) }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ user?.name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div
                            v-if="userMenuOpen"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1"
                        >
                            <button
                                @click="logout"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                Sign out
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <div v-if="flash?.success || flash?.error" class="px-6 pt-4">
                <div
                    v-if="flash?.success"
                    class="p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300"
                >
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash?.error"
                    class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300"
                >
                    {{ flash.error }}
                </div>
            </div>

            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>

        <!-- Click outside to close user menu -->
        <div v-if="userMenuOpen" @click="userMenuOpen = false" class="fixed inset-0 z-30" />
    </div>
</template>
