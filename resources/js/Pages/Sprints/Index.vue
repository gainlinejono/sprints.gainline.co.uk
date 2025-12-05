<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import type { Project, Sprint } from '@/Types';

interface Props {
    project: Project;
    sprints: Sprint[];
}

const props = defineProps<Props>();

const statusConfig = {
    planning: {
        bg: 'bg-slate-100 dark:bg-slate-700/50',
        text: 'text-slate-600 dark:text-slate-300',
        dot: 'bg-slate-400'
    },
    active: {
        bg: 'bg-emerald-100 dark:bg-emerald-900/30',
        text: 'text-emerald-700 dark:text-emerald-300',
        dot: 'bg-emerald-500'
    },
    completed: {
        bg: 'bg-blue-100 dark:bg-blue-900/30',
        text: 'text-blue-700 dark:text-blue-300',
        dot: 'bg-blue-500'
    },
    cancelled: {
        bg: 'bg-red-100 dark:bg-red-900/30',
        text: 'text-red-700 dark:text-red-300',
        dot: 'bg-red-500'
    },
};

const startSprint = (sprint: Sprint) => {
    router.post(`/projects/${props.project.id}/sprints/${sprint.id}/start`);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short'
    });
};
</script>

<template>
    <Head :title="`${project.name} - Sprints`" />

    <MainLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link
                    :href="`/projects/${project.id}`"
                    class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div
                    class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg"
                    :style="{ backgroundColor: project.color }"
                >
                    {{ project.key?.substring(0, 2) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">Sprints</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ project.name }}</p>
                </div>
            </div>
        </template>

        <div class="flex justify-between items-center mb-8">
            <div>
                <p class="text-slate-600 dark:text-slate-400">
                    {{ sprints.length }} {{ sprints.length === 1 ? 'sprint' : 'sprints' }} total
                </p>
            </div>
            <Link
                :href="`/projects/${project.id}/sprints/create`"
                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-sm font-medium rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-indigo-500/30 hover:-translate-y-0.5"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Sprint
            </Link>
        </div>

        <div class="space-y-4">
            <div
                v-for="sprint in sprints"
                :key="sprint.id"
                class="group bg-white dark:bg-slate-800/50 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/50 p-6 hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-3 mb-2">
                            <Link
                                :href="`/projects/${project.id}/sprints/${sprint.id}`"
                                class="text-lg font-semibold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate"
                            >
                                {{ sprint.name }}
                            </Link>
                            <span :class="['inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full', statusConfig[sprint.status].bg, statusConfig[sprint.status].text]">
                                <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', statusConfig[sprint.status].dot]"></span>
                                {{ sprint.status.charAt(0).toUpperCase() + sprint.status.slice(1) }}
                            </span>
                        </div>
                        <p v-if="sprint.goal" class="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-2">{{ sprint.goal }}</p>
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ formatDate(sprint.start_date) }} - {{ formatDate(sprint.end_date) }}
                            </span>
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                {{ sprint.stories_count || 0 }} stories
                            </span>
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ sprint.total_hours || 0 }}h total
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                        <button
                            v-if="sprint.status === 'planning'"
                            @click="startSprint(sprint)"
                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-xl shadow-sm transition-all duration-200 hover:shadow-md"
                        >
                            Start Sprint
                        </button>
                        <Link
                            v-if="sprint.status === 'active'"
                            :href="`/projects/${project.id}/sprints/${sprint.id}/board`"
                            class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-sm font-medium rounded-xl shadow-sm transition-all duration-200 hover:shadow-md"
                        >
                            Open Board
                        </Link>
                        <Link
                            :href="`/projects/${project.id}/sprints/${sprint.id}`"
                            class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Progress bar for active sprints -->
                <div v-if="sprint.status === 'active'" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/50">
                    <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-2">
                        <span class="font-medium">{{ sprint.completed_hours || 0 }}h completed</span>
                        <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ sprint.progress_percentage || 0 }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full transition-all duration-500"
                            :style="{ width: `${sprint.progress_percentage || 0}%` }"
                        />
                    </div>
                </div>
            </div>

            <div v-if="sprints.length === 0" class="text-center py-16">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 flex items-center justify-center">
                    <svg class="w-10 h-10 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">No sprints yet</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-sm mx-auto">Get started by creating your first sprint to organize your work into time-boxed iterations.</p>
                <Link
                    :href="`/projects/${project.id}/sprints/create`"
                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-sm font-medium rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:shadow-xl"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Sprint
                </Link>
            </div>
        </div>
    </MainLayout>
</template>
