<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import type { Project, Sprint, Story } from '@/Types';

interface Props {
    projects: Project[];
    activeSprints: Sprint[];
    myStories: Story[];
    stats: {
        total_projects: number;
        active_sprints: number;
        my_stories: number;
        completed_today: number;
    };
}

defineProps<Props>();

const priorityColors = {
    low: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    medium: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300',
    high: 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-300',
    critical: 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300',
};

const statusColors = {
    todo: 'bg-blue-500',
    in_progress: 'bg-yellow-500',
    review: 'bg-purple-500',
    done: 'bg-green-500',
};
</script>

<template>
    <Head title="Dashboard" />

    <MainLayout>
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        </template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Projects</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_projects }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Sprints</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active_sprints }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">My Stories</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.my_stories }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed Today</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.completed_today }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- My Stories -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">My Stories</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <Link
                        v-for="story in myStories"
                        :key="story.id"
                        :href="`/projects/${story.project_id}/stories/${story.id}`"
                        class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ story.story_key }}</span>
                                    <span :class="['text-xs px-2 py-0.5 rounded-full', priorityColors[story.priority]]">
                                        {{ story.priority }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ story.title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ story.project?.name }} {{ story.sprint ? `• ${story.sprint.name}` : '' }}
                                </p>
                            </div>
                            <div :class="['w-2 h-2 rounded-full ml-4 mt-2', statusColors[story.status]]" />
                        </div>
                    </Link>
                    <div v-if="myStories.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No stories assigned to you
                    </div>
                </div>
            </div>

            <!-- Active Sprints -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Active Sprints</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <Link
                        v-for="sprint in activeSprints"
                        :key="sprint.id"
                        :href="`/projects/${sprint.project_id}/sprints/${sprint.id}/board`"
                        class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ sprint.name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ sprint.days_remaining }} days left</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ sprint.project?.name }}</p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div
                                class="bg-indigo-600 h-2 rounded-full transition-all"
                                :style="{ width: `${sprint.progress_percentage}%` }"
                            />
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ sprint.completed_hours }}h completed</span>
                            <span>{{ sprint.remaining_hours }}h remaining</span>
                        </div>
                    </Link>
                    <div v-if="activeSprints.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No active sprints
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Projects -->
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Projects</h2>
                <Link href="/projects/create" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    + New Project
                </Link>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    v-for="project in projects.slice(0, 6)"
                    :key="project.id"
                    :href="`/projects/${project.id}`"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600"
                >
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-12 h-12 rounded-lg flex items-center justify-center text-white font-bold text-lg"
                            :style="{ backgroundColor: project.color }"
                        >
                            {{ project.key?.substring(0, 2) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ project.name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ project.stories_count || 0 }} stories • {{ project.sprints_count || 0 }} sprints
                            </p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </MainLayout>
</template>
