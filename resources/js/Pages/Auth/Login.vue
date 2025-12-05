<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash as { error?: string; success?: string });
</script>

<template>
    <Head title="Sign In" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
        <div class="w-full max-w-md">
            <!-- Logo Card -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 border border-white/20">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-lg mb-4">
                        <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Sprint Planner</h1>
                    <p class="text-white/80">Agile project management made simple</p>
                </div>

                <!-- Error Message -->
                <div v-if="flash?.error" class="mb-6 p-4 bg-red-500/20 border border-red-400/50 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-red-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-red-200">{{ flash.error }}</p>
                    </div>
                </div>

                <!-- Login Button -->
                <a
                    href="/auth/google/redirect"
                    class="flex items-center justify-center w-full px-6 py-4 bg-white hover:bg-gray-50 text-gray-800 font-semibold rounded-xl shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5"
                >
                    <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign in with Google
                </a>

                <!-- Restricted Access Notice -->
                <div class="mt-6 p-4 bg-white/10 rounded-xl border border-white/20">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-yellow-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-white/80">
                            Access is restricted to <span class="font-semibold text-white">@gainline.co.uk</span> accounts only.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-white/60 text-sm mt-6">
                Powered by Gainline
            </p>
        </div>
    </div>
</template>
