<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import type { Project, Sprint } from '@/Types';

interface Props {
    project: Project;
    sprint: Sprint;
    columns: { id: string; title: string; status: string }[];
    burndownData: any[];
    isOnTrack: boolean;
}

const props = defineProps<Props>();

const startSprint = () => {
    router.post(`/projects/${props.project.id}/sprints/${props.sprint.id}/start`);
};

const completeSprint = () => {
    if (confirm('Complete this sprint? Incomplete stories will be moved to backlog.')) {
        router.post(`/projects/${props.project.id}/sprints/${props.sprint.id}/complete`);
    }
};

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

const priorityConfig = {
    low: { bg: 'bg-slate-100 dark:bg-slate-700', text: 'text-slate-600 dark:text-slate-300' },
    medium: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-700 dark:text-blue-300' },
    high: { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-700 dark:text-amber-300' },
    critical: { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-700 dark:text-red-300' },
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head :title="sprint.name" />

    <MainLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link
                    :href="`/projects/${project.id}/sprints`"
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
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ sprint.name }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ project.name }}</p>
                </div>
            </div>
        </template>

        <!-- Sprint Navigation -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div class="flex items-center space-x-1 bg-slate-100 dark:bg-slate-800/50 rounded-xl p-1">
                <Link
                    :href="`/projects/${project.id}/sprints/${sprint.id}`"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm"
                >
                    Overview
                </Link>
                <Link
                    :href="`/projects/${project.id}/sprints/${sprint.id}/board`"
                    class="px-4 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors"
                >
                    Board
                </Link>
                <Link
                    :href="`/projects/${project.id}/sprints/${sprint.id}/timeline`"
                    class="px-4 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors"
                >
                    Timeline
                </Link>
                <Link
                    :href="`/projects/${project.id}/sprints/${sprint.id}/reports`"
                    class="px-4 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors"
                >
                    Reports
                </Link>
            </div>

            <div class="flex items-center space-x-3">
                <span :class="['inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-full', statusConfig[sprint.status].bg, statusConfig[sprint.status].text]">
                    <span :class="['w-2 h-2 rounded-full mr-2', statusConfig[sprint.status].dot]"></span>
                    {{ sprint.status.charAt(0).toUpperCase() + sprint.status.slice(1) }}
                </span>
                <button
                    v-if="sprint.status === 'planning'"
                    @click="startSprint"
                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-xl shadow-sm transition-all duration-200 hover:shadow-md"
                >
                    Start Sprint
                </button>
                <button
                    v-if="sprint.status === 'active'"
                    @click="completeSprint"
                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-xl shadow-sm transition-all duration-200 hover:shadow-md"
                >
                    Complete Sprint
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Sprint Info -->
                <div class="bg-white dark:bg-slate-800/50 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/50 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-6">Sprint Details</h2>

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Start Date</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ formatDate(sprint.start_date) }}
                            </p>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">End Date</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ formatDate(sprint.end_date) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="sprint.goal" class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800/30">
                        <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-wide mb-2">Sprint Goal</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ sprint.goal }}</p>
                    </div>
                </div>

                <!-- Progress -->
                <div class="bg-white dark:bg-slate-800/50 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/50 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-6">Progress</h2>

                    <div class="mb-6">
                        <div class="flex justify-between text-sm mb-3">
                            <span class="text-slate-600 dark:text-slate-400">{{ sprint.completed_hours }}h completed</span>
                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ sprint.progress_percentage }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-indigo-500 to-purple-500 h-3 rounded-full transition-all duration-500"
                                :style="{ width: `${sprint.progress_percentage}%` }"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl text-center">
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ sprint.total_hours }}</p>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Total Hours</p>
                        </div>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl text-center">
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ sprint.completed_hours }}</p>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Completed</p>
                        </div>
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-center">
                            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ sprint.remaining_hours }}</p>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Remaining</p>
                        </div>
                    </div>
                </div>

                <!-- Stories in Sprint -->
                <div class="bg-white dark:bg-slate-800/50 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
                    <div class="p-6 border-b border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Stories
                            <span class="ml-2 text-sm font-normal text-slate-500 dark:text-slate-400">({{ sprint.stories?.length || 0 }})</span>
                        </h2>
                        <Link
                            :href="`/projects/${project.id}/stories/create?sprint_id=${sprint.id}`"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-all"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Story
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        <Link
                            v-for="story in sprint.stories"
                            :key="story.id"
                            :href="`/projects/${project.id}/stories/${story.id}`"
                            class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">{{ story.story_key }}</span>
                                        <span :class="['text-xs font-medium px-2 py-0.5 rounded', priorityConfig[story.priority].bg, priorityConfig[story.priority].text]">
                                            {{ story.priority }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">{{ story.title }}</p>
                                </div>
                                <div class="flex items-center space-x-3 ml-4">
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">{{ story.estimated_hours }}h</span>
                                    <svg class="w-4 h-4 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </Link>
                        <div v-if="!sprint.stories?.length" class="p-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">No stories in this sprint yet</p>
                            <Link
                                :href="`/projects/${project.id}/stories/create?sprint_id=${sprint.id}`"
                                class="inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
                            >
                                Add your first story
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div
                    :class="[
                        'rounded-2xl shadow-sm border p-6',
                        isOnTrack
                            ? 'bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border-emerald-200/60 dark:border-emerald-800/50'
                            : 'bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border-red-200/60 dark:border-red-800/50'
                    ]"
                >
                    <div class="flex items-center space-x-4">
                        <div :class="['p-3 rounded-xl', isOnTrack ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'bg-red-100 dark:bg-red-900/50']">
                            <svg
                                v-if="isOnTrack"
                                class="w-6 h-6 text-emerald-600 dark:text-emerald-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg
                                v-else
                                class="w-6 h-6 text-red-600 dark:text-red-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <p :class="['text-lg font-semibold', isOnTrack ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400']">
                                {{ isOnTrack ? 'On Track' : 'At Risk' }}
                            </p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ sprint.days_remaining }} days remaining</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-slate-800/50 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/50 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <Link
                            :href="`/projects/${project.id}/sprints/${sprint.id}/board`"
                            class="flex items-center w-full px-4 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all group"
                        >
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mr-3 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/50 transition-colors">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                </svg>
                            </div>
                            Open Kanban Board
                        </Link>
                        <Link
                            :href="`/projects/${project.id}/sprints/${sprint.id}/timeline`"
                            class="flex items-center w-full px-4 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all group"
                        >
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg mr-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition-colors">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            View Timeline
                        </Link>
                        <Link
                            :href="`/projects/${project.id}/sprints/${sprint.id}/reports`"
                            class="flex items-center w-full px-4 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all group"
                        >
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg mr-3 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/50 transition-colors">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            View Reports
                        </Link>
                        <Link
                            :href="`/projects/${project.id}/sprints/${sprint.id}/edit`"
                            class="flex items-center w-full px-4 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all group"
                        >
                            <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg mr-3 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/50 transition-colors">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            Edit Sprint
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
